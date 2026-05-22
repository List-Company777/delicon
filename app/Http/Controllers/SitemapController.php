<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\Cast;
use App\Models\Genre;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Models\Shop;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    // LP検索ページ一覧（sitemap.xml）
    public function main()
    {
        $xml = Cache::remember('sitemap_main', 3600, function () {
            $genders  = ['male', 'female', 'yoasobi'];
            $jobTypes = JobType::whereHas('jobs', fn($q) => $q->where('status', 'active'))
                ->orderBy('slug')->get(['slug']);
            $genres   = Genre::whereHas('shops', fn($q) => $q->where('status', 'active'))
                ->orderBy('slug')->get(['slug']);
            $prefs    = Prefecture::whereHas('areas.jobs', fn($q) => $q->where('status', 'active'))
                ->orWhereHas('areas.shops', fn($q) => $q->where('status', 'active'))
                ->orderBy('slug')->get(['slug']);
            $areas    = Area::whereHas('jobs', fn($q) => $q->where('status', 'active'))
                ->orWhereHas('shops', fn($q) => $q->where('status', 'active'))
                ->orderBy('slug')->get(['slug']);

            $jobLastmod  = Job::where('status', 'active')->max('updated_at');
            $shopLastmod = Shop::where('status', 'active')->max('updated_at');
            $lastmod     = max($jobLastmod, $shopLastmod);

            return view('sitemap.main', compact(
                'genders', 'jobTypes', 'genres', 'prefs', 'areas', 'lastmod'
            ))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    // 求人・店舗・記事詳細ページ（sitemap-detail.xml）
    public function detail()
    {
        $xml = Cache::remember('sitemap_detail', 3600, function () {
            $jobs = Job::where('status', 'active')
                ->orderBy('id')
                ->get(['id', 'updated_at']);

            $shops = Shop::where('status', 'active')
                ->whereHas('detail', fn($q) => $q->where('status', 'active'))
                ->orderBy('id')
                ->get(['id', 'name', 'main_image', 'shop_file_name', 'updated_at']);

            $casts = Cast::where('status', 'active')
                ->whereNotNull('img_file_name')
                ->where('img_file_name', 'not like', '/img/common/%')
                ->with(['images' => fn($q) => $q->orderBy('sort_order')->limit(5)])
                ->orderBy('id')
                ->get(['id', 'name', 'img_file_name', 'updated_at']);

            $articles = Article::where('is_published', true)
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->get(['slug', 'updated_at']);

            return view('sitemap.detail', compact('jobs', 'shops', 'casts', 'articles'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    // 固定ページ（sitemap-pages.xml）
    public function pages()
    {
        $xml = Cache::remember('sitemap_pages', 86400, function () {
            $articleLastmod = Article::where('is_published', true)->max('updated_at');
            return view('sitemap.pages', compact('articleLastmod'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
