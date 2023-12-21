<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'image'
    ];

    public function scopeSearchable($query, $search){
        if($search) return $query->where('title', 'LIKE', '%'.$search.'%');
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => empty($value) ? NULL : asset('/storage/blogs/' . $value)
        );
    }
}
