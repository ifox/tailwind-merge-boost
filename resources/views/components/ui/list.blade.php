@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'bg-white shadow-lg rounded-lg overflow-hidden divide-y divide-gray-200';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<ul {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</ul>
