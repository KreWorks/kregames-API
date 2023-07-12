<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Link;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Link>
 */
class LinkFactory extends Factory
{
    protected $model = Link::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'linktype_id' => Str::uuid(),
            'linkable_type' =>get_class(User::all()->first()),
            'linkable_id' => User::all()->first()->id,
            'link' => fake()->url(),
            'display_text' => fake()->title(),
            'visible' => true,
        ];
    }

}