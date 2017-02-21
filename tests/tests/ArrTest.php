<?php

namespace Tests\Unit;

use Dukhanin\Support\Arr;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ArrTest extends TestCase
{

    public function testBefore()
    {
        $array = [
            'key.four' => 'Not four',
            'key.one'  => 'One',
            'key.two'  => 'Two',   
            // moving 'Not four' here with replaced value to 'Four'
            // and inserting 'Three' here, before 'Four'
            'key.five' => 'Five'
        ];

        Arr::before($array, 'key.four', 'Four', 'key.five');

        Arr::before($array, 'key.three', 'Three', 'key.four');

        $this->assertTrue($array === [
                'key.one'   => 'One',
                'key.two'   => 'Two',
                'key.three' => 'Three',
                'key.four'  => 'Four',
                'key.five'  => 'Five'
            ]);
    }


    public function testAfter()
    {
        $array = [
            'key.four' => 'Not four',
            'key.one'  => 'One',
            'key.two'  => 'Two',
            // moving 'Not four' here with replaced value to 'Four'
            // and inserting 'Three' here, before 'Four'
            'key.five' => 'Five'
        ];

        Arr::after($array, 'key.three', 'Three', 'key.two');

        Arr::after($array, 'key.four', 'Four', 'key.three');

        $this->assertTrue($array === [
                'key.one'   => 'One',
                'key.two'   => 'Two',
                'key.three' => 'Three',
                'key.four'  => 'Four',
                'key.five'  => 'Five'
            ]);
    }


    public function testBeforeWithoutTargetKey()
    {
        $array = [
            // inserting 'One' here
            'key.two'   => 'Two',
            'key.three' => 'Three',
            'key.four'  => 'Four'
        ];

        Arr::before($array, 'key.one', 'One');

        $this->assertTrue($array === [
                'key.one'   => 'One',
                'key.two'   => 'Two',
                'key.three' => 'Three',
                'key.four'  => 'Four'
            ]);
    }


    public function testAfterWithoutTargetKey()
    {
        $array = [
            'key.one'   => 'One',
            'key.two'   => 'Two',
            'key.three' => 'Three'
            // inserting 'Four' here
        ];

        Arr::after($array, 'key.four', 'Four');

        $this->assertTrue($array === [
                'key.one'   => 'One',
                'key.two'   => 'Two',
                'key.three' => 'Three',
                'key.four'  => 'Four'
            ]);

    }


    public function testBeforeDotNotation()
    {
        $array = [
            'beverages' => [
                'vodka'   => 'Absolute',
                // inserting 'Bud' beer here
                'whiskey' => 'Jack Daniels',
                'soda'    => 'Coca Cola',
            ],
            'fruits'    => [
                // adding 'Banana' here, before 'bad' key (which doesnt exists)
                'orange' => 'Orange',
                'yellow' => 'Lemon',
                'pink'   => 'Dragon',
                // removing 'Lemon' and setting 'Melon' instead to this position
                'green'  => 'Apple'
            ]
        ];

        Arr::beforeDotNotation($array, 'beer.key', 'Bud', 'beverages.whiskey');

        Arr::beforeDotNotation($array, 'yellow', 'Menon', 'fruits.green');

        Arr::beforeDotNotation($array, 'tall', 'Banana', 'fruits.bad');

        $this->assertTrue($array === [
                'beverages' => [
                    'vodka'    => 'Absolute',
                    'beer.key' => 'Bud',
                    'whiskey'  => 'Jack Daniels',
                    'soda'     => 'Coca Cola',
                ],
                'fruits'    => [
                    'tall'   => 'Banana',
                    'orange' => 'Orange',
                    'pink'   => 'Dragon',
                    'yellow' => 'Menon',
                    'green'  => 'Apple'
                ]
            ]);
    }


    public function testAfterDotNotation()
    {
        $array = [
            'beverages' => [
                'vodka'   => 'Absolute',
                // inserting 'Bud' beer here
                'whiskey' => 'Jack Daniels',
                'soda'    => 'Coca Cola',
            ],
            'fruits'    => [
                'orange' => 'Orange',
                'yellow' => 'Lemon',
                'pink'   => 'Dragon',
                // removing 'Lemon' and setting 'Melon' instead to this position
                'green'  => 'Apple'
                // adding 'Banana' here, after 'bad' key (which doesnt exists)
            ]
        ];

        Arr::afterDotNotation($array, 'beer.key', 'Bud', 'beverages.vodka');

        Arr::afterDotNotation($array, 'yellow', 'Menon', 'fruits.pink');

        Arr::afterDotNotation($array, 'tall', 'Banana', 'fruits.bad');

        $this->assertTrue($array === [
                'beverages' => [
                    'vodka'    => 'Absolute',
                    'beer.key' => 'Bud',
                    'whiskey'  => 'Jack Daniels',
                    'soda'     => 'Coca Cola',
                ],
                'fruits'    => [
                    'orange' => 'Orange',
                    'pink'   => 'Dragon',
                    'yellow' => 'Menon',
                    'green'  => 'Apple',
                    'tall'   => 'Banana'
                ]
            ]);

    }


    public function testBeforeDotNotationWithoutTargetKey()
    {
        $array = [
            // inserting [ crunchy => chips ] here

            'beverages' => [
                'vodka'   => 'Absolute',
                'whiskey' => 'Jack Daniels',
                'soda'    => 'Coca Cola',
            ],
            'fruits'    => [
                'orange' => 'Orange',
                'yellow' => 'Lemon',
                'green'  => 'Apple'
            ]
        ];

        Arr::beforeDotNotation($array, 'snacks.key', [ 'crunchy' => 'Chips' ]);

        $this->assertTrue($array === [
                'snacks.key' => [
                    'crunchy' => 'Chips'
                ],
                'beverages'  => [
                    'vodka'   => 'Absolute',
                    'whiskey' => 'Jack Daniels',
                    'soda'    => 'Coca Cola',
                ],
                'fruits'     => [
                    'orange' => 'Orange',
                    'yellow' => 'Lemon',
                    'green'  => 'Apple'
                ]
            ]);
    }


    public function testAfterDotNotationWithoutTargetKey()
    {
        $array = [
            'beverages' => [
                'vodka'   => 'Absolute',
                'whiskey' => 'Jack Daniels',
                'soda'    => 'Coca Cola',
            ],
            'fruits'    => [
                'orange' => 'Orange',
                'yellow' => 'Lemon',
                'green'  => 'Apple'
            ]
            // inserting [ crunchy => chips ] here
        ];

        Arr::afterDotNotation($array, 'snacks.key', [ 'crunchy' => 'Chips' ]);

        $this->assertTrue($array === [
                'beverages'  => [
                    'vodka'   => 'Absolute',
                    'whiskey' => 'Jack Daniels',
                    'soda'    => 'Coca Cola',
                ],
                'fruits'     => [
                    'orange' => 'Orange',
                    'yellow' => 'Lemon',
                    'green'  => 'Apple'
                ],
                'snacks.key' => [
                    'crunchy' => 'Chips'
                ]
            ]);
    }
}