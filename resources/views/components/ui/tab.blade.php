@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'py-2 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300 focus:outline-none';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<button {{ $attributes->merge(['class' => $mergedClasses]) }}>
    {{ $slot }}
</button>
