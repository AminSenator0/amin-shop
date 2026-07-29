@props(['title'])

<div class="admin-page-header">
    <div>
        <h2 class="admin-page-title">{{ $title }}</h2>
    </div>
    @if(isset($actions))
        <div class="admin-page-actions">{{ $actions }}</div>
    @endif
</div>
