<?php

namespace App\Mail;

use App\Models\User; // Import Model User
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VenueApprovedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $url; // URL trang quản lý

    public function __construct(User $user, $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Chúc mừng! Địa điểm của bạn đã được phê duyệt');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.venue_approved');
    }
}
