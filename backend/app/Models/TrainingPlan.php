<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TrainingPlan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category', 'description'];

    public function goals()
    {
        return $this->belongsToMany(Goal::class, 'goal_training_plan');
    }

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'training_plan_exercise');
    }
}
