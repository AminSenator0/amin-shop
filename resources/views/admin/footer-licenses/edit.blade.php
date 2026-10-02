@extends('layouts.admin')

@section('header', 'ویرایش مجوز')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.footer-licenses._form', ['license' => $license])
</div>
@endsection