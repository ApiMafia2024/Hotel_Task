<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'display_name',
        'name',
        'country_code',
        'country_name',
        'state',
        'city_name',
        'address',
        'description',
        'zip_code',
        'star_rating',
        'lat',
        'lng',
        'room_count',
        'phone',
        'fax',
        'email',
        'website',
        'property_category',
        'property_sub_category',
        'chain_code',
        'facilities',
        'images',
        'priority'
    ];
}
