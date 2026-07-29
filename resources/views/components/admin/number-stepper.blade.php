@props([
    'name',
    'id' => null,
    'value' => 0,
    'min' => 0,
    'max' => null,
    'step' => 1,
    'required' => false,
])

@php
    $inputId = $id ?? $name;
    $initialValue = (int) old($name, $value);
    $maxValue = $max !== null ? (int) $max : null;
@endphp

<div
    {{ $attributes->merge(['class' => 'admin-number-stepper']) }}
    x-data="{
        min: {{ (int) $min }},
        max: {{ $maxValue ?? 'null' }},
        step: {{ (int) $step }},
        value: {{ $initialValue }},
        toPersian(number) {
            return String(number).replace(/[0-9]/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[digit]);
        },
        increment() {
            if (this.max !== null && this.value >= this.max) return;
            this.value += this.step;
        },
        decrement() {
            if (this.value <= this.min) return;
            this.value -= this.step;
        },
    }"
>
    <button
        type="button"
        class="admin-number-stepper-btn"
        aria-label="افزایش"
        @click="increment()"
        :disabled="max !== null && value >= max"
    >
        <svg class="h-4 w-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
    </button>

    <input type="hidden" name="{{ $name }}" x-bind:value="value" @if($required) required @endif>

    <input
        type="text"
        id="{{ $inputId }}"
        class="admin-number-stepper-input"
        dir="ltr"
        inputmode="numeric"
        autocomplete="off"
        x-bind:value="toPersian(value)"
        readonly
    >

    <button
        type="button"
        class="admin-number-stepper-btn"
        aria-label="کاهش"
        @click="decrement()"
        :disabled="value <= min"
    >
        <svg class="h-4 w-4 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
        </svg>
    </button>
</div>
