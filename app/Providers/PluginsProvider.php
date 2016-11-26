<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PluginsProvider extends ServiceProvider
{
    public function boot()
    {
        $path = app_path() . '/Plugins/';
        $base_namespace = 'App\\Plugins\\';
        $names = array_diff(scandir($path), ['..', '.']);
        foreach($names as $module) {
            if (substr($module, 0, 1) == '.')
                continue;

            // this is to remove the ".php" part of the filename
            $module = substr($module, 0, strrpos($module, '.'));
            $namespace = $base_namespace . $module;
            $this->app->register($namespace);
        }
    }

    public function register()
    {
        //
    }
}
