<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Prefecture;
use App\Models\ShopDetail;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = '検索結果5件以上のURLのみサイトマップを生成する';

    private const MIN_RESULTS = 5;

    public function handle(): void
    {
        $this->info('サイトマップ生成開始...');

        $areas       = Area::all()->keyBy('id');
        $jobTypes    = JobType::all()->keyBy('id');
        $prefectures = Prefecture::all();
        $genders     = ['male', 'female', 'business'];

        // area_slug の選択肢（all を含む）
        $areaSlugs = collect(['all'])->merge($areas->pluck('slug'));

        // job_type_slug の選択肢（all を含む）
        $jobSlugs  = collect(['all'])->merge($jobTypes->pluck('slug'));

        $urls  = [];
        $total = 0;
        $added = 0;

        // 都道府県LP
        $base = rtrim(config('app.url'), '/');
        foreach ($genders as $gender) {
            foreach ($prefectures as $pref) {
                $total++;
                $count = $this->countPrefResults($gender, $pref->slug);
                if ($count >= self::MIN_RESULTS) {
                    $urls[] = "{$base}/{$gender}/{$pref->slug}/";
                    $added++;
                }
            }
        }

        // エリア×職種LP
        foreach ($genders as $gender) {
            foreach ($areaSlugs as $areaSlug) {
                foreach ($jobSlugs as $jobSlug) {
                    $total++;
                    $count = $this->countResults($gender, $areaSlug, $jobSlug, $areas, $jobTypes);

                    if ($count >= self::MIN_RESULTS) {
                        $urls[] = $this->buildUrl($gender, $areaSlug, $jobSlug);
                        $added++;
                    }
                }
            }
        }

        $this->writeXml($urls);

        $this->info("完了: {$total} 件チェック → {$added} 件のURL をサイトマップに追加");
    }

    private function countPrefResults(string $gender, string $prefSlug): int
    {
        if ($gender === 'business') {
            return ShopDetail::where('status', 'active')
                ->whereHas('shop', fn($s) =>
                    $s->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug))
                )
                ->count();
        }

        $searchGroups = $gender === 'male' ? ['male', 'both'] : ['female', 'both'];

        return Job::where('status', 'active')
            ->whereIn('search_group', $searchGroups)
            ->whereHas('area.prefecture', fn($p) => $p->where('slug', $prefSlug))
            ->count();
    }

    private function countResults(string $gender, string $areaSlug, string $jobSlug, $areas, $jobTypes): int
    {
        if ($gender === 'business') {
            $query = ShopDetail::where('status', 'active');

            if ($areaSlug !== 'all') {
                $query->whereHas('shop.area', fn($q) => $q->where('slug', $areaSlug));
            }

            return $query->count();
        }

        $searchGroups = $gender === 'male' ? ['male', 'both'] : ['female', 'both'];

        $query = Job::where('status', 'active')->whereIn('search_group', $searchGroups);

        if ($areaSlug !== 'all') {
            $query->whereHas('area', fn($q) => $q->where('slug', $areaSlug));
        }

        if ($jobSlug !== 'all') {
            $query->whereHas('jobType', fn($q) => $q->where('slug', $jobSlug));
        }

        return $query->count();
    }

    private function buildUrl(string $gender, string $areaSlug, string $jobSlug): string
    {
        $base = rtrim(config('app.url'), '/');
        return "{$base}/{$gender}/{$areaSlug}/{$jobSlug}/";
    }

    private function writeXml(array $urls): void
    {
        $now = now()->toAtomString();

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
            $xml .= "    <lastmod>{$now}</lastmod>\n";
            $xml .= "    <changefreq>daily</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }
}
