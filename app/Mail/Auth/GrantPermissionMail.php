<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GrantPermissionMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private string $name,
        private string $token,
        private string $email,
        private string $ip,
        private string $agent,
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail_auth.gran-permission.subject'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.auth.grant-permission',
            with: [
                'name'        => $this->name,
                'url'         => $this->bindUrl(),
                'expireToken' => config('auth.gran-permission.expire'),
                'device'      => $this->agent,
                'ip'          => $this->ip,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }

    private function bindUrl(): string
    {
        $url = Str::replace('{token}', $this->token, config('auth.gran-permission.front_url'));

        return $url;
    }
}
