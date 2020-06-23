<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    const TITLE = [
        'mrs',
        'Prof',
        'mr',
        'Dr',
    ];
}
