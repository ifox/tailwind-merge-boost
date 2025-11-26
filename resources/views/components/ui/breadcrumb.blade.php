@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'flex items-center gap-2 text-sm text-gray-600';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<nav {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</nav>
