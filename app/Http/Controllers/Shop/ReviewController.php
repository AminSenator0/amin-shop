<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ReviewRequest;
use App\Models\Product;
use App\Models\Review;
use App\Support\StoreSettings;

class ReviewController extends Controller
{
    public function store(ReviewRequest $request, Product $product)
    {
        if (! auth()->user()->hasPurchasedProduct($product->id)) {
            return $this->redirectAfterStore($request, 'error', 'فقط پس از خرید و تحویل محصول می‌توانید نظر ثبت کنید.');
        }

        if (Review::where('user_id', auth()->id())->where('product_id', $product->id)->exists()) {
            return $this->redirectAfterStore($request, 'error', 'شما قبلاً برای این محصول نظر ثبت کرده‌اید.');
        }

        $data = $request->validated();

        Review::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_approved' => StoreSettings::bool('auto_approve_reviews'),
        ]);

        $message = StoreSettings::bool('auto_approve_reviews')
            ? 'نظر شما ثبت و منتشر شد.'
            : 'نظر شما ثبت شد و پس از تأیید در فروشگاه نمایش داده می‌شود.';

        return $this->redirectAfterStore($request, 'success', $message);
    }

    private function redirectAfterStore(ReviewRequest $request, string $type, string $message)
    {
        if ($request->input('from') === 'panel') {
            return redirect()->route('user.reviews.index')->with($type, $message);
        }

        return back()->with($type, $message);
    }
}
