<?php

namespace App\Services;


use App\Http\Requests\CompanyRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\printLabelRequest;
use App\Models\PrintLabelSetting;
use Database\Seeders\PrinterSeeder;
use Dipokhalder\EnvEditor\EnvEditor;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;

class PrintLabelService
{

    protected array $printerLabelSettingFilter = [
        'name',
        'show_company_name',
        'show_branch_name',
        'show_phone_number',
        'show_order_number',
        'show_order_number_barcode',
        'show_order_qr_code',
        'show_item',
        'show_item_qty',
        'show_item_price',
        'show_customer_name',
        'show_customer_phone_number',
        'show_delivery_address',
        'show_payment_status',
        'show_payment_qr_code',
        'show_payment_method',
        'print_qty',
        'label_title',
        'label_width',
        'label_height',
        'separate_item',
        'separate_qty',
        'last_updated',
    ];
    protected $exceptFilter = [
        'excepts'
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

            return PrintLabelSetting::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->printerLabelSettingFilter)) {
                        if ($key == "last_updated") {
                            // Filter print label settings updated after the provided timestamp
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
    public function store(PrintLabelRequest $request)
    {
        try { 
            return PrintLabelSetting::create($request->validated()); 
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    /**
     * @throws Exception
     */
    public function update(PrintLabelRequest $request, PrintLabelSetting $printLabelSetting)
    {
        try {  
            return tap($printLabelSetting)->update($request->validated());   
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }

    public function show(PrintLabelSetting $printLabelSetting): PrintLabelSetting
    {
        try {  
            return $printLabelSetting;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    
    /**
     * @throws Exception
     */
    public function destroy(PrintLabelSetting $printLabelSetting)
    { 
        try {
            $printLabelSetting->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422); 
        }
    } 
}
