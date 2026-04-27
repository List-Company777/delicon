<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleGenerationPrompt;
use App\Models\ArticleTag;
use App\Models\ArticleTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $articleTab = $request->query('atab', 'published');

        $publishedArticles = Article::with(['categories', 'video'])
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(20, ['*'], 'pub_page')
            ->appends(['atab' => 'published']);

        $draftArticles = Article::with('categories')
            ->where('is_published', false)
            ->orderByDesc('updated_at')
            ->paginate(20, ['*'], 'dft_page')
            ->appends(['atab' => 'draft']);

        $pendingTopics  = ArticleTopic::pending()->orderBy('id')->get();
        $approvedTopics = ArticleTopic::approved()->orderBy('sort_order')->orderBy('id')->get();
        $prompts        = ArticleGenerationPrompt::orderByRaw("FIELD(gender,'female','male','business','shop')")->get();

        return view('admin.articles.index', compact(
            'publishedArticles', 'draftArticles', 'articleTab',
            'pendingTopics', 'approvedTopics', 'prompts'
        ));
    }

    public function updatePrompt(Request $request, string $gender)
    {
        abort_unless(in_array($gender, ['female', 'male', 'business', 'shop']), 404);

        $request->validate(['instruction' => ['required', 'string', 'max:1000']]);

        ArticleGenerationPrompt::updateOrCreate(
            ['gender' => $gender],
            ['instruction' => $request->input('instruction')]
        );

        return back()->with('prompt_success', 'プロンプトを保存しました。');
    }

    public function create()
    {
        $categories = ArticleCategory::orderBy('sort_order')->get();
        $tags       = ArticleTag::orderBy('name')->get();
        $article    = new Article();

        return view('admin.articles.form', compact('article', 'categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $article = Article::create($validated);
        $article->categories()->sync($request->input('category_ids', []));
        $article->tags()->sync($this->resolveTags($request->input('tag_names', '')));

        return redirect()->route('admin.articles.index')
            ->with('success', '記事を作成しました。');
    }

    public function edit(Article $article)
    {
        $categories = ArticleCategory::orderBy('sort_order')->get();
        $tags       = ArticleTag::orderBy('name')->get();
        $article->load(['categories', 'tags']);

        return view('admin.articles.form', compact('article', 'categories', 'tags'));
    }

    public function update(Request $request, Article $article)
    {
        $validated = $this->validated($request, $article);

        $article->update($validated);
        $article->categories()->sync($request->input('category_ids', []));
        $article->tags()->sync($this->resolveTags($request->input('tag_names', '')));

        return redirect()->route('admin.articles.index')
            ->with('success', '記事を更新しました。');
    }

    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->route('admin.articles.index')
            ->with('success', '記事を削除しました。');
    }

    public function preview(Article $article)
    {
        $article->load(['categories', 'tags']);

        $searchGroups = match($article->gender) {
            'female'   => ['female', 'both'],
            'male'     => ['male', 'both'],
            'business' => ['both'],
            'shop'     => ['female', 'male', 'both'],
            default    => ['female', 'male', 'both'],
        };
        $relatedJobs = \App\Models\Job::with(['shop.area', 'jobType'])
            ->where('status', 'active')
            ->whereIn('search_group', $searchGroups)
            ->inRandomOrder()->limit(4)->get();

        return view('article.show', compact('article', 'relatedJobs'));
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        $slugUnique = 'unique:articles,slug' . ($article ? ",{$article->id}" : '');

        $data = $request->validate([
            'slug'             => ['required', 'string', 'max:200', 'regex:/^[a-z0-9_-]+$/', $slugUnique],
            'title'            => ['required', 'string', 'max:200'],
            'lead'             => ['nullable', 'string', 'max:500'],
            'body'             => ['nullable', 'string'],
            'hero_image'       => ['nullable', 'string', 'max:500'],
            'gender'           => ['required', 'in:female,male,business,shop'],
            'is_published'     => ['boolean'],
            'published_at'     => ['nullable', 'date'],
            'updated_at_manual'=> ['nullable', 'date'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    /** タグ名カンマ区切り文字列 → TagのIDリスト（なければ作成） */
    private function resolveTags(?string $tagInput): array
    {
        if (!$tagInput) return [];
        $names = array_filter(array_map('trim', explode(',', $tagInput)));
        $ids   = [];
        foreach ($names as $name) {
            $tag   = ArticleTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            $ids[] = $tag->id;
        }
        return $ids;
    }
}
