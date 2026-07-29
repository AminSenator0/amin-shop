@props([
    'label' => 'خروج',
    'buttonClass' => '',
    'tooltip' => null,
])

<form method="POST" action="{{ route('logout') }}" {{ $attributes->except('tooltip') }}>
    @csrf
    <button type="submit" @class([$buttonClass]) @if($tooltip) data-tooltip="{{ $tooltip }}" @endif>
        {{ $slot->isEmpty() ? $label : $slot }}
    </button>
</form>
