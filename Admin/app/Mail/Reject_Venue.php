<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Venue; // <--- QUAN TRỌNG: Import model Venue
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Reject_Venue extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $venue;
    public $url;

    public function __construct(User $user, Venue $venue, $url)
    {
        $this->user = $user;
        $this->venue = $venue;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Thông báo: Địa điểm chưa đạt yêu cầu');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reject_venue');
    }
}
