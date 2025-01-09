<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'name',
        'name_id',
        'short_des',
        'short_des_id',
        'location',
        'lat',
        'lon',
        'required',
        'duration',
        'start_time',
        'finish_time',
        'currency',
        'price_start',
        'price_complate',
        'price_type',
        'activity_des',
        'activity_des_id',
        'included',
        'included_id',
        'whattobring',
        'whattobring_id',
        'note',
        'note_id',
        'visitor',
        'rating',
        'highlight',
        'highlight_id',
        'pickup_time',
        'pickup_time_id',
        'code',
        'cancelation',
        'confirmation_detail',
        'confirmation_detail_id',
        'how_to_use',
        'how_to_use_id',
        'payment',
        'payment_id',
        'price_usd',
        'price_idr',
        'lang',
        'visibility',
        'user_id',
        'last_viewed_at',
        'view_count'
    ];
}
