<?php

namespace Vtlabs\Ecommerce\Database\Seeds;

use Carbon\Carbon as Carbon;
use Illuminate\Database\Seeder;

use Vtlabs\Core\Models\User\User;
use Database\traits\TruncateTable;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Database\traits\DisableForeignKeys;;

class EcommerceSeeder extends Seeder
{
    use DisableForeignKeys, TruncateTable;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        // create role and permission
        Role::create(['name' => 'vendor']);
    }
}
