<?php

namespace App\Events;

use App\Models\FlagAttempt;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlagSubmitted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $attempt;

    public function __construct(FlagAttempt $attempt)
    {
        $this->attempt = $attempt;
    }
}