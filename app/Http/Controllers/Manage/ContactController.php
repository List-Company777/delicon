<?php

namespace App\Http\Controllers\Manage;

use App\Mail\ShopContactMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends BaseController
{
    private const CATEGORIES = [
        '掲載内容について',
        '請求・支払いについて',
        '機能・サービスへの要望',
        'その他',
    ];

    public function show()
    {
        $shop       = $this->shopOrFail();
        $categories = self::CATEGORIES;

        return view('manage.contact.index', compact('shop', 'categories'));
    }

    public function send(Request $request)
    {
        $this->shopOrFail();

        $data = $request->validate([
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'subject'  => ['required', 'string', 'max:100'],
            'body'     => ['required', 'string', 'max:3000'],
        ]);

        $user = auth()->user();
        $shop = $this->getShop();

        Mail::to(config('mail.admin_address', 'nwl-support@nightwork-list.com'))
            ->send(new ShopContactMail(
                shopName:    $shop?->name ?? '不明',
                senderName:  $user->name,
                senderEmail: $user->email,
                category:    $data['category'],
                contactSubject: $data['subject'],
                body:        $data['body'],
            ));

        return redirect()->route('manage.contact')
            ->with('success', 'お問い合わせを送信しました。内容を確認の上、ご連絡いたします。');
    }
}
