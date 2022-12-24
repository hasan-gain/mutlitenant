<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class AfterTenantCreatedDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Setting::create(['key'=> 'app_name', 'value' => tenant()->id]);
        $user = User::create([
            'name' => ucfirst(tenant()->name) . ' user',
            'email' => tenant()->name . '@demo.com',
            'password' => bcrypt(123456)
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
