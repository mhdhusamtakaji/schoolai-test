<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Research extends Model
{
    use HasFactory;

    protected $fillable = ['student_name', 'student_nationality', 'student_phone_number', 'pdf_file'];

}
