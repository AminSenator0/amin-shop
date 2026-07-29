<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\NewsletterSubscribeRequest;
use App\Models\NewsletterSubscriber;
use App\Support\StoreSettings;
use Illuminate\Http\RedirectResponse;

class NewsletterController extends Controller
{
    public function subscribe(NewsletterSubscribeRequest $request): RedirectResponse
    {
        if (! StoreSettings::bool('newsletter_enabled')) {
            return back()->with('error', 'عضویت در خبرنامه در حال حاضر غیرفعال است.');
        }

        NewsletterSubscriber::firstOrCreate(['email' => $request->validated('email')]);

        return back()->with('success', 'ایمیل شما با موفقیت ثبت شد.');
    }
}
