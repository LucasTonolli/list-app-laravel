<?php

namespace Database\Factories;

use App\Models\CustomList;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomList>
 */
class CustomListFactory extends Factory
{
    protected $model = CustomList::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (CustomList $list) {
            $list->sharedWith()->attach($list->owner_uuid, ['role' => 'owner']);
        });
    }
}
