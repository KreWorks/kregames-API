<?php

namespace App\Models;

use App\Models\_Base as Base;

class Game extends Base
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'publish_date',
        'visible',
    ];

    /** 
     * THe user this game blongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDeleteStringAttribute():string
    {
        return $this->name;
    }
}
