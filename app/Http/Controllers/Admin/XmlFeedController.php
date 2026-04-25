<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\XmlFeed;
use Illuminate\Http\Request;

class XmlFeedController extends Controller
{
    public function index()
    {
        $feeds = XmlFeed::orderBy('id')->get();
        return view('admin.xml-feeds.index', compact('feeds'));
    }

    public function create()
    {
        return view('admin.xml-feeds.form', ['feed' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        XmlFeed::create($data);
        return redirect()->route('admin.xml-feeds.index')->with('success', '連携先を追加しました');
    }

    public function edit(XmlFeed $xmlFeed)
    {
        return view('admin.xml-feeds.form', ['feed' => $xmlFeed]);
    }

    public function update(Request $request, XmlFeed $xmlFeed)
    {
        $data = $this->validated($request, $xmlFeed->id);
        $xmlFeed->update($data);
        return redirect()->route('admin.xml-feeds.index')->with('success', '連携先を更新しました');
    }

    public function addBudget(Request $request, XmlFeed $xmlFeed)
    {
        $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:9999999'],
        ]);

        $xmlFeed->increment('budget_balance', $request->integer('amount'));

        return back()->with('success', number_format($request->integer('amount')) . '円を予算に追加しました（残高: ' . number_format($xmlFeed->fresh()->budget_balance) . '円）');
    }

    public function toggleStatus(XmlFeed $xmlFeed)
    {
        $xmlFeed->update([
            'status' => $xmlFeed->status === 'active' ? 'inactive' : 'active',
        ]);
        return back()->with('success', 'ステータスを変更しました');
    }

    private function validated(Request $request, ?int $currentId = null): array
    {
        $validated = $request->validate([
            'name'                     => ['required', 'string', 'max:100'],
            'slug'                     => ['required', 'string', 'max:50', 'alpha_dash',
                                           "unique:xml_feeds,slug" . ($currentId ? ",{$currentId}" : '')],
            'url'                      => ['nullable', 'url', 'max:500'],
            'feed_type'                => ['required', 'in:staff_jobs,cast_jobs,business_info'],
            'is_own_site'              => ['boolean'],
            'allowed_categories_text'  => ['nullable', 'string'],
            'category_genre_map_json'  => ['nullable', 'string'],
            'bid_price_xml_field'      => ['nullable', 'string', 'max:50'],
            'monthly_budget_xml_field' => ['nullable', 'string', 'max:50'],
            'status'                   => ['required', 'in:active,inactive'],
        ]);

        // カテゴリリスト: 改行 or カンマ区切り → JSON配列
        $catText = trim($validated['allowed_categories_text'] ?? '');
        $allowedCategories = null;
        if ($catText !== '') {
            $allowedCategories = array_values(array_filter(
                array_map('trim', preg_split('/[\n,]+/', $catText))
            ));
        }

        // ジャンルマップ: JSON文字列を検証
        $genreMapJson = trim($validated['category_genre_map_json'] ?? '');
        $categoryGenreMap = null;
        if ($genreMapJson !== '') {
            $decoded = json_decode($genreMapJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'category_genre_map_json' => 'JSONの形式が正しくありません',
                ]);
            }
            $categoryGenreMap = $decoded;
        }

        return [
            'name'                     => $validated['name'],
            'slug'                     => $validated['slug'],
            'url'                      => $validated['url'] ?? '',
            'feed_type'                => $validated['feed_type'],
            'is_own_site'              => $request->boolean('is_own_site'),
            'allowed_categories'       => $allowedCategories,
            'category_genre_map'       => $categoryGenreMap,
            'bid_price_xml_field'      => $validated['bid_price_xml_field'] ?? null,
            'monthly_budget_xml_field' => $validated['monthly_budget_xml_field'] ?? null,
            'status'                   => $validated['status'],
        ];
    }
}
