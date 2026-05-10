<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'category', 'description', 'instructions'];

    public function trainingPlans()
    {
        return $this->belongsToMany(TrainingPlan::class, 'training_plan_exercise');
    }
}
