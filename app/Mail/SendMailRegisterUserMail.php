<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMailRegisterUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User   $user,
        public string $passwordPlain,
    )
    {
        //
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Registro de usuário",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.user_register',
            with: [
                "user"          => $this->user,
                "passwordPlain" => $this->passwordPlain,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
