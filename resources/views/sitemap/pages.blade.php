<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@php
  $base      = rtrim(url('/'), '/');
  $siteStart = '2026-04-01T00:00:00+09:00';
  $artMod    = $articleLastmod ? \Carbon\Carbon::parse($articleLastmod)->toAtomString() : $siteStart;
@endphp
  <url>
    <loc>{{ $base }}/</loc>
    <lastmod>{{ $artMod }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>
  <url>
    <loc>{{ $base }}/article/</loc>
    <lastmod>{{ $artMod }}</lastmod>
    <changefreq>daily</changefreq>
    <priority>0.8</priority>
  </url>
  <url>
    <loc>{{ $base }}/company/</loc>
    <lastmod>{{ $siteStart }}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.4</priority>
  </url>
  <url>
    <loc>{{ $base }}/privacy/</loc>
    <lastmod>{{ $siteStart }}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>
  <url>
    <loc>{{ $base }}/terms/</loc>
    <lastmod>{{ $siteStart }}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.3</priority>
  </url>

  <url>
    <loc>{{ $base }}/keisai/</loc>
    <lastmod>{{ $siteStart }}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <url>
    <loc>{{ $base }}/yorubiz/</loc>
    <lastmod>{{ $siteStart }}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
</urlset>
