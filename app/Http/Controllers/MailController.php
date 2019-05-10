<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use App\Mlog;
use Log;

class MailController extends Controller
{
    public function postStatus(Request $request)
    {
        $message = Message::fromRawPostData();
        $validator = new MessageValidator();

        try {
            $validator->validate($message);
        }
        catch (InvalidSnsMessageException $e) {
            Log::error('SNS Message Validation Error: ' . $e->getMessage());
            abort(404);
        }

        if ($message['Type'] === 'SubscriptionConfirmation') {
            file_get_contents($message['SubscribeURL']);
        }
        else if ($message['Type'] === 'Notification') {
            $data = json_decode($message['Message']);

            try {
                $filename = null;
                foreach($data->mail->headers as $header) {
                    if ($header->name == 'X-Tiret-Filename') {
                        $filename = $header->value;
                        break;
                    }
                }
            }
            catch(\Exception $e) {
                Log::error('Notifica SNS con headers non riconosciuti: ' . print_r($data, true));
                return;
            }

            if ($filename == null) {
                Log::error('Arrivata notifica per mail non identificata: ' . print_r($message, true));
                return;
            }

            $filename = explode(',', $filename);

            if ($data->notificationType == 'Delivery') {
                Mlog::updateStatus($filename, 'sent');
            }
            else if ($data->notificationType == 'Bounce') {
                try {
                    $message = $data->bounce->bouncedRecipients[0]->diagnosticCode;

                    if (strstr($message, 'too busy') !== false)
                        Mlog::updateStatus($filename, 'reschedule');
                    else
                        Mlog::updateStatus($filename, 'fail');
                }
                catch(\Exception $e) {
                    Log::error('Notifica SNS illeggibile: ' . print_r($data, true));
                    Mlog::updateStatus($filename, 'fail');
                }
            }
            else {
                Log::error('Notifica SNS tipo non riconosciuto: ' . print_r($data, true));
                Mlog::updateStatus($filename, 'fail');
            }
        }
    }
}
