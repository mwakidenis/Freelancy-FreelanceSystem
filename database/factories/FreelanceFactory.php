<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\{Freelance,User};
use Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Freelance>
 */
class FreelanceFactory extends Factory
{
    protected $model = Freelance::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->word();

        return [
            'client_id' => $this->faker->randomElement([2,4,5]),
            'title' => $this->faker->word(),
            'slug' => $this->title,
            'description' => $this->faker->sentence(),
            'category' => $this->faker->word(),
            'rate' => $this->faker->numberBetween(100,500), 
            'status' => 'active'
        ];
    }

    public function generateSlug($title){
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        while(Freelance::where('slug',$slug)->exists()){
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
