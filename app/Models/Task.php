<?php

namespace App\Models;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

class Task extends Model
{
     /** @use HasFactory<TaskFactory> */
    use HasFactory, SoftDeletes;
    protected $fillable = ['user_id', 'category_id', 'title', 'description', 'is_completed'];
    /**
     * Get the user that owns the task.
     */
    public function user(){
        return $this->belongsTo(User::class);
    }
    /**
     * Get the category that the task belongs to.
     */
    public function category(){
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'due_date' => 'datetime',
        ];
    }
}
