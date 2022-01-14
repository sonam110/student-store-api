<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;

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
    }
}
