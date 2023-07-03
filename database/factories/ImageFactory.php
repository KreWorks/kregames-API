<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Image;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class ImageFactory extends Factory
{
    protected $model = Image::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'type' => fake()->name(),
            'imageable_type' => get_class(User::all()->first()),
            'imageable_id' => User::all()->first()->id,
            'path' => 'images/user/'.User::all()->first()->username.'/avatar',
            'title' => fake()->title(),
        ];
    }
}