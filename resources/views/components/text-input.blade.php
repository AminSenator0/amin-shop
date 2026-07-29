@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-shop-primary focus:ring-shop-primary rounded-md shadow-sm']) }}>
