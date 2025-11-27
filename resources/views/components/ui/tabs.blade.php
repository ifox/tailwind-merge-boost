@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'flex space-x-4 border-b border-gray-200';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<nav {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</nav>
