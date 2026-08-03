<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\ContactRequest;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(ContactRequest $request)
    {
        $data = $request->validated();
        
        if ($request->hasFile('attachment')) {
            $data['attachment'] = upload_message_attachment($request->file('attachment'));
        }
        
        ContactMessage::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'پیام شما با موفقیت ارسال شد. پاسخ را در پنل کاربری خود مشاهده کنید.');
    }
}