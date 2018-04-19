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

            $filename = null;
            foreach($data->mail->headers as $header) {
                if ($header->name == 'X-Tiret-Filename') {
                    $filename = $header->value;
                    break;
                }
            }

            if ($filename == null) {
                Log::error('Arrivata notifica per mail non identificata: ' . print_r($message, true));
                return;
            }

            if ($data->notificationType == 'Delivery') {
                Mlog::updateStatus($filename, 'sent');
            }
            else {
                if (isset($data->bounce)) {
                    $address = $data->bounce->bouncedRecipients[0]->emailAddress;
                    $message = $data->bounce->bouncedRecipients[0]->diagnosticCode;

                    if (strstr($message, 'too busy') !== false)
                        Mlog::updateStatus($filename, 'reschedule');
                    else
                        Mlog::updateStatus($filename, 'fail');
                }
                else {
                    Log::error('Unrecognized notification from SNS: ' . print_r($data, true));
                }
            }
        }
    }
}
