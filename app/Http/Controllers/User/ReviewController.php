<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $reviews = $user->reviews()
            ->with('product')
            ->latest()
            ->paginate(10);

        $pendingProducts = $user->productsAwaitingReview();

        return view('user.reviews.index', compact('reviews', 'pendingProducts'));
    }
}
