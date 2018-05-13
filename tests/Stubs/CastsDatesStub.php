<?php

namespace Dukhanin\Support\Tests\Stubs;

use Illuminate\Database\Eloquent\Model;
use Dukhanin\Support\Traits\CastsDates;

class CastsDatesStub extends Model
{
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $casts = [
        'date' => 'date',
        'datetime' => 'datetime',
    ];

    use CastsDates;
}
