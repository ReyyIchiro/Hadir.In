<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendLeaveStatusEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $tenantName,
        public string $typeName,
        public string $dateRange,
        public string $statusLabel,
        public ?string $reviewNote,
        public string $detailUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan izin Anda '.$this->statusLabel,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.leave-status',
        );
    }
}
