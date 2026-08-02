<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSellSubmissionNote extends Model
{
    protected $fillable = [
        'customer_sell_submission_id', 'user_id', 'author_name', 'note',
    ];

    public function submission()
    {
        return $this->belongsTo(CustomerSellSubmission::class, 'customer_sell_submission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
