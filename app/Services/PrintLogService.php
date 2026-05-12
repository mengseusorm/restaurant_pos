<?php

namespace App\Services;

use App\Models\OrderPrintLog;
use App\Enums\PrintType;
use Exception;
use Illuminate\Support\Facades\Log;

class PrintLogService
{
    /**
     * Create a print log entry
     *
     * @param array $data
     * @return OrderPrintLog
     * @throws Exception
     */
    public function createPrintLog(array $data): OrderPrintLog
    {
        try {
            Log::info($data);
            // Validate print type
            if (!in_array($data['print_type'], [PrintType::MENU, PrintType::INVOICE, PrintType::BILL])) {
                throw new Exception('Invalid print type');
            }

            // Set defaults if not provided
            $data['user_id'] = $data['user_id'] ?? auth()->id();
            $data['branch_id'] = $data['branch_id'] ?? (auth()->user()->branch_id ?? null);

            $printLog = OrderPrintLog::create([
                'user_id' => $data['user_id'] ? $data['user_id'] : 0,
                'branch_id' => $data['branch_id'] ? $data['branch_id'] : 0,
                'order_serial_number' => $data['order_serial_number'],
                'print_type' => $data['print_type'],
                'print_success' => $data['print_success'],
                'error_message' => $data['error_message'] ?? null
            ]);

            return $printLog;
        } catch (Exception $exception) {
            Log::error('Failed to create print log: ' . $exception->getMessage());
            throw new Exception('Failed to create print log: ' . $exception->getMessage());
        }
    }

    /**
     * Log successful print
     *
     * @param string $orderSerialNumber
     * @param int $printType
     * @param int|null $userId
     * @param int|null $branchId
     * @return OrderPrintLog
     * @throws Exception
     */
    public function logSuccessfulPrint(
        string $orderSerialNumber,
        int $printType,
        ?int $userId = null,
        ?int $branchId = null
    ): OrderPrintLog {
        return $this->createPrintLog([
            'order_serial_number' => $orderSerialNumber,
            'print_type' => $printType,
            'print_success' => true,
            'user_id' => $userId,
            'branch_id' => $branchId,
            'error_message' => null
        ]);
    }

    /**
     * Log failed print
     *
     * @param string $orderSerialNumber
     * @param int $printType
     * @param string $errorMessage
     * @param int|null $userId
     * @param int|null $branchId
     * @return OrderPrintLog
     * @throws Exception
     */
    public function logFailedPrint(
        string $orderSerialNumber,
        int $printType,
        string $errorMessage,
        ?int $userId = null,
        ?int $branchId = null
    ): OrderPrintLog {
        return $this->createPrintLog([
            'order_serial_number' => $orderSerialNumber,
            'print_type' => $printType,
            'print_success' => false,
            'user_id' => $userId,
            'branch_id' => $branchId,
            'error_message' => $errorMessage
        ]);
    }
}
