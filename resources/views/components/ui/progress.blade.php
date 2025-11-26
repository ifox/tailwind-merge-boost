@props(['class' => '', 'merger' => 'boost', 'value' => 50])

@php
    $defaultClasses = 'w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700';
    
    if ($merger === 'boost') {
        $mergedClasses = app(\App\Services\TailwindMergeBoost::class)->merge($defaultClasses, $class);
    } else {
        $mergedClasses = \TailwindMerge\Laravel\Facades\TailwindMerge::merge($defaultClasses, $class);
    }
@endphp

<div {{ $attributes->merge(['class' => $mergedClasses]) }}>
    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $value }}%"></div>
</div>
