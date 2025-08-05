<?php

namespace App\Jobs;

use App\Mail\SendMailRegisterUserMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMailRegisterUserJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User   $user,
        public string $passwordPlain,
    )
    {
        //
    }

    public function handle(): void
    {
        Mail::to($this->user->email)
            ->send(new SendMailRegisterUserMail(
                    $this->user,
                    $this->passwordPlain,
                )
            );
    }
}
