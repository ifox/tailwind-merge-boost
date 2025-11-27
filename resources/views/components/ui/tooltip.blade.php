@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'group relative bg-white p-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 cursor-pointer';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</div>
