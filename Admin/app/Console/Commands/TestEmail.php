<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email verification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Tạo user fake để test
        $user = new User();
        $user->name = 'Test User';
        $user->email = $email;

        $verificationUrl = 'http://localhost/verify-email/test-token-123';

        try {
            Mail::to($email)->send(new EmailVerificationMail($user, $verificationUrl));
            $this->info("Email sent successfully to: {$email}");
            $this->info("Check storage/logs/laravel.log for email content");
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
        }
    }
}
