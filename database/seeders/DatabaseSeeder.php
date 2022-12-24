<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Setting;
use App\Models\User;
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
//        Setting::create(['key'=> 'app_name', 'value' => tenant()->id]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::create([
            'name' => 'Landlord admin',
            'email' => 'admin@demo.com',
            'password' => bcrypt(123456)
        ]);
        $this->call(TenantTableSeeder::class);
//        $tenant = $user->tenant()->create(['id' => 'first']);
//        $tenant->domains()->create(['domain' => 'first.localhost']);
    }
}
