<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        //$this->call(CountrySeeder::class);
        //$this->call(StateSeeder::class);
        //$this->call(CitySeeder::class);

        \DB::unprepared(file_get_contents(storage_path('backups/countries.sql')));
        \DB::unprepared(file_get_contents(storage_path('backups/states.sql')));
        \DB::unprepared(file_get_contents(storage_path('backups/cities.sql')));
        
        $this->call(AdminUserSeeder::class);
        $this->call(LabelSeeder::class);
        $this->call(PackageSeeder::class);
        $this->call(BucketSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(LangForDDLSeerder::class);
        $this->call(NotificationTemplateSeeder::class);
        $this->call(PageContentSeeder::class);

        
    }
}
