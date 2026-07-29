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

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $message = ContactMessage::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
        ]);

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

        $this->messages->userReply($message, auth()->user(), $request->validated('body'));

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
