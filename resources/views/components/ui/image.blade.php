@props(['class' => '', 'merger' => 'boost', 'src' => 'https://via.placeholder.com/150'])

@php
    $defaultClasses = 'w-full h-48 object-cover rounded-lg shadow-md';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<img {{ $attributes->merge(['class' => $mergedClasses, 'src' => $src, 'alt' => 'Image']) }}>
