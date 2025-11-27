@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}></div>
