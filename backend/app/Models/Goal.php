<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category', 'description'];

    public function trainingPlans()
    {
        return $this->belongsToMany(TrainingPlan::class, 'goal_training_plan');
    }
}
