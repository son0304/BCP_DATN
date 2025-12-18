<?php

namespace App\Mail;

use App\Models\User;
use App\Models\MerchantProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Confirm_Merchant extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $merchant;
    public $url;

    public function __construct(User $user, MerchantProfile $merchant, $url)
    {
        $this->user = $user;
        $this->merchant = $merchant;
        $this->url = $url;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Hồ sơ đối tác đã được phê duyệt');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.merchant_approved');
    }
}
