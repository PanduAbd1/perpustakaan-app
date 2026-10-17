<?php 
  
namespace App\Models; 
  
use Illuminate\Database\Eloquent\Factories\HasFactory; 
use Illuminate\Database\Eloquent\Model; 
  
class Book extends Model 
{ 
    use HasFactory; 
  
    protected $fillable = ['title', 'isbn', 'published_year', 'price', 'stock', 'author_id']; 
  
    public function author() 
    { 
        return $this->belongsTo(Author::class); 
    } 
  
    public function categories() 
    { 
        return $this->belongsToMany(Category::class); 
    } 
} 
  