<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@php
  $fmt  = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->toAtomString() : now()->toAtomString();
  $base = rtrim(url('/'), '/');
@endphp
@foreach($jobs as $job)
  <url>
    <loc>{{ $base }}/job/{{ $job->id }}/</loc>
    <lastmod>{{ $fmt($job->updated_at) }}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
  </url>
@endforeach
@foreach($shops as $shop)
  <url>
    <loc>{{ $base }}/shop/{{ $shop->id }}/</loc>
    <lastmod>{{ $fmt($shop->updated_at) }}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.6</priority>
    @php $shopImg = $shop->main_image ? url('storage/' . $shop->main_image) : ($shop->shop_file_name ? $base . $shop->shop_file_name : null); @endphp
    @if($shopImg)
    <image:image>
      <image:loc>{{ $shopImg }}</image:loc>
      @if($shop->name)<image:title>{{ e($shop->name) }}</image:title>@endif
    </image:image>
    @endif
  </url>
@endforeach
@foreach($casts as $cast)
  <url>
    <loc>{{ $base }}/cast/{{ $cast->id }}/</loc>
    <lastmod>{{ $fmt($cast->updated_at) }}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.7</priority>
    <image:image>
      <image:loc>{{ $base . $cast->img_file_name . 'big.jpg' }}</image:loc>
      @if($cast->name)<image:title>{{ e($cast->name) }}</image:title>@endif
    </image:image>
    @foreach($cast->images as $img)
    <image:image>
      <image:loc>{{ $base . $img->img_path }}</image:loc>
    </image:image>
    @endforeach
  </url>
@endforeach
@foreach($articles as $article)
  <url>
    <loc>{{ $base }}/article/{{ $article->slug }}/</loc>
    <lastmod>{{ $fmt($article->updated_at) }}</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.6</priority>
  </url>
@endforeach
</urlset>
