@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'flex items-center p-4 bg-gray-50 rounded-lg border border-gray-200';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</div>
