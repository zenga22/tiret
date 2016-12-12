<?php

namespace App\Plugins;

use Event;

use Illuminate\Support\ServiceProvider;

/*
    Plugin di esempio per Tiret.

    La logica di fondo è:
    - al bootstrap dell'applicazione, tutti i ServiceProviders presenti nella
    cartella app/Plugins vengono registrati
    - sta al singolo plugin fornire un parametro o una opzione per abilitarlo o
    disabilitarlo all'occorrenza
    - i plugins si auto-registrano agli eventi che vogliono monitorare, con le
    relative callbacks
*/

class SampleFileHandler extends ServiceProvider
{
    private $enabled = false;

    public function boot()
    {
        if ($this->enabled) {
            Event::subscribe($this);
        }
    }

    public function register()
    {
    }

    public function subscribe($events)
    {
        $events->listen('App\Events\FileToHandle', function ($event) {
            echo $event->getFilename() . "\n";
        });
    }
}
