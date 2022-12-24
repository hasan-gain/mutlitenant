<?php

declare(strict_types=1);

namespace App\Jobs\Tenancy;

use App\Jobs\Tenancy\Helper\CpanelManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;

class CreateDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase|Model */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(DatabaseManager $databaseManager)
    {
        event(new CreatingDatabase($this->tenant));

        // Terminate execution of this job & other jobs in the pipeline
        if ($this->tenant->getInternal('create_database') === false) {
            return false;
        }

        $this->tenant->database()->makeCredentials();
        $databaseManager->ensureTenantCanBeCreated($this->tenant);
        try {
            $this->tenant->database()->manager()->createDatabase($this->tenant);
        } catch (\Exception $e) {
            $this->createDatabase($this->tenant);
        }

        event(new DatabaseCreated($this->tenant));
    }

    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database()->getName();

        $domainName = env('SERVER_DOMAIN_NAME');
        $cpanelUsername = env('SERVER_USER_NAME');
        $cpanelPassword = env('SERVER_PASSWORD');
        // Database information
        $databaseUserName = env('DB_USERNAME');
        $databasePassword = env('DB_PASSWORD');

        $cpanel = new CpanelManager($domainName, $cpanelUsername, $cpanelPassword);

        $cpanel->createDataBaseMySQL($database);

        $cpanel->createUserMySQL($databaseUserName, $databasePassword);

        $cpanel->setPrivilegesMySQL($databaseUserName, $database);
        return true;
    }
}
