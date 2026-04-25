<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature   = 'admin:create';
    protected $description = '管理者ユーザーを作成します';

    public function handle(): int
    {
        $this->info('管理者ユーザーを作成します。');

        $name     = $this->ask('名前');
        $email    = $this->ask('メールアドレス');
        $password = $this->secret('パスワード');

        if (User::where('email', $email)->exists()) {
            $this->error("メールアドレス {$email} はすでに使用されています。");
            return self::FAILURE;
        }

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
            'role'     => 'admin',
        ]);

        $this->info("管理者ユーザー「{$name}」を作成しました。");
        $this->info("ログインURL: /login/");

        return self::SUCCESS;
    }
}
