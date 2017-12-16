<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Aws\Sns\Exception\InvalidSnsMessageException;

use App\Mlog;

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
            $message_id = $data->mail->messageId;

            if ($data->notificationType == 'Delivery') {
                Mlog::updateStatus($message_id, 'sent');
            }
            else {
                if (isset($data->bounce)) {
                    $address = $data->bounce->bouncedRecipients[0]->emailAddress;
                    $message = $data->bounce->bouncedRecipients[0]->diagnosticCode;

                    if (strstr($message, 'too busy') !== false)
                        Mlog::updateStatus($message_id, 'reschedule');
                    else
                        Mlog::updateStatus($message_id, 'fail');
                }
                else {
                    Log::error('Unrecognized notification from SNS: ' . print_r($data, true));
                }
            }
        }
    }
}
