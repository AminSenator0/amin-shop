@extends('layouts.admin')

@section('header', 'ویرایش بنر هیرو')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.hero-banners._form', ['banner' => $banner])
</div>
@endsection