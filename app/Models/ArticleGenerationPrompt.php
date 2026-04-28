<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleGenerationPrompt extends Model
{
    protected $fillable = ['gender', 'instruction'];

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'female'   => '女性ナイトワーク向け',
            'male'     => '男性ナイトワーク向け',
            'yoasobi' => '夜遊び（客）向け',
            'shop'     => '店舗運営者向け',
            default    => $this->gender,
        };
    }
}
