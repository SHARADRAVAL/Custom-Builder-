<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmissionValue extends Model
{
    use HasFactory;

    protected $table = 'form_submission_values';

    protected $fillable = ['submission_id', 'field_id', 'value'];

    public function submission()
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id', 'id');
    }

    public function field()
    {
        return $this->belongsTo(FormField::class, 'field_id', 'id');
    }
}
