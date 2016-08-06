<?php

namespace App\Providers;

use Illuminate\Mail\MailServiceProvider;
use Illuminate\Mail\Transport\Transport;
use Swift_Mailer;
use Swift_Mime_Message;
use GuzzleHttp\Client as HttpClient;

class EmergencyMailServiceProvider extends MailServiceProvider
{
    public function registerSwiftMailer()
    {
        if ($this->app['config']->get('mail.driver') == 'emergency') {
            $this->registerEmergencyMailer();
        } else {
            parent::registerSwiftMailer();
        }
    }

    protected function registerEmergencyMailer()
    {
        $conf = $this->app['config']->get('services.emergency_mail', []);
        $this->app['swift.mailer'] = $this->app->share(function ($app) use ($conf) {
            return new Swift_Mailer(
                new Swift_EmergencyTransport($conf['url'])
            );
        });
    }
}

class Swift_EmergencyTransport implements \Swift_Transport
{
    private $client;
    private $url;

    public function __construct($url)
    {
        $this->client = new HttpClient();
        $this->url = $url;
    }

    public function isStarted()
    {
        return true;
    }

    public function start()
    {
        return true;
    }

    public function stop()
    {
        return true;
    }

    public function send(Swift_Mime_Message $message, &$failedRecipients = null)
    {
        $to = array_keys($message->getTo());
        $to = $to[0];

        $data = [
            'subject' => $message->getSubject(),
            'to' => $to,
            'message' => (string) $message
        ];
        $options = ['form_params' => $data];

        return $this->client->post($this->url, $options);
    }

    public function registerPlugin(\Swift_Events_EventListener $plugin)
    {
        /* dummy */
    }
}
