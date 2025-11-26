@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<span {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</span>
