<?php

namespace App\Mail;

use App\Models\User;
use App\Models\MerchantProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Reject_Merchant extends Mailable
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
        return new Envelope(subject: 'Thông báo: Hồ sơ đối tác chưa hợp lệ');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reject_merchant');
    }
}
