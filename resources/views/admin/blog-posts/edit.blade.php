@extends('layouts.admin')

@section('header', 'ویرایش مقاله')

@section('content')
<form method="POST" action="{{ route('admin.blog-posts.update', $post) }}" class="admin-form-card" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="admin-card-header">
        <div>
            <h2 class="admin-card-title">ویرایش مقاله</h2>
            <p class="mt-0.5 text-xs text-zinc-500 truncate max-w-md">{{ $post->title }}</p>
        </div>
        <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn-secondary text-xs">بازگشت به لیست</a>
    </div>
    <div class="space-y-6 p-6">
        @include('admin.blog-posts._form', ['post' => $post])
        <div class="flex flex-wrap gap-3 border-t border-zinc-100 pt-6">
            <button type="submit" class="admin-btn-primary">به‌روزرسانی</button>
            <a href="{{ route('admin.blog-posts.index') }}" class="admin-btn-secondary">انصراف</a>
        </div>
    </div>
</form>
@endsection
