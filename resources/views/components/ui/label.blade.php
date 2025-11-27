@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'text-sm font-medium text-gray-700 mb-1 block';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } elseif ($merger === 'once') {
        $mergedClasses = app(\TailwindMerge\Contracts\TailwindMergeContract::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<label {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</label>
