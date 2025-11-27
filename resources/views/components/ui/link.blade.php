@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'font-medium text-indigo-600 hover:text-indigo-500 hover:underline transition-colors duration-200';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<a {{ $attributes->merge(['class' => $mergedClasses, 'href' => '#']) }}>
    {{ $slot }}
</a>
