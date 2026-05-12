<?php

namespace Database\Seeders;

use App\Libraries\AppLibrary;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            [
                'title'      => 'Dashboard',
                'name'       => 'dashboard',
                'guard_name' => 'sanctum',
                'url'        => 'dashboard',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Items',
                'name'       => 'items',
                'guard_name' => 'sanctum',
                'url'        => 'items',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Items Create',
                        'name'       => 'items_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'items/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Items Edit',
                        'name'       => 'items_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'items/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Items Delete',
                        'name'       => 'items_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'items/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Items Show',
                        'name'       => 'items_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'items/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Dining Tables',
                'name'       => 'dining-tables',
                'guard_name' => 'sanctum',
                'url'        => 'dining-tables',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Dining Tables Create',
                        'name'       => 'dining_tables_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'dining-table/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Dining Tables Edit',
                        'name'       => 'dining_tables_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'dining-table/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Dining Tables Delete',
                        'name'       => 'dining_tables_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'dining-tables/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Dining Tables Show',
                        'name'       => 'dining_tables_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'dining-tables/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'POS',
                'name'       => 'pos',
                'guard_name' => 'sanctum',
                'url'        => 'pos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Orders Pending List',
                'name'       => 'orders-pending',
                'guard_name' => 'sanctum',
                'url'        => 'pending-orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'POS Orders',
                'name'       => 'pos-orders',
                'guard_name' => 'sanctum',
                'url'        => 'pos-orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Orders Deleted',
                'name'       => 'order-deleted',
                'guard_name' => 'sanctum',
                'url'        => 'order-deleted',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'POS Orders Unpaid',
                'name'       => 'pos-orders-unpaid',
                'guard_name' => 'sanctum',
                'url'        => 'pos-orders-unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Table Orders',
                'name'       => 'table-orders',
                'guard_name' => 'sanctum',
                'url'        => 'table-orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Online Orders',
                'name'       => 'online-orders',
                'guard_name' => 'sanctum',
                'url'        => 'online-orders',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Telegram Order',
                'name'       => 'telegram-mini-app-orders',
                'guard_name' => 'sanctum',
                'url'        => 'telegram-mini-app-orders',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'telegram Mini App',
                'name'       => 'telegram-mini-app',
                'guard_name' => 'sanctum',
                'url'        => 'telegram-mini-app',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Offers',
                'name'       => 'offers',
                'guard_name' => 'sanctum',
                'url'        => 'offers',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Offers Create',
                        'name'       => 'offers_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'offers/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Offers Edit',
                        'name'       => 'offers_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'offers/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Offers Delete',
                        'name'       => 'offers_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'offers/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Offers Show',
                        'name'       => 'offers_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'offers/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Administrators',
                'name'       => 'administrators',
                'guard_name' => 'sanctum',
                'url'        => 'administrators',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Administrators Create',
                        'name'       => 'administrators_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'administrators/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Administrators Edit',
                        'name'       => 'administrators_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'administrators/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Administrators Delete',
                        'name'       => 'administrators_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'administrators/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Administrators Show',
                        'name'       => 'administrators_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'administrators/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Delivery Boys',
                'name'       => 'delivery-boys',
                'guard_name' => 'sanctum',
                'url'        => 'delivery-boys',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Delivery Boys Create',
                        'name'       => 'delivery-boys_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'delivery-boys/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Delivery Boys Edit',
                        'name'       => 'delivery-boys_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'delivery-boys/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Delivery Boys Delete',
                        'name'       => 'delivery-boys_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'delivery-boys/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Delivery Boys Show',
                        'name'       => 'delivery-boys_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'delivery-boys/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Customers',
                'name'       => 'customers',
                'guard_name' => 'sanctum',
                'url'        => 'customers',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Customers Create',
                        'name'       => 'customers_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'customers/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customers Edit',
                        'name'       => 'customers_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'customers/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customers Delete',
                        'name'       => 'customers_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'customers/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customers Show',
                        'name'       => 'customers_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'customers/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Members',
                'name'       => 'members',
                'guard_name' => 'sanctum',
                'url'        => 'members',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Members Create',
                        'name'       => 'members_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'members/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Members Edit',
                        'name'       => 'members_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'members/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Members Delete',
                        'name'       => 'members_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'members/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Members Show',
                        'name'       => 'members_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'members/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Employees',
                'name'       => 'employees',
                'guard_name' => 'sanctum',
                'url'        => 'employees',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Employees Create',
                        'name'       => 'employees_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'employees/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Employees Edit',
                        'name'       => 'employees_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'employees/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Employees Delete',
                        'name'       => 'employees_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'employees/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Employees Show',
                        'name'       => 'employees_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'employees/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Transactions',
                'name'       => 'transactions',
                'guard_name' => 'sanctum',
                'url'        => 'transactions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'PayWay Transactions',
                'name'       => 'payway-transactions',
                'guard_name' => 'sanctum',
                'url'        => 'payway-transactions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Sales Report',
                'name'       => 'sales-report',
                'guard_name' => 'sanctum',
                'url'        => 'sales-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Sales Summary Report',
                'name'       => 'sales-summary-report',
                'guard_name' => 'sanctum',
                'url'        => 'sales-summary-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'S.S.R.S',
                'name'       => 'sales-summary-report-staff',
                'guard_name' => 'sanctum',
                'url'        => 'sales-summary-report-staff',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Branch Sales Summary Report',
                'name'       => 'branch-sales-summary-report',
                'guard_name' => 'sanctum',
                'url'        => 'branch-sales-summary-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Items Report',
                'name'       => 'items-report',
                'guard_name' => 'sanctum',
                'url'        => 'items-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Items Detail Report',
                'name'       => 'items-detail-report',
                'guard_name' => 'sanctum',
                'url'        => 'items-detail-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Items Category Report',
                'name'       => 'items-category-report',
                'guard_name' => 'sanctum',
                'url'        => 'items-category-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Credit Balance Report',
                'name'       => 'credit-balance-report',
                'guard_name' => 'sanctum',
                'url'        => 'credit-balance-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Daily Sale Report',
                'name'       => 'daily-sale-report',
                'guard_name' => 'sanctum',
                'url'        => 'daily-sale-report',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Daily Sale Summary Report',
                'name'       => 'daily-sale-summary-report',
                'guard_name' => 'sanctum',
                'url'        => 'daily-sale-summary-report',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Payment Method Report',
                'name'       => 'payment-method-report',
                'guard_name' => 'sanctum',
                'url'        => 'payment-method-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Order Type Report',
                'name'       => 'order-type-report',
                'guard_name' => 'sanctum',
                'url'        => 'order-type-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Order Source Report',
                'name'       => 'order-source-report',
                'guard_name' => 'sanctum',
                'url'        => 'order-source-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Branch Sale Report',
                'name'       => 'branch-sale-report',
                'guard_name' => 'sanctum',
                'url'        => 'branch-sale-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Branch Trend Report',
                'name'       => 'branch-trend-report',
                'guard_name' => 'sanctum',
                'url'        => 'branch-trend-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Branch Daily Sale Report',
                'name'       => 'branch-daily-sale-report',
                'guard_name' => 'sanctum',
                'url'        => 'branch-daily-sale-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'User Sales Report',
                'name'       => 'user-sales-report',
                'guard_name' => 'sanctum',
                'url'        => 'user-sales-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'title'      => 'HQ Dashboard',
                'name'       => 'hq-dashboard',
                'guard_name' => 'sanctum',
                'url'        => 'hq-dashboard',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Settings',
                'name'       => 'settings',
                'guard_name' => 'sanctum',
                'url'        => 'settings',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Order Print Logs',
                'name'       => 'order-print-logs',
                'guard_name' => 'sanctum',
                'url'        => 'order-print-logs',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Stocks',
                'name'       => 'stocks',
                'guard_name' => 'sanctum',
                'url'        => 'stocks', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Stock Records',
                'name'       => 'stock-records',
                'guard_name' => 'sanctum',
                'url'        => 'stock-records', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Stocks Report',
                'name'       => 'stocks-report',
                'guard_name' => 'sanctum',
                'url'        => 'stocks-report', 
                'created_at' => now(),
                'updated_at' => now()
            ],

            [
                'title'      => 'Point Earn Rules',
                'name'       => 'point-earn-rules',
                'guard_name' => 'sanctum',
                'url'        => 'point-earn-rules',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Point Earn Rules Create',
                        'name'       => 'point-earn-rules_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-earn-rules/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Earn Rules Edit',
                        'name'       => 'point-earn-rules_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-earn-rules/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Earn Rules Delete',
                        'name'       => 'point-earn-rules_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-earn-rules/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Earn Rules Show',
                        'name'       => 'point-earn-rules_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-earn-rules/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Point Usage Rules',
                'name'       => 'point-usage-rules',
                'guard_name' => 'sanctum',
                'url'        => 'point-usage-rules',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Point Usage Rules Create',
                        'name'       => 'point-usage-rules_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-usage-rules/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Usage Rules Edit',
                        'name'       => 'point-usage-rules_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-usage-rules/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Usage Rules Delete',
                        'name'       => 'point-usage-rules_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-usage-rules/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Point Usage Rules Show',
                        'name'       => 'point-usage-rules_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'point-usage-rules/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Expense Types',
                'name'       => 'expense-types',
                'guard_name' => 'sanctum',
                'url'        => 'expense-types',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Expense Types Create',
                        'name'       => 'expense-types_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-types/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Types Edit',
                        'name'       => 'expense-types_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-types/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Types Delete',
                        'name'       => 'expense-types_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-types/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Types Show',
                        'name'       => 'expense-types_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-types/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Expense Payment Methods',
                'name'       => 'expense-payment-methods',
                'guard_name' => 'sanctum',
                'url'        => 'expense-payment-methods',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Expense Payment Methods Create',
                        'name'       => 'expense-payment-methods_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-payment-methods/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Payment Methods Edit',
                        'name'       => 'expense-payment-methods_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-payment-methods/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Payment Methods Delete',
                        'name'       => 'expense-payment-methods_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-payment-methods/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expense Payment Methods Show',
                        'name'       => 'expense-payment-methods_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-payment-methods/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Expenses',
                'name'       => 'expenses',
                'guard_name' => 'sanctum',
                'url'        => 'expenses',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Expenses Create',
                        'name'       => 'expenses_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'expenses/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expenses Edit',
                        'name'       => 'expenses_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'expenses/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expenses Delete',
                        'name'       => 'expenses_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'expenses/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Expenses Show',
                        'name'       => 'expenses_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'expenses/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Expense Report',
                'name'       => 'expense-report',
                'guard_name' => 'sanctum',
                'url'        => 'expense-report',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Expense Report Show',
                        'name'       => 'expense-report_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'expense-report/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Shop Expense Report',
                'name'       => 'shop-expense-report',
                'guard_name' => 'sanctum',
                'url'        => 'shop-expense-report',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Shop Expense Report Show',
                        'name'       => 'shop-expense-report_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'shop-expense-report/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],

            [
                'title'      => 'Activity Logs',
                'name'       => 'activity_log',
                'guard_name' => 'sanctum',
                'url'        => 'activity-logs',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Activity Logs View',
                        'name'       => 'activity_log_view',
                        'guard_name' => 'sanctum',
                        'url'        => 'activity-logs/view',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Activity Logs Delete',
                        'name'       => 'activity_log_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'activity-logs/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],


            [
                'title'      => 'printer',
                'name'       => 'printer',
                'guard_name' => 'sanctum',
                'url'        => 'printer', 
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title'      => 'Floor Plan',
                'name'       => 'floor_plan',
                'guard_name' => 'sanctum',
                'url'        => 'floor-plan',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Floor Plan Groups Create',
                        'name'       => 'floor_plan_groups_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/groups/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Groups Edit',
                        'name'       => 'floor_plan_groups_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/groups/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Groups Delete',
                        'name'       => 'floor_plan_groups_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/groups/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Table Position Update',
                        'name'       => 'floor_plan_table_position_update',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/table/position',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Table Properties Update',
                        'name'       => 'floor_plan_table_properties_update',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/table/properties',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Table Guests Update',
                        'name'       => 'floor_plan_table_guests_update',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/table/guests',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Table Release',
                        'name'       => 'floor_plan_table_release',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/table/release',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Floor Plan Analytics',
                        'name'       => 'floor_plan_analytics',
                        'guard_name' => 'sanctum',
                        'url'        => 'floor-plan/analytics',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Reservations',
                'name'       => 'reservations',
                'guard_name' => 'sanctum',
                'url'        => 'reservation',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Reservations Create',
                        'name'       => 'reservations_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'reservation/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Reservations Edit',
                        'name'       => 'reservations_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'reservation/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Reservations Delete',
                        'name'       => 'reservations_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'reservation/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Reservations Show',
                        'name'       => 'reservations_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'reservation/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Lost & Found',
                'name'       => 'lost_and_found',
                'guard_name' => 'sanctum',
                'url'        => 'lost-and-found',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Lost & Found Create',
                        'name'       => 'lost_and_found_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'lost-and-found/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Lost & Found Edit',
                        'name'       => 'lost_and_found_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'lost-and-found/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Lost & Found Delete',
                        'name'       => 'lost_and_found_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'lost-and-found/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Lost & Found Show',
                        'name'       => 'lost_and_found_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'lost-and-found/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Customer Beverage Storage',
                'name'       => 'customer_beverage_storage',
                'guard_name' => 'sanctum',
                'url'        => 'customer-beverage-storage',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Customer Beverage Storage Create',
                        'name'       => 'customer_beverage_storage_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'customer-beverage-storage/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customer Beverage Storage Edit',
                        'name'       => 'customer_beverage_storage_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'customer-beverage-storage/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customer Beverage Storage Delete',
                        'name'       => 'customer_beverage_storage_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'customer-beverage-storage/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Customer Beverage Storage Show',
                        'name'       => 'customer_beverage_storage_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'customer-beverage-storage/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]
            ],
            [
                'title'      => 'Make Payment',
                'name'       => 'make_payment',
                'guard_name' => 'sanctum',
                'url'        => 'make-payment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Discount',
                'name'       => 'discount',
                'guard_name' => 'sanctum',
                'url'        => 'discount',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Delete Order',
                'name'       => 'delete_order',
                'guard_name' => 'sanctum',
                'url'        => 'delete-order',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title'      => 'Rooms',
                'name'       => 'rooms',
                'guard_name' => 'sanctum',
                'url'        => 'rooms',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Rooms Create',
                        'name'       => 'rooms_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'rooms/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Rooms Edit',
                        'name'       => 'rooms_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'rooms/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Rooms Delete',
                        'name'       => 'rooms_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'rooms/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Rooms Show',
                        'name'       => 'rooms_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'rooms/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Beds',
                'name'       => 'beds',
                'guard_name' => 'sanctum',
                'url'        => 'beds',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Beds Create',
                        'name'       => 'beds_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'beds/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Beds Edit',
                        'name'       => 'beds_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'beds/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Beds Delete',
                        'name'       => 'beds_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'beds/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Beds Show',
                        'name'       => 'beds_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'beds/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Therapist Profiles',
                'name'       => 'therapist_profiles',
                'guard_name' => 'sanctum',
                'url'        => 'therapist-profile',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Therapist Profiles Create',
                        'name'       => 'therapist_profiles_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'therapist-profile/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Therapist Profiles Edit',
                        'name'       => 'therapist_profiles_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'therapist-profile/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Therapist Profiles Delete',
                        'name'       => 'therapist_profiles_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'therapist-profile/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Therapist Profiles Show',
                        'name'       => 'therapist_profiles_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'therapist-profile/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Massage Sessions',
                'name'       => 'massage_sessions',
                'guard_name' => 'sanctum',
                'url'        => 'massage-session',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Massage Sessions Create',
                        'name'       => 'massage_sessions_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'massage-session/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Massage Sessions Edit',
                        'name'       => 'massage_sessions_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'massage-session/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Massage Sessions Delete',
                        'name'       => 'massage_sessions_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'massage-session/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Massage Sessions Show',
                        'name'       => 'massage_sessions_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'massage-session/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Front Desk',
                'name'       => 'front_desk',
                'guard_name' => 'sanctum',
                'url'        => 'front-desk',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Front Desk Show',
                        'name'       => 'front_desk_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'front-desk/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Session Queue',
                'name'       => 'session_queue',
                'guard_name' => 'sanctum',
                'url'        => 'session-queue',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Session Queue Create',
                        'name'       => 'session_queue_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'session-queue/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Session Queue Edit',
                        'name'       => 'session_queue_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'session-queue/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Session Queue Delete',
                        'name'       => 'session_queue_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'session-queue/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Session Queue Show',
                        'name'       => 'session_queue_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'session-queue/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Room Schedule',
                'name'       => 'room_schedule',
                'guard_name' => 'sanctum',
                'url'        => 'room-schedule',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Room Schedule Show',
                        'name'       => 'room_schedule_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'room-schedule/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Group Sessions',
                'name'       => 'group_sessions',
                'guard_name' => 'sanctum',
                'url'        => 'group-session',
                'created_at' => now(),
                'updated_at' => now(),
                'children'   => [
                    [
                        'title'      => 'Group Sessions Create',
                        'name'       => 'group_sessions_create',
                        'guard_name' => 'sanctum',
                        'url'        => 'group-session/create',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Group Sessions Edit',
                        'name'       => 'group_sessions_edit',
                        'guard_name' => 'sanctum',
                        'url'        => 'group-session/edit',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Group Sessions Delete',
                        'name'       => 'group_sessions_delete',
                        'guard_name' => 'sanctum',
                        'url'        => 'group-session/delete',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'title'      => 'Group Sessions Show',
                        'name'       => 'group_sessions_show',
                        'guard_name' => 'sanctum',
                        'url'        => 'group-session/show',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ],
            ],
            [
                'title'      => 'Service Report',
                'name'       => 'service-report',
                'guard_name' => 'sanctum',
                'url'        => 'service-report',
                'created_at' => now(),
                'updated_at' => now(),
            ],


        ];

        $permissions = AppLibrary::associativeToNumericArrayBuilder($permissions);
        Permission::insert($permissions);
    }
}

