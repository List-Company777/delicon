<?php

namespace App\Http\Controllers;

use App\Mail\InquiryMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class InquiryController extends Controller
{
    private const CATEGORIES = [
        'サービスについて',
        '掲載・求人情報について',
        '不具合・エラーについて',
        'その他',
    ];

    public function show()
    {
        $categories = self::CATEGORIES;
        return view('inquiry.index', compact('categories'));
    }

    public function send(Request $request)
    {
        $ip  = $request->ip();
        $key = 'inquiry:' . $ip;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['body' => '送信回数の上限に達しました。しばらく時間をおいてから再度お試しください。'])->withInput();
        }
        RateLimiter::hit($key, 3600);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:50'],
            'email'    => ['required', 'email', 'max:200'],
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'body'     => ['required', 'string', 'max:3000'],
        ]);

        Mail::to(config('mail.admin_address', 'nwl-support@nightwork-list.com'))
            ->send(new InquiryMail(
                senderName:  $data['name'],
                senderEmail: $data['email'],
                category:    $data['category'],
                body:        $data['body'],
                senderIp:    $ip,
            ));

        return redirect()->route('inquiry')->with('success', true);
    }
}
