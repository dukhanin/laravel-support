<?php
namespace Dukhanin\Support\Traits;

use Illuminate\Events\Dispatcher as DefaultDispatcher;
use Illuminate\Contracts\Events\Dispatcher;

trait DispatchesEvents
{

    protected $eventDispatcher;


    public function getEventDispatcher()
    {
        if (is_null($this->eventDispatcher)) {
            $this->initEventDispatcher();
        }

        return $this->eventDispatcher;
    }


    public function initEventDispatcher()
    {
        $this->eventDispatcher = new DefaultDispatcher();
    }


    public function setEventDispatcher(Dispatcher $dispatcher)
    {
        $this->eventDispatcher = $dispatcher;
    }


    public function unsetEventDispatcher()
    {
        $this->eventDispatcher = null;
    }


    public function registerEvent($event, $callback, $priority = 0)
    {
        $this->getEventDispatcher()->listen($event, $callback, $priority);
    }


    public function fireEvent($event, $halt = true)
    {
        $method = $halt ? 'until' : 'fire';

        return $this->getEventDispatcher()->$method($event, $this);
    }

}