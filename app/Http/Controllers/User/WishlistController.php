<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\WishlistService;

class WishlistController extends Controller
{
    public function index(WishlistService $wishlist)
    {
        return view('user.wishlist.index', ['products' => $wishlist->products()]);
    }
}
