<?php

namespace Dukhanin\Support\Tests\Stubs;

use Illuminate\Support\Collection;
use Dukhanin\Support\Traits\ClearableCollection;

class ClearableCollectionStub extends Collection
{
    use ClearableCollection;
}
