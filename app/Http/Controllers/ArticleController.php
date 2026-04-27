<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Article;
use App\Models\ArticleCategory;
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
            'business' => ['both'],
            default    => ['female', 'male', 'both'],
        };

        $relatedJobs = Job::with(['shop.area', 'jobType'])
            ->where('status', 'active')
            ->whereIn('search_group', $searchGroups)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $lpLinks = $this->buildLpLinks($article->gender);

        return view('article.show', compact('article', 'relatedJobs', 'lpLinks'));
    }

    private function buildLpLinks(string $gender): array
    {
        $jobSearchGroups = match($gender) {
            'female'   => ['female', 'both'],
            'male'     => ['male', 'both'],
            'business' => ['female', 'both'],
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
                    'gender'    => $gender === 'business' ? 'female' : $gender,
                    'area_slug' => $area->slug,
                    'job_slug'  => 'all',
                ]) . '/',
            ])
            ->all();
    }
}
