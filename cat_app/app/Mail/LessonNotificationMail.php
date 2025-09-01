<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LessonNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;

    /**
     * Create a new message instance.
     */
    public function __construct($mailData)
    {
        $this->mailData = $mailData;
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

    public function build()
    {

        return $this->subject($this->mailData['subject'])
                    ->view('admin.mail.lesson_notification')
                    ->with([
                        'subject' => $this->mailData['subject'],
                        'body' => $this->mailData['body'],
                        'attachmentUrl' => isset($this->mailData['attachment'])
                            ? asset('storage/' . $this->mailData['attachment'])
                            : null,
                    ]);
    }
}
