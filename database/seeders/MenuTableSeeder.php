<?php

namespace Database\Seeders;

use App\Libraries\AppLibrary;
use App\Models\Menu;
use Illuminate\Database\Seeder;


class MenuTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = [
            [
                'name'       => 'Dashboard',
                'language'   => 'dashboard',
                'url'        => 'dashboard',
                'icon'       => 'lab lab-dashboard',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
            ],
            [
                'name'       => 'Dining Tables',
                'language'   => 'dining_tables',
                'url'        => '#',
                'icon'       => 'lab lab-dining-table',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
                'children'   => [
                    
                    [
                        'name'       => 'Floor Plan',
                        'language'   => 'floor_plan',
                        'url'        => 'floor-plan',
                        'icon'       => 'lab lab-item-categories',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Dining Tables',
                        'language'   => 'dining_tables',
                        'url'        => 'dining-tables',
                        'icon'       => 'lab lab-dining-table',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ]
                ]
            ],
            
            [
                'name'       => 'Pos & Orders',
                'language'   => 'pos_and_orders',
                'url'        => '#',
                'icon'       => 'lab lab-pos',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
                'children'   => [
                    [
                        'name'       => 'POS',
                        'url'        => 'pos',
                        'language'   => 'pos',
                        'icon'       => 'lab lab-pos',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3,

                    ],
                    [
                        'name'       => 'Orders',
                        'language'   => 'orders',
                        'url'        => 'pos-orders',
                        'icon'       => 'lab lab-pos-orders',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    // [
                    //     'name'       => 'Pending Orders',
                    //     'language'   => 'pending_orders',
                    //     'url'        => 'pending-orders',
                    //     'icon'       => 'lab lab-pos-orders',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // // add menu pos order unpaid
                    // [
                    //     'name'       => 'POS Orders Unpaid',
                    //     'language'   => 'pos_orders_unpaid',
                    //     'url'        => 'pos-orders-unpaid',
                    //     'icon'       => 'lab lab-fill-moneys',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'POS Orders',
                    //     'language'   => 'pos_orders',
                    //     'url'        => 'pos-orders',
                    //     'icon'       => 'lab lab-pos-orders',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],

                    // [
                    //     'name'       => 'Table Orders',
                    //     'language'   => 'table_orders',
                    //     'url'        => 'table-orders',
                    //     'icon'       => 'lab lab-reserve-line',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()

                    // ],
                    // [
                    //     'name'       => 'Online Orders',
                    //     'language'   => 'online_orders',
                    //     'url'        => 'online-orders',
                    //     'icon'       => 'lab lab-online-orders',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'Telegram Orders',
                    //     'language'   => 'telegram_mini_app_orders',
                    //     'url'        => 'telegram-mini-app-orders',
                    //     'icon'       => 'lab lab-online-orders',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    [
                        'name'       => 'Orders Deleted',
                        'language'   => 'order_deleted',
                        'url'        => 'pos-order-deleted',
                        'icon'       => 'lab lab-pos-orders',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ]
                    // end
                ]
            ],
            [
                'name'       => 'Customer',
                'language'   => 'customer',
                'url'        => '#',
                'icon'       => 'lab-pos',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
                'children'   => [
                    [
                        'name'       => 'Reservations',
                        'language'   => 'reservations',
                        'url'        => 'reservation',
                        'icon'       => 'lab lab-calendar',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Lost & Found',
                        'language'   => 'lost_and_found',
                        'url'        => 'lost-and-found',
                        'icon'       => 'lab lab-save-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Customer Beverage Storage',
                        'language'   => 'customer_beverage_storage',
                        'url'        => 'customer-beverage-storage',
                        'icon'       => 'lab lab-save-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ]
                ]
            ],
            [
                'name'       => 'Reports',
                'language'   => 'reports',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Order Report',
                        'language'   => 'order_report',
                        'url'        => 'order-report',
                        'icon'       => 'lab lab-items-report',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Daily Sale Report',
                        'language'   => 'daily_sale_report',
                        'url'        => 'daily-sale-report',
                        'icon'       => 'lab lab-calendar-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Daily Sale Summary Report',
                        'language'   => 'daily_sale_summary_report',
                        'url'        => 'daily-sale-summary-report',
                        'icon'       => 'lab lab-calendar-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Sales Summary Report',
                        'language'   => 'sales_summary_report',
                        'url'        => 'sales-summary-report',
                        'icon'       => 'lab lab-sale-summary',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ],    
                    
                    // [
                    //     'name'       => 'Sales Report',
                    //     'language'   => 'sales_report',
                    //     'url'        => 'sales-report',
                    //     'icon'       => 'lab lab-sales-report',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()

                    // ],
                    // add sale sumary report
                    
                    [
                        'name'       => 'Staff Sale Summary',
                        'language'   => 'sales_summary_report_staff',
                        'url'        => 'sales-summary-report-staff',
                        'icon'       => 'lab lab-sale-summary',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ],


                    //end
                    // [
                    //     'name'       => 'Items Report',
                    //     'language'   => 'items_report',
                    //     'url'        => 'items-report',
                    //     'icon'       => 'lab lab-items-report',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'Items Detail Report',
                    //     'language'   => 'items_detail_report',
                    //     'url'        => 'items-detail-report',
                    //     'icon'       => 'lab lab-items-report',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'Items Category Report',
                    //     'language'   => 'items_category_report',
                    //     'url'        => 'items-category-report',
                    //     'icon'       => 'lab lab-item-categories',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    
                    //add menu
                    
                    // [
                    //     'name'       => 'Payment Method Report',
                    //     'language'   => 'payment_method_report',
                    //     'url'        => 'payment-method-report',
                    //     'icon'       => 'lab lab-payment-gateway',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'Order Type Report',
                    //     'language'   => 'order_type_report',
                    //     'url'        => 'order-type-report',
                    //     'icon'       => 'lab lab-items-report',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    // [
                    //     'name'       => 'Order Source Report',
                    //     'language'   => 'order_source_report',
                    //     'url'        => 'order-source-report',
                    //     'icon'       => 'lab lab-items-report',
                    //     'priority'   => 100,
                    //     'status'     => 1,
                    //     'created_at' => now(),
                    //     'updated_at' => now()
                    // ],
                    
                    [
                        'name'       => 'User Sales Report',
                        'language'   => 'user_sales_report',
                        'url'        => 'user-sales-report',
                        'icon'       => 'lab lab-employee',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Shop Sales Summary Report',
                        'language'   => 'branch_sales_summary_report',
                        'url'        => 'branch-sales-summary-report',
                        'icon'       => 'lab lab-sales-report',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ],
                    [
                        'name'       => 'Credit Balance Report',
                        'language'   => 'credit_balance_report',
                        'url'        => 'credit-balance-report',
                        'icon'       => 'lab lab-credit-balance-report',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],

                ]
            ],
            [
                'name'       => 'HQ Reports',
                'language'   => 'hq_reports',
                'url'        => '#',
                'icon'       => 'lab lab-hq-reports',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'HQ Dashboard',
                        'language'   => 'hq_dashboard',
                        'url'        => 'hq-dashboard',
                        'icon'       => 'lab lab-dashboard',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Shop Sale Report',
                        'language'   => 'branch_sale_report',
                        'url'        => 'branch-sale-report',
                        'icon'       => 'lab lab-shop',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Shop Trend Report',
                        'language'   => 'branch_trend_report',
                        'url'        => 'branch-trend-report',
                        'icon'       => 'lab lab-shop',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Shop Daily Sale Report',
                        'language'   => 'branch_daily_sale_report',
                        'url'        => 'branch-daily-sale-report',
                        'icon'       => 'lab lab-calendar-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Shop Expense Report',
                        'language'   => 'shop_expense_report',
                        'url'        => 'shop-expense-report',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 99,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ],
            [
                'name'       => 'Accounts',
                'language'   => 'accounts',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Transactions',
                        'language'   => 'transactions',
                        'url'        => 'transactions',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ],
                    [
                        'name'       => 'PayWay Transactions',
                        'language'   => 'payway_transactions',
                        'url'        => 'payway-transactions',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 99,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ],
                    [
                        'name'       => 'Expenses',
                        'language'   => 'expenses',
                        'url'        => 'expenses',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 97,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Expense Report',
                        'language'   => 'expense_report',
                        'url'        => 'expense-report',
                        'icon'       => 'lab lab-sales-report',
                        'priority'   => 96,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Expense Types',
                        'language'   => 'expense_types',
                        'url'        => 'expense-types',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 99,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Expense Payment Methods',
                        'language'   => 'expense_payment_methods',
                        'url'        => 'expense-payment-methods',
                        'icon'       => 'lab lab-transactions',
                        'priority'   => 98,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ],
            [
                'name'       => 'Stock',
                'language'   => 'warehouse',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Warehouse',
                        'language'   => 'warehouse',
                        'url'        => 'stocks',
                        'icon'       => 'lab lab-shop',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Stock Records',
                        'language'   => 'stock_records',
                        'url'        => 'stock-records',
                        'icon'       => 'lab lab-pos',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Stocks Report',
                        'language'   => 'stocks_report',
                        'url'        => 'stocks-report',
                        'icon'       => 'lab lab-sales-report',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ],
            [
                'name'       => 'Promo',
                'language'   => 'promo',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Offers',
                        'language'   => 'offers',
                        'url'        => 'offers',
                        'icon'       => 'lab lab-offers',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1,

                    ]
                ]
            ],
            [
                'name'       => 'Point Management',
                'language'   => 'point_management',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Point Earn Settings',
                        'language'   => 'point_earn_settings',
                        'url'        => 'point-earn-rules',
                        'icon'       => 'lab lab-shop',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Point Redeem Settings',
                        'language'   => 'point_redeem_settings',
                        'url'        => 'point-usage-rules',
                        'icon'       => 'lab lab-pos',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ], 
            [
                'name'       => 'Users',
                'language'   => 'users',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Administrators',
                        'language'   => 'administrators',
                        'url'        => 'administrators',
                        'icon'       => 'lab lab-administrators',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Customers',
                        'language'   => 'customers',
                        'url'        => 'customers',
                        'icon'       => 'lab lab-customers',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Employees',
                        'language'   => 'employees',
                        'url'        => 'employees',
                        'icon'       => 'lab lab-employee',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ],
                    [
                        'name'       => 'Members',
                        'language'   => 'members',
                        'url'        => 'members',
                        'icon'       => 'lab lab-customers',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ],
            [
                'name'       => 'Setup',
                'language'   => 'setup',
                'url'        => '#',
                'icon'       => 'lab ',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 1,
                'children'   => [
                    [
                        'name'       => 'Settings',
                        'language'   => 'settings',
                        'url'        => 'settings',
                        'icon'       => 'lab lab-settings',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ], 
                    [
                        'name'       => 'Items',
                        'language'   => 'items',
                        'url'        => 'items',
                        'icon'       => 'lab lab-items',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 1
                    ]
                ]
            ],
            [
                'name'       => 'Massage Shop Setting',
                'language'   => 'massage_shop_setting',
                'url'        => '#',
                'icon'       => 'lab lab-settings',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
                'children'   => [
                    [
                        'name'       => 'Rooms',
                        'language'   => 'rooms',
                        'url'        => 'rooms',
                        'icon'       => 'lab lab-rooms',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Beds',
                        'language'   => 'beds',
                        'url'        => 'beds',
                        'icon'       => 'lab lab-reserve-line',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Therapist Profiles',
                        'language'   => 'therapist_profiles',
                        'url'        => 'therapist-profile',
                        'icon'       => 'lab lab-therapist',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ]
                ]
            ],
            [
                'name'       => 'Massage Shop',
                'language'   => 'massage_shop',
                'url'        => '#',
                'icon'       => 'lab lab-massage',
                'priority'   => 100,
                'status'     => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'type'       => 3,
                'children'   => [
                    [
                        'name'       => 'Front Desk',
                        'language'   => 'front_desk',
                        'url'        => 'front-desk',
                        'icon'       => 'lab lab-desk',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Session Queue',
                        'language'   => 'session_queue',
                        'url'        => 'session-queue',
                        'icon'       => 'lab lab-queue',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Massage Sessions',
                        'language'   => 'massage_sessions',
                        'url'        => 'massage-session',
                        'icon'       => 'lab lab-session',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Room Schedule',
                        'language'   => 'room_schedule',
                        'url'        => 'room-schedule',
                        'icon'       => 'lab lab-calendar',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Group Sessions',
                        'language'   => 'group_sessions',
                        'url'        => 'group-session',
                        'icon'       => 'lab lab-customers',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ],
                    [
                        'name'       => 'Service Report',
                        'language'   => 'service_report',
                        'url'        => 'service-report',
                        'icon'       => 'lab lab-sales-report',
                        'priority'   => 100,
                        'status'     => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'type'       => 3
                    ]
                ]
            ]
        ];

        Menu::insert(AppLibrary::associativeToNumericArrayBuilder($menus));
    }
}

