<div class="product-reviews-wrap">
    @if($approvedCount > 0)
        <div class="product-reviews-overview">
            <div class="reviews-summary">
                <span class="reviews-summary-score">{{ to_persian_digits(number_format($avgRating, 1)) }}</span>
                <div class="reviews-summary-meta">
                    <x-star-rating :rating="round($avgRating)" size="md" />
                    <span class="reviews-summary-count">بر اساس {{ format_number($approvedCount) }} نظر</span>
                </div>
            </div>

            <div class="product-rating-bars" aria-label="توزیع امتیازها">
                @foreach($ratingDistribution as $star => $count)
                    @php $pct = $approvedCount > 0 ? round(($count / $approvedCount) * 100) : 0; @endphp
                    <div class="product-rating-bar-row">
                        <span class="product-rating-bar-label">{{ $star }} ستاره</span>
                        <div class="product-rating-bar-track" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="product-rating-bar-fill" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="product-rating-bar-count">{{ format_number($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if($canReview)
        <form method="POST" action="{{ route('products.reviews.store', $product) }}" class="review-form" x-data="{ rating: 5, hover: 0 }">
            @csrf
            @if(request('from') === 'panel')
                <input type="hidden" name="from" value="panel">
            @endif
            <input type="hidden" name="rating" :value="rating" required>
            <p class="review-form-heading">نظر خود را بنویسید</p>
            <div class="review-form-row">
                <label class="review-form-label">امتیاز شما</label>
                <div class="review-form-stars" role="radiogroup" aria-label="انتخاب امتیاز">
                    @for($i = 1; $i <= 5; $i++)
                        <button
                            type="button"
                            class="review-form-star"
                            :class="{ 'is-active': (hover || rating) >= {{ $i }} }"
                            @click="rating = {{ $i }}"
                            @mouseenter="hover = {{ $i }}"
                            @mouseleave="hover = 0"
                            aria-label="{{ $i }} ستاره"
                        >
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                    <span class="review-form-rating-label" x-text="(hover || rating) + ' از ۵'"></span>
                </div>
            </div>
            <div class="review-form-row">
                <label for="review-comment" class="review-form-label">نظر شما</label>
                <textarea id="review-comment" name="comment" rows="4" class="review-form-textarea" placeholder="تجربه خرید خود را با دیگران به اشتراک بگذارید..."></textarea>
            </div>
            <button type="submit" class="btn-primary review-form-submit">ثبت نظر</button>
        </form>
    @elseif(auth()->check() && !auth()->user()->hasPurchasedProduct($product->id))
        <div class="product-review-notice">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
            <p>برای ثبت نظر، ابتدا این محصول را خریداری کنید.</p>
        </div>
    @endif

    @if($reviews->count())
        @if($reviews->count() > 3)
            <x-reviews-carousel class="reviews-list-carousel">
                @foreach($reviews as $review)
                    <div class="reviews-carousel-slide">
                        <x-review-card :review="$review" class="h-full" />
                    </div>
                @endforeach
            </x-reviews-carousel>
        @else
            <div class="reviews-list">
                @foreach($reviews as $review)
                    <x-review-card :review="$review" />
                @endforeach
            </div>
        @endif
    @else
        <div class="reviews-empty">
            <svg class="reviews-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 01.778-.332 48.294 48.294 0 005.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
            </svg>
            <p class="reviews-empty-title">هنوز نظری ثبت نشده</p>
            <p class="reviews-empty-desc">اولین نفری باشید که تجربه خود را با دیگران به اشتراک می‌گذارد.</p>
        </div>
    @endif
</div>
