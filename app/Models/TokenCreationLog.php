<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenCreationLog extends Model
{
    protected $fillable = [
        'jti', 'iss', 'aud', 'exp', 
        'nbf', 'iat', 'sub', 'token_type'
    ];
    
}
