<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Genre;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->input('category');

        $articles = Article::with(['categories'])
            ->published()
            ->when($categorySlug, fn($q) => $q->whereHas('categories', fn($c) => $c->where('slug', $categorySlug)))
            ->orderByDesc('published_at')
            ->paginate(12)
            ->withPath(rtrim(request()->url(), '/') . '/')->withQueryString();

        $categories     = ArticleCategory::orderBy('sort_order')->get();
        $currentCategory = $categorySlug
            ? ArticleCategory::where('slug', $categorySlug)->first()
            : null;

        return view('article.index', compact('articles', 'categories', 'currentCategory'));
    }

    public function show(string $slug)
    {
        $article = Article::with(['categories', 'tags', 'video'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        $searchGroups = match($article->gender) {
            'female'   => ['female', 'both'],
            'male'     => ['male', 'both'],
            'yoasobi' => ['both'],
            default    => ['female', 'male', 'both'],
        };

        // エリア・ジャンルマスタ（タイトルマッチ用）をキャッシュ
        $areasRaw = Cache::remember('article:areas_for_matching', 86400, fn() =>
            Area::orderByRaw('LENGTH(name) DESC')->get(['id', 'name'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name])->all()
        );
        $areas = collect($areasRaw)->map(fn($a) => (object) $a);

        $genresRaw = Cache::remember('article:genres_for_matching', 86400, fn() =>
            Genre::orderByRaw('LENGTH(name) DESC')->get(['id', 'name'])
                ->map(fn($g) => ['id' => $g->id, 'name' => $g->name])->all()
        );
        $genres = collect($genresRaw)->map(fn($g) => (object) $g);

        $matchedArea  = $areas->first(fn($a) => mb_strpos($article->title, $a->name) !== false);
        $matchedGenre = $genres->first(fn($g) => mb_strpos($article->title, $g->name) !== false);

        // 関連求人：IDプール（最大20件）をキャッシュし、PHPでランダム4件選択
        $poolKey = 'article:related_job_pool:' . $article->id . ':' . implode(',', $searchGroups);
        $relatedJobIds = Cache::remember($poolKey, 1800, function () use ($searchGroups, $matchedArea, $matchedGenre) {
            $baseQuery = fn() => Job::where('status', 'active')->whereIn('search_group', $searchGroups);

            $ids = $baseQuery()
                ->when($matchedArea,  fn($q) => $q->whereHas('shop', fn($s) => $s->where('area_id', $matchedArea->id)))
                ->when($matchedGenre, fn($q) => $q->whereHas('shop', fn($s) => $s->where('genre_id', $matchedGenre->id)))
                ->limit(20)->pluck('id')->all();

            if (empty($ids) && $matchedArea && $matchedGenre) {
                $ids = $baseQuery()
                    ->where(fn($q) => $q
                        ->whereHas('shop', fn($s) => $s->where('area_id', $matchedArea->id))
                        ->orWhereHas('shop', fn($s) => $s->where('genre_id', $matchedGenre->id)))
                    ->limit(20)->pluck('id')->all();
            }

            if (empty($ids)) {
                $ids = $baseQuery()->limit(20)->pluck('id')->all();
            }

            return $ids;
        });

        $pickedIds   = $relatedJobIds ? collect($relatedJobIds)->shuffle()->take(4)->all() : [];
        $relatedJobs = $pickedIds
            ? Job::with(['shop.area', 'jobType'])->whereIn('id', $pickedIds)->get()
            : collect();

        $lpLinks = $this->buildLpLinks($article->gender);

        return view('article.show', compact('article', 'relatedJobs', 'lpLinks'));
    }

    private function buildLpLinks(string $gender): array
    {
        return Cache::remember("article:lp_links:{$gender}", 3600, function () use ($gender) {
            $jobSearchGroups = match($gender) {
                'female'   => ['female', 'both'],
                'male'     => ['male', 'both'],
                'yoasobi' => ['female', 'both'],
                default    => [],
            };
            if (!$jobSearchGroups) return [];

            return Area::withCount(['jobs' => fn($q) => $q->where('status', 'active')
                    ->whereIn('search_group', $jobSearchGroups)])
                ->having('jobs_count', '>=', 5)
                ->orderByDesc('jobs_count')
                ->take(6)
                ->get(['id', 'name', 'slug'])
                ->map(fn($area) => [
                    'label' => $area->name . 'の求人を見る',
                    'url'   => route('shop.list', [
                        'area_slug' => $area->slug,
                    ]) . '/',
                ])
                ->all();
        });
    }
}
