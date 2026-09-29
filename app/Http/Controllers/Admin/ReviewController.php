<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        if ($request->boolean('pending') && ! $status) {
            $status = 'pending';
        }

        $filteredQuery = $this->filteredReviewsQuery($request);

        $query = (clone $filteredQuery)->with(['user', 'product', 'repliedBy']);

        if ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'approved') {
            $query->where('is_approved', true);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => (clone $filteredQuery)->count(),
            'pending' => (clone $filteredQuery)->where('is_approved', false)->count(),
            'approved' => (clone $filteredQuery)->where('is_approved', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    private function filteredReviewsQuery(Request $request)
    {
        $query = Review::query();

        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', '%'.$search.'%'))
                    ->orWhere('comment', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    public function approve(Review $review)
    {
        $review->update(['is_approved' => true]);

        return back()->with('success', 'نظر تایید شد.');
    }

    public function reject(Review $review)
    {
        $review->update(['is_approved' => false]);

        return back()->with('success', 'تایید نظر لغو شد.');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('success', 'نظر حذف شد.');
    }

    /** پاسخ ادمین به نظر (ساخت یا ویرایش) */
    public function reply(Request $request, Review $review)
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'max:2000'],
        ], [
            'reply.required' => 'متن پاسخ را بنویسید.',
            'reply.max' => 'پاسخ نمی‌تواند بیشتر از ۲۰۰۰ کاراکتر باشد.',
        ]);

        $review->update([
            'reply' => $data['reply'],
            'replied_by' => $request->user()->id,
            'replied_at' => now(),
        ]);

        return back()->with('success', 'پاسخ ثبت شد.');
    }

    /** حذف پاسخ */
    public function deleteReply(Review $review)
    {
        $review->update([
            'reply' => null,
            'replied_by' => null,
            'replied_at' => null,
        ]);

        return back()->with('success', 'پاسخ حذف شد.');
    }
}