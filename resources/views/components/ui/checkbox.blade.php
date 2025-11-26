@props(['class' => '', 'merger' => 'boost'])

@php
    $defaultClasses = 'relative flex items-center justify-center h-4 w-4';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}>
    <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
</div>
