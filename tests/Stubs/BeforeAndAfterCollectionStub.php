<?php

namespace Dukhanin\Support\Tests\Stubs;

use Illuminate\Support\Collection;
use Dukhanin\Support\Traits\BeforeAndAfterCollection;

class BeforeAndAfterCollectionStub extends Collection
{
    use BeforeAndAfterCollection;
}
