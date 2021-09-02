<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LangForDDL;

class LangForDDLSeerder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = ['Hindi', 'English', 'Swedish'];
        foreach ($languages as $key => $language) {
        	$lang = new LangForDDL;
        	$lang->name = $language;
        	$lang->save();
        }
    }
}
