@props(['status'])

@if ($status)
    <div 
        x-data="{ show: true }" 
        x-init="setTimeout(() => show = false, 3000)" 
        x-show="show"
        {{ $attributes->merge([
            'class' => 'text-sm font-semibold mt-2 mb-4 transition-opacity duration-500',
            'style' => 'color: #8BC34A;'
        ]) }}>
        {{ $status }}
    </div>
@endif
