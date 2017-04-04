<?php
namespace Dukhanin\Support\Traits;

trait HasSettings
{
    protected $settingsInited = false;

    public function settings($key = null, $value = null)
    {
        if (! $this->settingsInited && method_exists($this, 'initSettings')) {
            $this->initSettings();
            $this->settingsInited = true;
        }

        $attr = $this->settingsAttribute();

        if (is_null($key)) {
            return $this->{$attr};
        }

        if (is_array($key)) {
            foreach ($key as $_key => $value) {
                $this->settings($_key, $value);
            }

            return;
        }

        if (is_null($value)) {
            return array_get((array) $this->{$attr}, $key);
        }

        // @dukhanin вычурная конструкция на случай использование getter-ов
        $settings = $this->{$attr};
        array_set($settings, $key, $value);
        $this->{$attr} = $settings;
    }

    protected function settingsAttribute()
    {
        return 'settings';
    }
}