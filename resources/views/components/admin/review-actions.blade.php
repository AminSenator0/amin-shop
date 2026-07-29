@props(['review'])

<div class="flex items-center gap-1">
    <x-admin.table-action
        type="view"
        :href="route('products.show', $review->product->slug)"
        title="مشاهده محصول"
        target="_blank"
    />

    <x-admin.table-action
        type="approve"
        :action="route('admin.reviews.approve', $review)"
        method="PATCH"
        title="تایید"
    />

    @if(! $review->is_approved)
        <x-admin.table-action
            type="reject"
            :action="route('admin.reviews.destroy', $review)"
            method="DELETE"
            confirm="آیا از رد این نظر مطمئن هستید؟"
            title="رد"
        />
    @else
        <x-admin.table-action
            type="reject"
            :action="route('admin.reviews.reject', $review)"
            method="PATCH"
            confirm="آیا از رد (لغو تایید) این نظر مطمئن هستید؟"
            title="رد"
        />
    @endif

    <x-admin.table-action
        type="delete"
        :action="route('admin.reviews.destroy', $review)"
        confirm="آیا از حذف این نظر مطمئن هستید؟"
        title="حذف"
    />
</div>
