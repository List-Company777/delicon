<?php

namespace App\Http\Controllers;

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
        $article = Article::with(['categories', 'tags'])
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

        return view('article.show', compact('article', 'relatedJobs'));
    }
}
