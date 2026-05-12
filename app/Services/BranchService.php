<?php

namespace App\Services;


use App\Http\Requests\BranchRequest;
use App\Http\Requests\PaginateRequest;
use App\Models\Branch;
use Exception;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use App\Libraries\QueryExceptionLibrary;
use Carbon\Carbon;

class BranchService
{
    protected array $branchFilter = [
        'name',
        'name_kh',
        'name_cn',
        'code',
        'online_order_slug',
        'email',
        'phone',
        'latitude',
        'longitude',
        'city',
        'state',
        'zip_code',
        'address',
        'status',
        'close_business_day_time',
        'current_business_day',
        'last_updated'
    ];

    /**
     * @throws Exception
     */
    public function list(PaginateRequest $request)
    {
        try {
            $requests    = $request->all();
            $method      = $request->get('paginate', 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate', 0) == 1 ? $request->get('per_page', 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';

            return Branch::with('shopCategory')->where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->branchFilter)) {
                        if ($key == 'last_updated') {
                            // Filter branches updated after the provided timestamp
                            $query->where('updated_at', '>', $request);
                        } else {
                            $query->where($key, 'like', '%' . $request . '%');
                        }
                    }
                }
            })->orderBy($orderColumn, $orderType)->$method(
                $methodValue
            );
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function store(BranchRequest $request)
    {
        try { 
            if (isset($request->online_order_slug) && strpos($request->online_order_slug, ' ') !== false) {
                $request->merge(['online_order_slug' => str_replace(' ', '-', $request->online_order_slug)]);
            }
            return Branch::create([
                "name" => $request->name,
                "name_kh" => $request->name_kh,
                "name_cn" => $request->name_cn,
                "code" => $request->code,
                "online_order_slug" => $request->online_order_slug,
                "email" => $request->email,
                "phone" => $request->phone,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                "city" => $request->city,
                "state" => $request->state,
                "zip_code" => $request->zip_code,
                "address" => $request->address,
                "status" => $request->status,
                "currency_id" => $request->currency_id,
                "language_id" => $request->language_id,
                "close_business_day_time" => $request->close_business_day_time,
                "current_business_day" =>  $request->current_business_day
                    ? Carbon::parse($request->current_business_day)->toDateTimeString()
                    : Carbon::today(),
                'open_time' => $request->open_time,
                'close_time' => $request->close_time,
                'show_unpaid_button' => $request->show_unpaid_button,
                'change_status_paid_to_unpaid' => $request->change_status_paid_to_unpaid,
                'show_delete_order_button' => $request->show_delete_order_button,
                // add option
                "show_select_table" => $request->show_select_table,
                "show_select_table_list" => $request->show_select_table_list,
                "show_token" => $request->show_token,
                "show_delivery" => $request->show_delivery,
                "show_waiting_number" => $request->show_waiting_number,
                "show_suspense_button" => $request->show_suspense_button,
                "show_paid_order_button" => $request->show_paid_order_button,
                "show_sidebar_table_list" => $request->show_sidebar_table_list,
                "unpaid_order_show_invoice" => $request->unpaid_order_show_invoice,
                "show_receive_amount" => $request->show_receive_amount,
                "show_select_customer" => $request->show_select_customer,
                "show_input_number_of_people" => $request->show_input_number_of_people,
                "default_selected_order_type" => $request->default_selected_order_type,
                "show_select_member" => $request->show_select_member,
                "member_can_redeem_point" => $request->member_can_redeem_point,
                "show_online_order_button" => $request->show_online_order_button,
                "show_pos_button" => $request->show_pos_button,
                "show_retail_pos_button" => $request->show_retail_pos_button,
                "show_floor_plan" => $request->show_floor_plan,
                "show_customer_view_button" => $request->show_customer_view_button,
                "show_navbar_button_text" => $request->show_navbar_button_text,
                "show_customer_name" => $request->show_customer_name,
                "show_customer_phone_number" => $request->show_customer_phone_number,
                "show_customer_address" => $request->show_customer_address,
                "show_table_button" => $request->show_table_button,
                "show_pending_order_button" => $request->show_pending_order_button,
                "show_quick_pos_button" => $request->show_quick_pos_button,
                "telegram_mini_app_slug" => $request->telegram_mini_app_slug,
                'shop_category_id' => $request->shop_category_id,
                'show_btn_print_web' => $request->show_btn_print_web,
                'show_btn_print' => $request->show_btn_print,
                'show_print_label_button' => $request->show_print_label_button,
                'show_discount_button' => $request->show_discount_button,
                'create_paid_order_confirm' => $request->create_paid_order_confirm,
                'create_unpaid_order_confirm' => $request->create_unpaid_order_confirm,
                'create_paid_order_auto_print' => $request->create_paid_order_auto_print,
                'create_unpaid_order_auto_print' => $request->create_unpaid_order_auto_print,
                'void_order_auto_print' => $request->void_order_auto_print,
                'change_item_qty_auto_print' => $request->change_item_qty_auto_print,
                'unpaid_print_bill' => $request->unpaid_print_bill,
                'unpaid_print_invoice' => $request->unpaid_print_invoice,
                'open_table_confirm' => $request->open_table_confirm,
                'payment_auto_release_table' => $request->payment_auto_release_table,
                'open_time' => $request->open_time ?? '00:00',
                'close_time' => $request->close_time ?? '23:59',
            ]);
            // return Branch::create($request->validated());

        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(BranchRequest $request, Branch $branch)
    {
        try {
            Log::info($request->all());
            if (isset($request->online_order_slug) && strpos($request->online_order_slug, ' ') !== false) {
                $request->merge(['online_order_slug' => str_replace(' ', '-', $request->online_order_slug)]);
            }
            return tap($branch)->update($request->validated());
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function destroy(Branch $branch): void
    {
        try {
            // Check if branch has any items or categories
            if ($branch->item()->exists() || $branch->itemCategories()->exists()) {
                throw new Exception("Cannot delete branch that has items or categories associated with it", 422);
            }

            // Check if it's the default branch
            if (Settings::group('site')->get("site_default_branch") != $branch->id) {
                $branch->delete();
            } else {
                throw new Exception("Default branch not deletable", 422);
            }
        } catch (Exception $exception) {
            // Log::info($exception->getMessage());
            // throw new Exception($exception->getMessage(), 422);
            Log::info(QueryExceptionLibrary::message($exception));
            throw new Exception(QueryExceptionLibrary::message($exception), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function show(Branch $branch): Branch
    {
        try {
            return $branch;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
}
