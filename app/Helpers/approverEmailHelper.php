<?php

namespace App\Helpers;

use App\Mail\QiwaEosNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


class ApproverEmailHelper
{


    public function sendApproverNotify($email_ditals)
    {

        try {

            $view_name = 'emails.approver_email_notification';
            $subject = 'e-Qiwa-EOS: New Qiwa EOS Request Alert';

            // Split the name by spaces
            $nameParts = explode(' ', $email_ditals['next_approver_name']);

            // Get the first name
            $email_ditals['next_approver_name'] = $nameParts[0];

            $email_address = 'oalkhateeb1710@gmail.com';
            // $email_address = $email_ditals['mail_to'];

            $data = [
                'header' => "Ajmi Company",
                'title' => "New Qiwa EOS Request Alert",
                'message' => $email_ditals['message'] ?? null,
                'qiwa_eos_request_id' => $email_ditals['qiwa_eos_request_id'] ?? null,
                'next_approver_number' => $email_ditals['next_approver_number'] ?? null,
                'next_approver_name' => $email_ditals['next_approver_name'] ?? null,
                'employee_number' => $email_ditals['employee_number'] ?? null,
                'employee_name' => $email_ditals['employee_name'] ?? null,
                'cost_center' => $email_ditals['cost_center'] ?? null,
                'notes' => $email_ditals['notes'] ?? null,
                'job' => $email_ditals['job'] ?? null,
                'nationality' => $email_ditals['nationality'] ?? null,
                'iqama_number' => $email_ditals['iqama_number'] ?? null,
                'e_qiwa_number' => $email_ditals['e_qiwa_number'] ?? null,
                'qiwa_request_date' => $email_ditals['qiwa_request_date'] ?? null,
                'type_name' => $email_ditals['type_name'] ?? null,
                'login_user' => $email_ditals['login_user'] ?? null,
            ];

            if (!empty($email_address)) {
                // $emailSent = Mail::to($email_address)->send(new QiwaEosNotificationMail($data, $view_name, $subject));
                // return $emailSent;
            } else {
                Log::channel('sendnotification')->debug("error->");
                return false;
            }

        } catch (\Exception $exception) {
            Log::channel('sendnotification')->debug($exception);
            throw $exception;
        }

    }

    public function sendOwnerNotify($email_ditals)
    {

        $view_name = 'emails.approver_email_notification';
        $subject = 'e-Qiwa-EOS: New Qiwa EOS Request Alert';

        // Split the name by spaces
        $nameParts = explode(' ', $email_ditals['next_approver_name']);

        // Get the first name
        $email_ditals['next_approver_name'] = $nameParts[0];

        $email_address = 'oalkhateeb1710@gmail.com';
        // $email_address = $email_ditals['mail_to'];

        $data = [
            'header' => "Ajmi Company",
            'title' => "New Qiwa EOS Request Alert",
            'message' => $email_ditals['message'] ?? null,
            'qiwa_eos_request_id' => $email_ditals['qiwa_eos_request_id'] ?? null,
            'next_approver_number' => $email_ditals['next_approver_number'] ?? null,
            'next_approver_name' => $email_ditals['next_approver_name'] ?? null,
            'created_by' => $email_ditals['created_by'] ?? null,
            'employee_number' => $email_ditals['employee_number'] ?? null,
            'employee_name' => $email_ditals['employee_name'] ?? null,
            'cost_center' => $email_ditals['cost_center'] ?? null,
            'notes' => $email_ditals['notes'] ?? null,
            'job' => $email_ditals['job'] ?? null,
            'nationality' => $email_ditals['nationality'] ?? null,
            'iqama_number' => $email_ditals['iqama_number'] ?? null,
            'e_qiwa_number' => $email_ditals['e_qiwa_number'] ?? null,
            'qiwa_request_date' => $email_ditals['qiwa_request_date'] ?? null,
            'type_name' => $email_ditals['type_name'] ?? null,
            'login_user' => $email_ditals['login_user'] ?? null,
        ];

        if (!empty($email_address)) {
            // $emailSent = Mail::to($email_address)->send(new QiwaEosNotificationMail($data, $view_name, $subject));
            // return $emailSent;
        } else {
            Log::info('Email not sent: email_to is empty.', $email_ditals);
            return false;
        }
    }


}
