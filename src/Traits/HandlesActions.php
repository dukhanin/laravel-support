<?php

namespace Dukhanin\Support\Traits;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Exception;

trait HandlesActions
{

    use AuthorizesRequests;

    protected $request;

    protected $url;


    public function before()
    {
    }


    public function after()
    {
    }


    public function init()
    {
    }


    public function handle()
    {
    }


    public function execute()
    {
        $info = $this->getRequestDetails();

        return $this->executeAction($info['action'], $info['arguments']);
    }


    public function executeAction($action, $arguments)
    {
        $_arguments = array_values($arguments);
        for ($i = count($_arguments); $i < 10; $i++) {
            $_arguments[] = null;
        }

        $method = $this->getActionMethodName($action);

        $this->init();
        $this->handle();

        $this->before();
        $res = $this->$method(...$_arguments);
        $this->after();

        return $res;
    }


    public function actionIndex()
    {
    }


    public function getUrl()
    {
        if (is_null($this->url)) {
            $this->initUrl();
        }

        return $this->url;
    }


    public function initUrl()
    {
        if ($currentRoute = Route::getCurrentRoute()) {
            $this->url = route($currentRoute->getName());
        } else {
            throw new Exception('Auth guard [' . __CLASS__ . '::url] is not defined.' . ' Set it manually or override [' . __CLASS__ . '::initUrl()] method.');
        }
    }


    public function setUrl($url)
    {
        $this->url = $url;
    }


    public function getRequest()
    {
        if ( ! $this->request instanceof Request) {
            $this->initRequest();
        }

        return $this->request;
    }


    public function initRequest()
    {
        $this->request = Request::capture();
    }


    public function setRequest(Request $request)
    {
        $this->request = $request;
    }


    public function parseRequestDetails()
    {
        $result = [
            'action'    => 'index',
            'arguments' => [ ]
        ];

        $thisPath   = urlbuilder($this->getUrl())->path();
        $requestUrl = urlbuilder($this->getRequest()->path())->shift($thisPath);

        if (count($requestUrl->segments()) == 0) {
            return $result;
        }

        $result['action']    = $requestUrl->shiftSegment();
        $result['arguments'] = $requestUrl->segments();

        return $result;
    }


    public function getRequestDetails($key = null, $default = null)
    {
        $details = $this->parseRequestDetails();

        if ($details['action'] != 'index') {
            $actionMethodName = $this->getActionMethodName($details['action']);

            if ( ! method_exists($this, $actionMethodName)) {
                array_unshift($details['arguments'], $details['action']);
                $details['action'] = 'index';
            }
        }

        return array_get($details, $key, $default);
    }


    public function getActionMethodName($action)
    {
        $action = strtolower($action);
        $method = 'action' . studly_case($action);

        return $method;
    }
}