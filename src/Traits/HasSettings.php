<?php
namespace Dukhanin\Support\Traits;

trait HasSettings
{

    protected $settingsInited = false;


    public function settings($key = null, $value = null)
    {
        if ( ! $this->settingsInited && method_exists($this, 'initSettings')) {
            $this->initSettings();
            $this->settingsInited = true;
        }

        if (is_null($key)) {
            return $this->settings;
        }

        if (is_array($key)) {
            foreach ($key as $_key => $value) {
                $this->settings($_key, $value);
            }

            return;
        }

        if (is_null($value)) {
            return array_get((array) $this->settings, $key);
        }

        // @dukhanin вычурная конструкция на случай использование getter-ов
        $settings = $this->settings;
        array_set($settings, $key, $value);
        $this->settings = $settings;
    }
}