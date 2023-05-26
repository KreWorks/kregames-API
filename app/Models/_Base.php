<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class _Base extends Model
{
    use HasFactory;

    /**
     * We need to create a uuid on create
     */
    public static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }

    public $incrementing = false;

    public function getDeleteStringAttribute(): string
    {
        return "ID: ".$this->id;
    }
}