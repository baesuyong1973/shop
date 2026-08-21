<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactInquiry extends Mailable
{
    use Queueable, SerializesModels;

    private const TYPE_LABELS = [
        'bug' => '不具合の報告',
        'shop_registration' => '店舗登録のお問い合わせ',
        'other' => 'その他のお問い合わせ',
    ];

    public function __construct(
        public string $name,
        public string $email,
        public string $phone,
        public string $type,
        public string $message,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'お問合せがありました',
            replyTo: [$this->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact.inquiry',
            with: [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'typeLabel' => self::TYPE_LABELS[$this->type] ?? $this->type,
                'message' => $this->message,
            ],
        );
    }
}
