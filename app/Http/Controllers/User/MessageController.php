<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ContactMessageReplyRequest;
use App\Http\Requests\User\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Services\ContactMessageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function __construct(private ContactMessageService $messages) {}

    public function index(): View
    {
        $messages = $this->messages->queryForUser(auth()->user())
            ->withCount('replies')
            ->latest()
            ->paginate(10);

        return view('user.messages.index', compact('messages'));
    }

    public function create(): View
    {
        return view('user.messages.create');
    }

    public function store(StoreContactMessageRequest $request)
    {
        $user = auth()->user();
    
        $data = [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
        ];
    
        if ($request->hasFile('attachment')) {
            $data['attachment'] = upload_message_attachment($request->file('attachment'));
        }
    
        $message = ContactMessage::create($data);
    
        return redirect()
            ->route('user.messages.show', $message)
            ->with('success', 'پیام شما ثبت شد. به زودی پاسخ می‌دهیم.');
    }

    public function show(ContactMessage $message): View
    {
        $this->authorizeMessage($message);

        $this->messages->linkToUser($message, auth()->user());
        $this->messages->markRepliesReadForUser($message);

        $message->load(['replies.user']);

        return view('user.messages.show', compact('message'));
    }

    public function reply(ContactMessageReplyRequest $request, ContactMessage $message): RedirectResponse
    {
        $this->authorizeMessage($message);
    
        $data = [
            'contact_message_id' => $message->id,
            'user_id' => auth()->id(),
            'body' => $request->validated('body'),
            'is_from_admin' => false,
        ];
    
        if ($request->hasFile('attachment')) {
            $data['attachment'] = upload_message_attachment($request->file('attachment'));
        }
    
        \App\Models\ContactMessageReply::create($data);
    
        // به ادمین نشون بده که پاسخ جدید داره
        $message->update(['has_unread_reply_for_user' => false]);
    
        return redirect()
            ->route('user.messages.show', $message)
            ->with('success', 'پیام شما ارسال شد. به زودی پاسخ می‌دهیم.');
    }
    private function authorizeMessage(ContactMessage $message): void
    {
        if (! $this->messages->userCanAccess($message, auth()->user())) {
            abort(403);
        }
    }
}
