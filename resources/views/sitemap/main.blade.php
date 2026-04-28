<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@php
  $fmt  = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->toAtomString() : now()->toAtomString();
  $base = rtrim(url('/'), '/');
@endphp
@foreach($genders as $gender)
  <url>
    <loc>{{ $base }}/{{ $gender }}/all/all/</loc>
    <lastmod>{{ $fmt($lastmod) }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.9</priority>
  </url>
@if($gender !== 'business')
@foreach($jobTypes as $jt)
  <url>
    <loc>{{ $base }}/{{ $gender }}/all/{{ $jt->slug }}/</loc>
    <lastmod>{{ $fmt($lastmod) }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.7</priority>
  </url>
@endforeach
@else
@foreach($genres as $g)
  <url>
    <loc>{{ $base }}/{{ $gender }}/all/{{ $g->slug }}/</loc>
    <lastmod>{{ $fmt($lastmod) }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.7</priority>
  </url>
@endforeach
@endif
@foreach($prefs as $pref)
  <url>
    <loc>{{ $base }}/{{ $gender }}/{{ $pref->slug }}/all/</loc>
    <lastmod>{{ $fmt($lastmod) }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
@endforeach
@foreach($areas as $area)
  <url>
    <loc>{{ $base }}/{{ $gender }}/{{ $area->slug }}/all/</loc>
    <lastmod>{{ $fmt($lastmod) }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
@endforeach
@endforeach
</urlset>
