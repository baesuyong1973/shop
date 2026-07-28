<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminOrderNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "新規注文が入りました（注文番号：{$this->order->id}）",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.orders.admin-notification',
            with: [
                'order' => $this->order,
            ],
        );
    }
}
