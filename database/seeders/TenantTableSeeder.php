<?php
namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;
use Stancl\Tenancy\Jobs\DeleteDatabase;

class TenantTableSeeder extends Seeder
{
    use DisableForeignKeys;

    protected $tenants = [
        [ 'id' => 1, 'name' => 'first'],
        [ 'id' => 2, 'name' => 'second'],
    ];

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeys();

        $this->truncateDatabase();
        foreach($this->tenants as $tenant_info) {
            $this->deleteDatabase($tenant_info);
            $this->makeTenant($tenant_info);
        }

        $this->enableForeignKeys();
    }

    public function truncateDatabase() {
        resolve(config('tenancy.domain_model'))->query()->truncate();
        resolve(config('tenancy.tenant_model'))->query()->truncate();
    }

    public function deleteDatabase($tenant_info) {
        $user = new User([
            'name' => ucfirst($tenant_info['name']) . ' user',
            'email' => $tenant_info['name'] . '@demo.com',
        ]);
        $tenant = new Tenant([
            'id' => $tenant_info['id'],
            'name' => $tenant_info['name'],
            'description' => ucfirst($tenant_info['name']),
            'user_id' => $user->id,
        ]);

        $manager = $tenant->database()->manager();
        if ($manager->databaseExists($tenant->database()->getName())) {
            $job = new DeleteDatabase($tenant);
            $job->handle();
        }
    }

    public function makeTenant($tenant_info) {
        $user = User::create([
            'name' => ucfirst($tenant_info['name']) . ' user',
            'email' => $tenant_info['name'] . '@demo.com',
            'password' => bcrypt(123456)
        ]);
        $tenant = Tenant::create([
            'id' => $tenant_info['id'],
            'name' => $tenant_info['name'],
            'description' => ucfirst($tenant_info['name']),
            'user_id' => $user->id,
        ]);
        $tenant->domains()->saveMany($this->getDomains($tenant_info['name']));
    }

    public function getDomains($tenant_name): array {
        $domains = [];
        foreach ($this->centralDomains() as $domain) {
            $domains[] = resolve(config('tenancy.domain_model'))
                        ->fill([
                            'domain' => $tenant_name.'.'.$domain
                        ]);
        }
        return $domains;
    }

    protected function centralDomains(): array
    {
        return config('tenancy.central_domains');
    }
}
