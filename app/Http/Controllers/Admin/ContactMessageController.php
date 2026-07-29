<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReplyChannel;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContactMessageReplyRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function __construct(private ContactMessageService $messages) {}

    public function index(Request $request): View
    {
        $query = ContactMessage::withCount('replies')->latest();

        if ($request->boolean('unread')) {
            $query->unread();
        } elseif ($request->boolean('read')) {
            $query->read();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('message', 'like', '%'.$search.'%');
            });
        }

        $stats = [
            'total' => ContactMessage::count(),
            'unread' => ContactMessage::unread()->count(),
            'read' => ContactMessage::read()->count(),
        ];

        $messages = $query->paginate(15)->withQueryString();

        return view('admin.messages.index', compact('messages', 'stats'));
    }

    public function show(ContactMessage $message): View
    {
        $message->markAsRead();
        $message->load(['replies.user', 'user']);

        $adjacent = [
            'newer' => ContactMessage::where('created_at', '>', $message->created_at)->oldest()->first(),
            'older' => ContactMessage::where('created_at', '<', $message->created_at)->latest()->first(),
        ];

        return view('admin.messages.show', compact('message', 'adjacent'));
    }

    public function reply(ContactMessageReplyRequest $request, ContactMessage $message): RedirectResponse
    {
        $channel = ReplyChannel::from($request->validated('channel'));

        $reply = $this->messages->adminReply(
            $message,
            auth()->user(),
            $request->validated('body'),
            $channel,
        );

        $flash = match ($channel) {
            ReplyChannel::Panel => 'پاسخ در پنل کاربری مشتری ثبت شد.',
            ReplyChannel::PanelSms => $reply->sms_sent
                ? 'پاسخ در پنل ثبت شد و پیامک اطلاع‌رسانی ارسال شد.'
                : 'پاسخ در پنل ثبت شد. (شماره موبایل ثبت نشده — پیامک ارسال نشد)',
            ReplyChannel::Sms => $reply->sms_sent
                ? 'پاسخ با پیامک ارسال شد و در پنل نیز ثبت گردید.'
                : 'پاسخ در پنل ثبت شد. (شماره موبایل ثبت نشده — پیامک ارسال نشد)',
            ReplyChannel::Email => $reply->email_sent
                ? 'پاسخ با ایمیل ارسال شد و در پنل نیز ثبت گردید.'
                : 'پاسخ در پنل ثبت شد. (ارسال ایمیل ناموفق بود)',
        };

        return redirect()
            ->route('admin.messages.show', $message)
            ->with('success', $flash);
    }

    public function markUnread(ContactMessage $message): RedirectResponse
    {
        $message->markAsUnread();

        return back()->with('success', 'پیام به‌عنوان خوانده‌نشده علامت‌گذاری شد.');
    }

    public function markAllRead(): RedirectResponse
    {
        ContactMessage::unread()->update(['is_read' => true]);

        return redirect()
            ->route('admin.messages.index')
            ->with('success', 'همه پیام‌ها خوانده شدند.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'پیام حذف شد.');
    }
}
