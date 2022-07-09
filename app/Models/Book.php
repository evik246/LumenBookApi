<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $table = 'books';

    protected $fillable = [
        'id',
        'title',
        'year',
        'pages',
        'cover',
        'description'
    ];

    protected $hidden = [
        'language_id',
        'pivot'
    ];

    public function language()
    {
        return $this->belongsTo('App\Models\Language', 'language_id', 'id');
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }

    public function authors()
    {
        return $this->belongsToMany(Author::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
