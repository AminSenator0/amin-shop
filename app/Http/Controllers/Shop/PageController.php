<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Faq;

class PageController extends Controller
{
    public function faq()
    {
        $faqs = Faq::active();

        return view('shop.pages.faq', compact('faqs'));
    }

    public function about()
    {
        return view('shop.pages.about');
    }

    public function rules()
    {
        return view('shop.pages.rules');
    }

    public function contact()
    {
        return view('shop.pages.contact');
    }
}
