<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use App\Models\EmailTemplate;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['template_for'=>'abuse_reported', 'title'=>'Abuse Reported', 'body'=>'An Abuse has been reported for the {{product_type}} {{product_title}}.', 'attributes'=>["{{product_type}}","{{product_title}}"]],
            ['template_for'=>'new_message', 'title'=>'New Message', 'body'=>'{{message}}', 'attributes'=>["{{message}}"]],
            ['template_for'=>'new_contest_posted', 'title'=>'New Contest Posted', 'body'=>'New Contest {{contest_title}} Posted.', 'attributes'=>["{{contest_title}}"]],
        ];

        foreach($data as $rec)
        {
            $template = new NotificationTemplate;
            $template->language_id  = 1;
            $template->template_for = $rec['template_for'];
            $template->title        = $rec['title'];
            $template->body         = $rec['body'];
            $template->attributes   = json_encode($rec['attributes'], JSON_UNESCAPED_UNICODE);
            $template->save();
        }

        //email template
        $data = '[
          {
            "auto_id": "7",
            "id": "004f469e-d2b9-44ff-bfd9-2fff268b6603",
            "language_id": "1",
            "template_for": "order_replacement_request",
            "from": null,
            "subject": "Order Replacement Request",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has a replacement request because of {{replacement_reason}}.we will notify you once it will be accepted",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}},{{replacement_reason}}",
            "status": null,
            "created_at": "2021-09-30 06:31:31",
            "updated_at": "2021-10-09 10:45:09"
          },
          {
            "auto_id": "8",
            "id": "04579cff-587f-4518-a147-b9ea960f6597",
            "language_id": "1",
            "template_for": "order_replaced",
            "from": null,
            "subject": "Order Replaced",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been a replaced.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": "2021-09-30 06:43:57",
            "updated_at": "2021-09-30 06:43:57"
          },
          {
            "auto_id": "2",
            "id": "12169124-9fbf-4847-ab17-c7e7b9f28ddf",
            "language_id": "1",
            "template_for": "forgot_password",
            "from": null,
            "subject": "Forgot Password Otp",
            "body": "Dear {{user_name}}, {{otp}} is your otp for number verification.",
            "attachment_path": null,
            "attributes": "{{otp}},{{user_name}}",
            "status": "1",
            "created_at": "2021-09-16 00:46:48",
            "updated_at": "2021-09-16 00:46:48"
          },
          {
            "auto_id": "3",
            "id": "2528a137-07b8-4423-be1a-a672e8a3f987",
            "language_id": "1",
            "template_for": "order_placed",
            "from": null,
            "subject": "Order Placed",
            "body": "Dear {{user_name}}, Your Order has been placed successfully with order number {{order_number}}.",
            "attachment_path": null,
            "attributes": "{{order_number}},{{user_name}}",
            "status": "1",
            "created_at": "2021-09-16 00:48:30",
            "updated_at": "2021-09-16 00:48:30"
          },
          {
            "auto_id": "10",
            "id": "2e3088a4-e28e-4e08-9f82-8676cd96419f",
            "language_id": "1",
            "template_for": "order_shipped",
            "from": null,
            "subject": "Order Shipped",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been shipped.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": "2021-09-30 07:01:48",
            "updated_at": "2021-09-30 07:01:48"
          },
          {
            "auto_id": "6",
            "id": "496e244c-ee8b-48d3-b904-5a5123e22d73",
            "language_id": "1",
            "template_for": "package_upgrade",
            "from": null,
            "subject": "Package Upgraded",
            "body": "Dear {{user_name}}, Your Package for module {{module}} has been upgraded to {{package_type}} which is valid till {{valid_till}} .",
            "attachment_path": null,
            "attributes": "{{module}},{{package_type}},{{valid_till}}",
            "status": "1",
            "created_at": "2021-09-17 06:05:26",
            "updated_at": "2021-09-17 06:05:26"
          },
          {
            "auto_id": "19",
            "id": "4e0a7377-58d3-4d6a-b510-145ee6187bde",
            "language_id": "1",
            "template_for": "order_completed",
            "from": null,
            "subject": "Sail",
            "body": "gfdtgdghede",
            "attachment_path": null,
            "attributes": null,
            "status": null,
            "created_at": "2021-12-30 11:21:28",
            "updated_at": "2021-12-30 11:21:28"
          },
          {
            "auto_id": "12",
            "id": "5a927c90-7831-4487-aebb-7ce5eb35c4fa",
            "language_id": "1",
            "template_for": "order_completed",
            "from": null,
            "subject": "Order Completed",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been completed.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": "2021-09-30 07:02:36",
            "updated_at": "2021-09-30 07:02:36"
          },
          {
            "auto_id": "11",
            "id": "7b1efe30-be66-46bb-9fd4-8faee087202e",
            "language_id": "1",
            "template_for": "order_delivered",
            "from": null,
            "subject": "Order Delivered",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been delivered.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": "2021-09-30 07:02:09",
            "updated_at": "2021-09-30 07:02:09"
          },
          {
            "auto_id": "14",
            "id": "942071d1-2505-11ec-933a-0ab49365b9d3",
            "language_id": "1",
            "template_for": "order_returned",
            "from": null,
            "subject": "Order Returned",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been returned.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": null,
            "updated_at": null
          },
          {
            "auto_id": "13",
            "id": "980eb296-58af-4eac-b263-151551dc275b",
            "language_id": "1",
            "template_for": "order_canceled",
            "from": null,
            "subject": "Order Canceled",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been canceled.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}}",
            "status": "1",
            "created_at": "2021-09-30 07:03:48",
            "updated_at": "2021-09-30 07:03:48"
          },
          {
            "auto_id": "1",
            "id": "aaf1d9c6-02a3-477d-b150-92c74fce853f",
            "language_id": "1",
            "template_for": "registration",
            "from": null,
            "subject": "Registered Successfully",
            "body": "Dear {{user_name}}, you have been registered Successfully.verification link - {{verification_link}}",
            "attachment_path": null,
            "attributes": "{{user_name}},{{verification_link}}",
            "status": "1",
            "created_at": "2021-09-16 00:45:28",
            "updated_at": "2021-09-16 00:45:28"
          },
          {
            "auto_id": "1",
            "id": "aaf1d9c6-02a3-477d-b150-92c74fce8544",
            "language_id": "1",
            "template_for": "contact-us",
            "from": null,
            "subject": "Contact Us",
            "body": "Dear {{user_name}}, Thanks for contacting us, we will contact you ASAP.",
            "attachment_path": null,
            "attributes": "{{user_name}}",
            "status": "1",
            "created_at": "2021-09-16 00:45:28",
            "updated_at": "2021-09-16 00:45:28"
          },
          {
            "auto_id": "4",
            "id": "df0d38c9-1084-4c2d-9e25-bc81f58fde2c",
            "language_id": "1",
            "template_for": "order_confirmed",
            "from": null,
            "subject": "Order Confirmed",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has been confirmed.",
            "attachment_path": null,
            "attributes": "{{order_item}},{{user_name}}",
            "status": "1",
            "created_at": "2021-09-16 02:13:22",
            "updated_at": "2021-09-16 02:13:22"
          },
          {
            "auto_id": "9",
            "id": "f047906c-3c7b-4d4c-8b4c-4c6ab8a4500a",
            "language_id": "1",
            "template_for": "order_return_request",
            "from": null,
            "subject": "Order Return Request",
            "body": "Dear {{user_name}}, Your Order for {{order_item}} has a return request because of {{return_reason}}.we will notify you once it will be accepted.",
            "attachment_path": null,
            "attributes": "{{user_name}},{{order_item}},{{return_reason}}",
            "status": "1",
            "created_at": "2021-09-30 06:50:24",
            "updated_at": "2021-09-30 06:50:24"
          }
        ]';

        foreach(json_decode($data, true) as $rec)
        {
            $template = new EmailTemplate;
            $template->language_id  = 1;
            $template->template_for = $rec['template_for'];
            $template->subject      = $rec['subject'];
            $template->body         = $rec['body'];
            $template->attributes   = json_encode($rec['attributes'], JSON_UNESCAPED_UNICODE);
            $template->status       = $rec['status'];
            $template->save();
        }
    }
}
