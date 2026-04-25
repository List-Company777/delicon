@props(['src', 'alt' => '', 'class' => ''])
@if($src)
<picture>
    <source srcset="{{ asset('storage/' . \App\Services\ImageService::webpPath($src)) }}" type="image/webp">
    <img src="{{ asset('storage/' . $src) }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => $class, 'loading' => 'lazy']) }}>
</picture>
@endif
