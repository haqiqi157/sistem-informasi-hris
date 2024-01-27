<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Responsibility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function responsibilities()
    {
        return $this->hasMany(Responsibility::class);
    }

    public function employees()
    {
        $this->hasMany(Employee::class);
    }
}
