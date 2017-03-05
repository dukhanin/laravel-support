<?php

namespace Tests\Unit;

use Dukhanin\Support\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{

    public function testBeforeWithNewItem()
    {
        $array = [
            'one'  => 'One',
            'two'  => 'Two',
            // inserting 'Three' here (before 'Four')
            'four' => 'Four'
        ];

        Arr::before($array, 'three', 'Three', 'four');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three',
                'four'  => 'Four'
            ]);
    }


    public function testBeforeWithExistsItem()
    {
        $array = [
            'three' => 'Not three',
            'one'   => 'One',
            'two'   => 'Two',
            // moving 'Not three' here (before four)
            // with replaced value to 'Three'
            'four'  => 'Four'
        ];

        Arr::before($array, 'three', 'Three', 'four');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three',
                'four'  => 'Four'
            ]);
    }


    public function testBeforeWithUnexistsNeighbor()
    {
        $array = [
            // inserting 'One' here (at the begining of array)
            'two'   => 'Two',
            'three' => 'Three'
        ];

        Arr::before($array, 'one', 'One', 'dummy');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }


    public function testBeforeWithUndefinedArray()
    {
        Arr::before($array, 'two', 'Two');
        Arr::before($array, 'one', 'One');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two'
            ]);
    }


    public function testBeforeWithNullKeys()
    {
        $array = [
            // inserting 'One' here (at the begining of array)
            'two'   => 'Two',
            'three' => 'Three'
        ];

        Arr::before($array, null, 'One');

        $this->assertTrue($array === [
                'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }


    public function testAfterWithNewItem()
    {
        $array = [
            'one'  => 'One',
            'two'  => 'Two',
            // inserting 'Three' here (after two)
            'four' => 'Four'
        ];

        Arr::after($array, 'three', 'Three', 'two');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three',
                'four'  => 'Four'
            ]);
    }


    public function testAfterWithExistsItem()
    {
        $array = [
            'three' => 'Not three',
            'one'   => 'One',
            'two'   => 'Two',
            // moving 'Not three' here (after two)
            // with replaced value to 'Three' 
            'four'  => 'Four'
        ];

        Arr::after($array, 'three', 'Three', 'two');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three',
                'four'  => 'Four'
            ]);
    }


    public function testAfterWithUnexistsNeighbor()
    {
        $array = [
            'one' => 'One',
            'two' => 'Two'
            // inserting 'Three' here (at the end of array)
        ];

        Arr::after($array, 'three', 'Three', 'dummy');

        $this->assertTrue($array === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }


    public function testAfterWithUndefinedArray()
    {
        Arr::after($array, 'one', 'One');
        Arr::after($array, 'two', 'Two');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two'
            ]);
    }


    public function testAfterWithNullKeys()
    {
        $array = [
            'one' => 'One',
            'two' => 'Two'
            // inserting 'Three' here  (at the end of array)
        ];

        Arr::after($array, null, 'Three');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two',
                'Three'
            ]);
    }


    public function testBeforeDotNotationWithNewItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            // inserting 'Two' here (before three)
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'two', [
            'a' => 'Two A',
            'b' => 'Two B',
            'c' => 'Two C'
        ], 'three');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithExistsItem()
    {
        $array = [
            'two'   => [
                'a' => 'Invalida A',
                'b' => 'Invalida B',
                'c' => 'Invalida C'
            ],
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            // moving 'Two' here (before three)
            // with replacing values to valid
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'two', [
            'a' => 'Two A',
            'b' => 'Two B',
            'c' => 'Two C'
        ], 'three');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithUnexistsNeighbor()
    {
        $array = [
            // inserting 'One' here (at the beginig of array)
            'two'   => [
                'a' => 'Two A',
                'b' => 'Two B',
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'one', [
            'a' => 'One A',
            'b' => 'One B',
            'c' => 'One C'
        ], 'dummy');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithNestedNewItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two'   => [
                'a' => 'Two A',
                // inserting 'Two' B here (before two.c)
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'b', 'Two B', 'two.c');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithNestedExistsItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two'   => [
                'b' => 'Not One B',
                'a' => 'Two A',
                // moving 'Not One B' here (before two.c)
                // with replaced value to 'One B'
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'b', 'Two B', 'two.c');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithNestedUnexistsNeighbor()
    {
        $array = [
            'one'   => [
                // inserting 'One A' (at the beginig of nested array)
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two'   => [
                'a' => 'Two A',
                'b' => 'Two B',
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::beforeDotNotation($array, 'a', 'One A', 'one.dummy');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testBeforeDotNotationWithUndefinedArray()
    {
        Arr::beforeDotNotation($array, 'two', 'Two');
        Arr::beforeDotNotation($array, 'one', 'One');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two',
            ]);
    }


    public function testBeforeDotNotationWithNullKeys()
    {
        $array = [
            // inserting 'One' here (at the begining of array)
            'two'   => 'Two',
            'three' => 'Three'
        ];

        Arr::beforeDotNotation($array, null, 'One');

        $this->assertTrue($array === [
                'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }


    public function testAfterDotNotationWithNewItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            // inserting 'Two' here (after one)
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::afterDotNotation($array, 'two', [
            'a' => 'Two A',
            'b' => 'Two B',
            'c' => 'Two C'
        ], 'one');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithExistsItem()
    {
        $array = [
            'two'   => [
                'a' => 'Invalida A',
                'b' => 'Invalida B',
                'c' => 'Invalida C'
            ],
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            // moving 'Two' here (after one)
            // with replacing values to valid
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::afterDotNotation($array, 'two', [
            'a' => 'Two A',
            'b' => 'Two B',
            'c' => 'Two C'
        ], 'one');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithUnexistsNeighbor()
    {
        $array = [
            'one' => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two' => [
                'a' => 'Two A',
                'b' => 'Two B',
                'c' => 'Two C'
            ]
            // inserting 'Three' here (at the end of array)
        ];

        Arr::afterDotNotation($array, 'three', [
            'a' => 'Three A',
            'b' => 'Three B',
            'c' => 'Three C'
        ], 'dummy');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithNestedNewItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two'   => [
                'a' => 'Two A',
                // inserting 'Two B' here (after two.a)
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::afterDotNotation($array, 'b', 'Two B', 'two.a');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithNestedExistsItem()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B',
                'c' => 'One C'
            ],
            'two'   => [
                'b' => 'Not One B',
                'a' => 'Two A',
                // moving 'Not One B' here (after two.a)
                // with replaced value to 'One B'
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::afterDotNotation($array, 'b', 'Two B', 'two.a');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithNestedUnexistsNeighbor()
    {
        $array = [
            'one'   => [
                'a' => 'One A',
                'b' => 'One B'
                // inserting 'One C' (at the end of nested array)
            ],
            'two'   => [
                'a' => 'Two A',
                'b' => 'Two B',
                'c' => 'Two C'
            ],
            'three' => [
                'a' => 'Three A',
                'b' => 'Three B',
                'c' => 'Three C'
            ]
        ];

        Arr::afterDotNotation($array, 'c', 'One C', 'one.dummy');

        $this->assertTrue($array === [
                'one'   => [
                    'a' => 'One A',
                    'b' => 'One B',
                    'c' => 'One C'
                ],
                'two'   => [
                    'a' => 'Two A',
                    'b' => 'Two B',
                    'c' => 'Two C'
                ],
                'three' => [
                    'a' => 'Three A',
                    'b' => 'Three B',
                    'c' => 'Three C'
                ]
            ]);
    }


    public function testAfterDotNotationWithUndefinedArray()
    {
        Arr::afterDotNotation($array, 'one', 'One');
        Arr::afterDotNotation($array, 'two', 'Two');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two'
            ]);
    }


    public function testAfterDotNotationWithNullKeys()
    {
        $array = [
            'one' => 'One',
            'two' => 'Two',
            // inserting 'Three' here (at the end of array)
        ];

        Arr::afterDotNotation($array, null, 'Three');

        $this->assertTrue($array === [
                'one' => 'One',
                'two' => 'Two',
                'Three'
            ]);
    }
}