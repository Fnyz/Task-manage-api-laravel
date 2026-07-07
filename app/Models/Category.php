<?php

namespace App\Models;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, softDeletes;
    protected $fillable = ['user_id', 'name'];
    /**
     * Get the user that owns the category.
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
    /**
     * Get the tasks for the category.
     */
    public function tasks(){
        return $this->hasMany(Task::class);
    }
}
