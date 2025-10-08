<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailJa extends BaseVerifyEmail
{
    /**
     * メール内容を日本語でカスタマイズ
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('【' . config('app.name') . '】メールアドレスの確認')
            ->line($notifiable->name . ' 様')
            ->line('このたびはご登録ありがとうございます。')
            ->line('以下のボタンをクリックして、メールアドレスの確認を完了してください。')
            ->action('メールアドレスを確認する', $verificationUrl)
            ->line('このメールに心当たりがない場合は、破棄してください。');
    }

    /**
     * 認証リンクの生成（標準のままでOK）
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
        );
    }
}
