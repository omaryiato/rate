<?php

namespace App\Helpers;

use App\Mail\ContactMessageMail;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ContactMessageHelper
{


    public function ContactMessageNotification(int $message_id)
    {

        try {

            $message_details = ContactMessage::findOrFail($message_id);

            $view_name = 'emails.contact_message';
            $subject = 'Contact Messages: New Message';

            // $email_address = 'oalkhateeb1710@gmail.com';
            $email_address = 'info@beveconsult.com';
            // $email_address = 'o.bsharat@beveconsult.com';
            // $email_address = $email_ditals['mail_to'];

            $data = [
                'header' => "IBC Group",
                'title' => "New Message",
            ];

            if (!empty($email_address)) {
                $emailSent = Mail::to($email_address)->send(new ContactMessageMail($message_details, $view_name, $subject));
                return $emailSent;
            } else {
                // Log::channel('sendnotification')->debug("error->");
                return false;
            }

        } catch (\Exception $exception) {
            // Log::channel('sendnotification')->debug($exception);
            throw $exception;
        }

    }


}
