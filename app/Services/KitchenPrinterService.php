<?php

namespace App\Services;

use App\Http\Requests\KitchenPrinterRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\KitchenPrinterResource;
use App\Models\kitchenPrinter;
use Exception;
use Illuminate\Support\Facades\Log;

class KitchenPrinterService {

    protected $kitchenPrinterFilter = [
        'category_id',
        'ip',
        'port',
        'printer_type',
        'printer_method', 
        'branch_id', 
        'printer_server',
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
    
            return kitchenPrinter::where(function ($query) use ($requests) {
                foreach ($requests as $key => $request) {
                    if (in_array($key, $this->kitchenPrinterFilter)) {
                        if ($key == 'last_updated') {
                            // Filter printers updated after the provided timestamp
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($param): kitchenPrinter
    {
        try {  
            return  kitchenPrinter::where('branch_id', $param)->where('printer_type', 10)->first();  
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    /**
     * @throws Exception
     */
    public function store(KitchenPrinterRequest $request,kitchenPrinter $kitchenPrinter) : kitchenPrinter
    { 
        try { 
            $kitchenPrinter = $kitchenPrinter->create([ 
                'name'           => $request->name,
                'ip'             => $request->ip,
                'port'           => $request->port, 
                'printer_type'   => $request->printer_type,
                'printer_method' => $request->printer_method,
                'branch_id'      => $request->branch_id, 
                'printer_server' => $request->printer_server,
                'label'          => $request->label,
                'print_copies'   => $request->print_copies
            ]);
            return $kitchenPrinter;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    /**
     * @throws Exception
     */
    public function update(KitchenPrinterRequest $request, kitchenPrinter $kitchenPrinter): kitchenPrinter
    {
        try { 
            $kitchenPrinter->update([
                'name'           => $request->name,
                'ip'             => $request->ip,
                'port'           => $request->port, 
                'printer_type'   => $request->printer_type,
                'printer_method' => $request->printer_method, 
                'branch_id'      => $request->branch_id, 
                'printer_server' => $request->printer_server,
                'label'          => $request->label,
                'print_copies'   => $request->print_copies
            ]); 
            return $kitchenPrinter;
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }
    /**
     * @throws Exception
     */
    public function destroy($id){
        try {
            $kitchenPrinter = kitchenPrinter::find($id);
            return $kitchenPrinter->delete();
        } catch (Exception $exception) {
            Log::info($exception->getMessage());
            throw new Exception($exception->getMessage(), 422);
        }
    }


}