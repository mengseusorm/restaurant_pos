<?php

namespace Database\Seeders;

use App\Enums\Role as EnumRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class RolePermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::find(EnumRole::ADMIN);
        $adminRole?->givePermissionTo(Permission::all());

        $branchManager = Role::find(5);
        if ($branchManager) {
            $branchManagerPermissions = [
                'dashboard',
                'dining-tables',
                'dining_tables_create',
                'dining_tables_edit',
                'dining_tables_delete',
                'dining_tables_show',
                'pos',
                'pos-orders',
                'online-orders',
                'table-orders',
                'push-notifications',
                'push-notifications_create',
                'push-notifications_edit',
                'push-notifications_delete',
                'push-notifications_show',
                'messages',
                'delivery-boys',
                'delivery-boys_create',
                'delivery-boys_edit',
                'delivery-boys_delete',
                'delivery-boys_show',
                'customers',
                'customers_create',
                'customers_edit',
                'customers_delete',
                'customers_show',
                'employees',
                'employees_create',
                'employees_edit',
                'employees_delete',
                'employees_show',
                'transactions',
                'sales-report',
                'sales-summary-report',
                'sales-summary-report-staff',
                'branch-sales-summary-report',
                'items-report',
                'items-category-report',
                'daily-sale-report',
                'payment-method-report',
                'order-type-report',
                // 'order-source-report',
                'branch-sale-report',
                'branch-trend-report',
                'branch-daily-sale-report',
                'user-sales-report',
                'service-report',
                'stocks',
                'stock-records',
                'stocks-report',
            ];
            $branchManagerPermissions = Permission::whereIn('name', $branchManagerPermissions)->get();
            $branchManager->givePermissionTo($branchManagerPermissions);
        }

        $posOperatorManager = Role::find(6);
        if ($posOperatorManager) {
            $posOperatorManagerPermissions = [
                'dashboard',
                'pos',
                'pos-orders',
                'dining-tables',
                'dining_tables_show',
                'dining_tables_edit'
            ];
            $posOperatorManagerPermissions = Permission::whereIn('name', $posOperatorManagerPermissions)->get();
            $posOperatorManager->givePermissionTo($posOperatorManagerPermissions);
        }
    }
}