<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ContactRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        ContactMessage::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'پیام شما با موفقیت ارسال شد. پاسخ را در پنل کاربری خود مشاهده کنید.');
    }
}
