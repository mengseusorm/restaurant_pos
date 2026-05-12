<?php

namespace App\Http\Controllers\Admin;

use Exception;
use App\Models\OrderPrintLog;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\OrderPrintLogResource;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrderPrintLogExport;
use Illuminate\Support\Facades\Log;

class OrderPrintLogController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:pos-orders'])->only(
            'index',
            'show',
            'store',
            'destroy',
            'export'
        );
    }

    public function index(PaginateRequest $request)
    {
        try {
            $orderPrintLogs = OrderPrintLog::query();

            // Apply filters
            if ($request->filled('user_id')) {
                $orderPrintLogs->where('user_id', $request->user_id);
            }

            if ($request->filled('branch_id')) {
                $orderPrintLogs->where('branch_id', $request->branch_id);
            }

            if ($request->filled('order_serial_number')) {
                $orderPrintLogs->where('order_serial_number', 'like', '%' . $request->order_serial_number . '%');
            }

            if ($request->filled('print_type')) {
                $orderPrintLogs->where('print_type', $request->print_type);
            }

            if ($request->filled('print_success')) {
                $orderPrintLogs->where('print_success', $request->print_success);
            }

            if ($request->filled('from_date')) {
                $orderPrintLogs->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $orderPrintLogs->whereDate('created_at', '<=', $request->to_date);
            }

            $orderPrintLogs = $orderPrintLogs->orderBy($request->order_column ?? 'id', $request->order_by ?? 'desc')
                ->paginate($request->per_page ?? 10);

            return OrderPrintLogResource::collection($orderPrintLogs);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function show($id)
    {
        try {
            $orderPrintLog = OrderPrintLog::findOrFail($id);
            return new OrderPrintLogResource($orderPrintLog);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'user_id' => 'nullable|integer|exists:users,id',
                'branch_id' => 'nullable|integer|exists:branches,id',
                'order_serial_number' => 'required|string|max:255',
                'print_type' => 'required|integer|in:5,10,15',
                'print_success' => 'required|boolean',
                'error_message' => 'nullable|string|max:500'
            ]);

            // Use authenticated user if user_id not provided
            if (!isset($validatedData['user_id'])) { 
                $validatedData['user_id'] = auth()->id(); 
            }

            // Use user's branch if branch_id not provided
            if (!isset($validatedData['branch_id'])) {
                $validatedData['branch_id'] = auth()->user()->branch_id ?? null;
            }
 
            if($validatedData){
                $orderPrintLog = OrderPrintLog::create([
                    'user_id' => $validatedData['user_id'],
                    'branch_id' => $validatedData['branch_id'],
                    'order_serial_number' => $validatedData['order_serial_number'],
                    'print_type' => $validatedData['print_type'],
                    'print_success' => $validatedData['print_success'],
                    'error_message' => $validatedData['error_message'],
                ]);
            }
            // $orderPrintLog = OrderPrintLog::create($validatedData);

            

            return new OrderPrintLogResource($orderPrintLog);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $orderPrintLog = OrderPrintLog::findOrFail($id);
            $orderPrintLog->delete();
            return response(['status' => true, 'message' => 'Print log deleted successfully'], 200);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request)
    {
        try {
            $orderPrintLogs = OrderPrintLog::query();

            // Apply same filters as index
            if ($request->filled('user_id')) {
                $orderPrintLogs->where('user_id', $request->user_id);
            }

            if ($request->filled('order_serial_number')) {
                $orderPrintLogs->where('order_serial_number', 'like', '%' . $request->order_serial_number . '%');
            }

            if ($request->filled('print_type')) {
                $orderPrintLogs->where('print_type', $request->print_type);
            }

            if ($request->filled('print_success')) {
                $orderPrintLogs->where('print_success', $request->print_success);
            }

            if ($request->filled('from_date')) {
                $orderPrintLogs->whereDate('created_at', '>=', $request->from_date);
            }

            if ($request->filled('to_date')) {
                $orderPrintLogs->whereDate('created_at', '<=', $request->to_date);
            }

            return Excel::download(new OrderPrintLogExport($orderPrintLogs->get()), 'order-print-logs.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
