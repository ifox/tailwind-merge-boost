@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'text-gray-600 leading-relaxed';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<p {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</p>
