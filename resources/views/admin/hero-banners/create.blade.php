@extends('layouts.admin')

@section('header', 'بنر هیرو جدید')

@section('content')
<div class="mx-auto max-w-3xl">
    @include('admin.hero-banners._form', ['banner' => $banner])
</div>
@endsection