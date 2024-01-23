<?php

namespace App\Mail;

use App\Models\User;
use App\Models\diagrama;
use AWS\CRT\HTTP\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\View\View;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class CorreosMailable extends Mailable
{
    use Queueable, SerializesModels;


    public $invitado;

    /**
     * Create a new message instance.
     */
    public function __construct($invitado)
    {
        $this->invitado = $invitado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitacion a la plataforma de Mis Pizarras',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->view('VistaEmail.index',[
            'invitado' => $this->invitado,
        ]);
    }
    // public function content(): Content
    // {
    //     return $this->view('VistaEmail.index');
    // }



    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
