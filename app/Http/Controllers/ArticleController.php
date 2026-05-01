<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Genre;
use App\Models\Job;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $categorySlug = $request->input('category');

        $articles = Article::with(['categories'])
            ->published()
            ->when($categorySlug, fn($q) => $q->whereHas('categories', fn($c) => $c->where('slug', $categorySlug)))
            ->orderByDesc('published_at')
            ->paginate(12);

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

        // タイトルからエリア・業種を抽出（長い名前優先で誤マッチ防止）
        $areas  = Area::orderByRaw('LENGTH(name) DESC')->get(['id', 'name']);
        $genres = Genre::orderByRaw('LENGTH(name) DESC')->get(['id', 'name']);

        $matchedArea  = $areas->first(fn($a) => mb_strpos($article->title, $a->name) !== false);
        $matchedGenre = $genres->first(fn($g) => mb_strpos($article->title, $g->name) !== false);

        $baseQuery = fn() => Job::with(['shop.area', 'jobType'])
            ->where('status', 'active')
            ->whereIn('search_group', $searchGroups);

        $relatedJobs = $baseQuery()
            ->when($matchedArea,  fn($q) => $q->whereHas('shop', fn($s) => $s->where('area_id', $matchedArea->id)))
            ->when($matchedGenre, fn($q) => $q->whereHas('shop', fn($s) => $s->where('genre_id', $matchedGenre->id)))
            ->inRandomOrder()
            ->limit(4)
            ->get();

        // エリア＋業種で0件なら条件を緩めてリトライ
        if ($relatedJobs->isEmpty() && $matchedArea && $matchedGenre) {
            $relatedJobs = $baseQuery()
                ->where(fn($q) => $q
                    ->whereHas('shop', fn($s) => $s->where('area_id', $matchedArea->id))
                    ->orWhereHas('shop', fn($s) => $s->where('genre_id', $matchedGenre->id)))
                ->inRandomOrder()->limit(4)->get();
        }

        // それでも0件ならランダムフォールバック
        if ($relatedJobs->isEmpty()) {
            $relatedJobs = $baseQuery()->inRandomOrder()->limit(4)->get();
        }

        $lpLinks = $this->buildLpLinks($article->gender);

        return view('article.show', compact('article', 'relatedJobs', 'lpLinks'));
    }

    private function buildLpLinks(string $gender): array
    {
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
                'url'   => route('search.directory', [
                    'gender'    => $gender === 'yoasobi' ? 'female' : $gender,
                    'area_slug' => $area->slug,
                    'job_slug'  => 'all',
                ]) . '/',
            ])
            ->all();
    }
}
