<?php

namespace App\Services;

use Illuminate\Support\Str;

class TokenService
{
    public function makeApiKey()
    {
        return base64_encode(Str::random(32));
    }
}