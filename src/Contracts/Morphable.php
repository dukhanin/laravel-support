<?php
namespace Dukhanin\Support\Contracts;

interface Morphable
{
    public function getMorphType();

    public function getMorphKey();
}