<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnviarBoletaPDF extends Mailable
{
    use Queueable, SerializesModels;

    public $pdf;
    public $filename;

    public function __construct($pdf, $filename)
    {
        $this->pdf = $pdf;
        $this->filename = $filename;
    }

    public function build()
    {
        return $this->markdown('emails.boleta')
            ->subject('Tu boleta electrónica')
            ->attachData($this->pdf, $this->filename, ['mime' => 'application/pdf']);
    }
}

