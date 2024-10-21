<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Category;
use App\Models\StudentCourseEnrollment;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'icon', 'description', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentCourseEnrollment::class);
    }
}
