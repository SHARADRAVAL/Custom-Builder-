<?php

namespace App\Models;
// use app\Models\FormSubmissionValue;
use App\Models\FormSubmissionValue; 

use Illuminate\Database\Eloquent\Model;
class FormSubmission extends Model {

    protected $fillable = [
        'form_id',
    ];

    public function form()
    {
        return $this->belongsTo(Forms::class, 'form_id', 'id');
    }
    public function values()
    {
        return $this->hasMany(FormSubmissionValue::class, 'submission_id', 'id'); 
    }

}

