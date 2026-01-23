<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forms extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',  // allow mass assignment
    ];

    // Relationship: form has many fields
    public function fields()
    {
        // explicitly define foreign key as form_id
        return $this->hasMany(FormField::class, 'form_id', 'id');
    }
    public function submissions()
    {
        return $this->hasMany(FormSubmission::class, 'form_id'); 
    }
    //  public function submissions()
    // {
    //     return $this->hasMany(FormSubmission::class);
    // }
}
