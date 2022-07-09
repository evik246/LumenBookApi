<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = [
        'id',
        'name'
    ];

    public function books()
    {
        return $this->hasMany('App\Models\Book', 'language_id', 'id');
    }
}
