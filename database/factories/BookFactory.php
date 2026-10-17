<?php 
  
namespace Database\Factories; 
  
use App\Models\Author; 
use Illuminate\Database\Eloquent\Factories\Factory; 
  
class BookFactory extends Factory 
{ 
    public function definition(): array 
    { 
        return [ 
            'title' => fake()->sentence(3), 
            'isbn' => fake()->unique()->isbn13(), 
            'published_year' => fake()->year(), 
            'price' => fake()->randomFloat(2, 50000, 500000), 
            'stock' => fake()->numberBetween(1, 100), 
            'author_id' => Author::factory(), 
        ]; 
    } 
} 