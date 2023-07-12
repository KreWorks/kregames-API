<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\_Base as Base;

class LinkType extends Base
{
    protected $table = 'linktypes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'font_awesome',
        'color',
    ];

    public function getDeleteStringAttribute():string
    {
        return $this->name;
    }

    /**
     * The links that has this linktype
     */
    public function links()
    {
        return $this->hasMany(Link::class);
    }


}
