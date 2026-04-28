<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
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
