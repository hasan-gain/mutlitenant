<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getIncrementing(): bool
    {
        return true;
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'user_id',
            'description',
        ];
    }
}
