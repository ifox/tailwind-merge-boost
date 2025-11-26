@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'inline-block bg-gray-100 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<span {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</span>
