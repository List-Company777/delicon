<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateArticleVideo;
use App\Models\Article;
use App\Models\ArticleVideo;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArticleVideoController extends Controller
{
    public function generate(Article $article)
    {
        // 生成中は重複実行しない
        $inProgress = ArticleVideo::where('article_id', $article->id)
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($inProgress) {
            return response()->json(['status' => $inProgress->status, 'message' => '生成中です']);
        }

        // 完了・失敗の古いレコードと動画ファイルを削除してから再生成
        ArticleVideo::where('article_id', $article->id)
            ->whereIn('status', ['done', 'failed'])
            ->each(function (ArticleVideo $v) {
                if ($v->video_path) Storage::disk('public')->delete($v->video_path);
                if ($v->audio_path) Storage::disk('public')->delete($v->audio_path);
                $v->delete();
            });

        $video = ArticleVideo::create([
            'article_id' => $article->id,
            'status'     => 'pending',
        ]);

        GenerateArticleVideo::dispatch($video);

        return response()->json(['status' => 'pending', 'video_id' => $video->id]);
    }

    public function status(Article $article)
    {
        $video = ArticleVideo::where('article_id', $article->id)
            ->latest()
            ->first();

        if (!$video) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'status'      => $video->status,
            'video_id'    => $video->id,
            'has_video'   => $video->isDone() && $video->video_path,
            'sns_caption' => $video->sns_caption,
        ]);
    }

    public function destroy(Article $article)
    {
        ArticleVideo::where('article_id', $article->id)
            ->each(function (ArticleVideo $v) {
                if ($v->video_path) Storage::disk('public')->delete($v->video_path);
                if ($v->audio_path) Storage::disk('public')->delete($v->audio_path);
                $v->delete();
            });

        return response()->json(['status' => 'none']);
    }

    public function download(Article $article): StreamedResponse
    {
        $video = ArticleVideo::where('article_id', $article->id)
            ->where('status', 'done')
            ->whereNotNull('video_path')
            ->latest()
            ->firstOrFail();

        $filename = 'article_' . $article->id . '_video.mp4';

        return Storage::disk('public')->download($video->video_path, $filename);
    }
}
