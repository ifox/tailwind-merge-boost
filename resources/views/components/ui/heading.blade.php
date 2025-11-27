@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'text-2xl font-bold tracking-tight text-gray-900';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<h2 {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</h2>
