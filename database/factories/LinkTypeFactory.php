<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\LinkType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LinkType>
 */
class LinkTypeFactory extends Factory
{
    protected $model = LinkType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'name' => fake()->name(),
            'font_awesome' => $this->getRadomFA(),
            'color' => "#aabbcc"
        ];
    }

    protected function getRadomFA()
    {
        return "fa fa-alma";
    }

}