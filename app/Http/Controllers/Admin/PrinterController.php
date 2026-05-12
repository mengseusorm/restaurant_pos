<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Log;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
use Illuminate\Http\Request;
use Exception;
use Intervention\Image\ImageManagerStatic as Image;
use App\Services\PrintLogService;
use App\Enums\PrintType;

class PrinterController extends AdminController
{
    protected $printLogService;

    public function __construct(PrintLogService $printLogService)
    {
        parent::__construct();
        $this->printLogService = $printLogService;
    }
    
    public function printLabel(Request $request)
    {
        // Get input data
        $data = $request->input('printLabelData');
        $printerIp = $request->input('ip');
        $printerPort = $request->input('port');

        // Log the parsed data
        Log::info('Print Label Data:', ['data' => $data]);

        // Validate input
        if (!is_array($data) || empty($data)) {
            return response()->json(['success' => false, 'message' => 'Invalid or empty printLabelData'], 400);
        }
        if (empty($printerIp) || !is_string($printerIp) || empty($printerPort) || !is_numeric($printerPort)) {
            return response()->json(['success' => false, 'message' => 'Invalid printer IP or port'], 400);
        }

        // Establish printer connection
        $fp = @fsockopen($printerIp, $printerPort, $errno, $errstr, 10);
        if (!$fp) {
            return response()->json(['success' => false, 'message' => "Could not connect to printer: $errstr ($errno)"], 500);
        }

        try {
            foreach ($data as $label) {
                // Validate label data
                if (!is_array($label) || empty($label['companyName']) || empty($label['orderNo']) || empty($label['items'])) {
                    Log::warning('Invalid label data:', ['label' => $label]);
                    continue; // Skip invalid labels
                }

                $companyName = $label['companyName'];
                $orderNo = $label['orderNo'];
                $items = $label['items'];

                // Build TSPL for the entire label
                $tspl = "SIZE 50 mm,40 mm\n";
                $tspl .= "GAP 3 mm,0 mm\n";
                $tspl .= "CLS\n";
                $y = 30;

                // Company name (centered)
                if ($companyName) {
                    $tspl .= "TEXT 180,{$y},\"3\",0,1,1,\"{$companyName}\"\n";
                    $y += 50;
                }

                // Order No
                if ($orderNo) {
                    $tspl .= "TEXT 30,{$y},\"2\",0,1,1,\"Order No.: #{$orderNo}\"\n";
                    $y += 40;
                }

                // Divider line
                $tspl .= "TEXT 30,{$y},\"2\",0,1,1,\"--------------------------------\"\n";
                $y += 30;

                // Items
                foreach ($items as $item) {
                    $itemName = $item['name'] ?? '';
                    $itemQty = $item['qty'] ?? 0;

                    if ($itemName && $itemQty > 0) {
                        $tspl .= "TEXT 30,{$y},\"2\",0,1,1,\"Item: {$itemName}\"\n";
                        $y += 30;
                        $tspl .= "TEXT 30,{$y},\"2\",0,1,1,\"Qty: {$itemQty}\"\n";
                        $y += 30;
                    }
                }

                // Final divider
                $tspl .= "TEXT 30,{$y},\"2\",0,1,1,\"--------------------------------\"\n";
                $y += 30;

                // Print command for the entire label
                $tspl .= "PRINT 1\n";

                // Send to printer
                fwrite($fp, $tspl);
            }

            fclose($fp);
            return response()->json(['success' => true, 'message' => 'Labels printed successfully!']);
        } catch (\Exception $e) {
            fclose($fp);
            Log::error('Error printing labels:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error printing labels: ' . $e->getMessage()], 500);
        }
    }


    function convertToMonochromeBitmap($inputPath, $outputPath) {
        // Load the image
        $image = imagecreatefromstring(file_get_contents($inputPath));
        if (!$image) {
            return false;
        }

        // Get image dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Create a new monochrome image
        $monoImage = imagecreatetruecolor($width, $height);
        imagefilter($monoImage, IMG_FILTER_GRAYSCALE); // Convert to grayscale
        imagefilter($monoImage, IMG_FILTER_CONTRAST, -100); // Increase contrast for monochrome effect

        // Copy the original image to the monochrome image
        imagecopy($monoImage, $image, 0, 0, 0, 0, $width, $height);

        // Save as monochrome bitmap
        imagewbmp($monoImage, $outputPath);

        // Free memory
        imagedestroy($image);
        imagedestroy($monoImage);

        return true;
    }


    public function print(Request $request)
    { 
        $request->validate([
            'image' => 'required|string',
            'ip' => 'required|ip',
            'port' => 'sometimes|integer|min:1|max:65535',
            'order_serial_number' => 'sometimes|string|max:255',
            'print_type' => 'sometimes|integer|in:5,10,15,20'
        ]);

        // Get print details for logging
        $orderSerialNumber = $request->order_serial_number ?? 'DIRECT-PRINT-' . now()->timestamp;
        $printType = $request->print_type ?? PrintType::INVOICE; // Default to invoice

        try {
            // Extract pure base64 if data URI is provided
            $base64Image = $request->image;
            if (strpos($base64Image, ';base64,') !== false) {
                $base64Image = explode(',', $base64Image)[1];
            }

            // Decode and save to temporary file
            $imageData = base64_decode($base64Image);
            $tempFile = tempnam(sys_get_temp_dir(), 'escpos') . '.png';

            // Resize for 80mm paper (typically 576px width for thermal printers)
            $img = Image::make($imageData)->resize(576, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($tempFile);
            $port = $request->port ?? 9100;
            $connector = new NetworkPrintConnector($request->ip, $port);
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $escposImage = EscposImage::load($tempFile, false);
            $printer->bitImage($escposImage);

            $printer->feed(2);
            $printer->cut(Printer::CUT_PARTIAL);
            $printer->close();
            unlink($tempFile);

            // Log successful print
            $this->printLogService->logSuccessfulPrint(
                $orderSerialNumber,
                $printType,
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Image sent to printer successfully'
            ]);

        } catch (Exception $e) {
            // Log failed print
            $this->printLogService->logFailedPrint(
                $orderSerialNumber,
                $printType,
                $e->getMessage(),
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json([
                'success' => false,
                'message' => 'Printing failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    function printReceiptUSB(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'printer_name' => 'required|string',
            'vendor_id' => 'sometimes|string',
            'product_id' => 'sometimes|string',
            'invoice_id' => 'sometimes|string|max:255',
            'print_copies' => 'sometimes|integer|min:1',
            'printer_type' => 'sometimes|integer|in:5,10,15',
            'open_cash_drawer' => 'sometimes|boolean'
        ]);

        // Get print details for logging
        $orderSerialNumber = $request->invoice_id ?? 'USB-PRINT-' . now()->timestamp;
        $printType = $request->printer_type ?? PrintType::INVOICE; // Default to invoice
        $printCopies = $request->print_copies ?? 1;

        try {
            // Extract pure base64 if data URI is provided
            $base64Image = $request->image;
            if (strpos($base64Image, ';base64,') !== false) {
                $base64Image = explode(',', $base64Image)[1];
            }

            // Decode and save to temporary file
            $imageData = base64_decode($base64Image);
            $tempFile = tempnam(sys_get_temp_dir(), 'escpos') . '.png';

            // Resize for 80mm paper (typically 576px width for thermal printers)
            $img = Image::make($imageData)->resize(576, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->save($tempFile);

            // Use Windows connector for USB printing
            $connector = new WindowsPrintConnector($request->vendor_id);
            $printer = new Printer($connector);
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            $escposImage = EscposImage::load($tempFile, false);

            // Print multiple copies if specified
            for ($i = 0; $i < $printCopies; $i++) {
                $printer->bitImage($escposImage);
                $printer->feed(2);
                if ($request->open_cash_drawer) {
                    $printer->pulse();
                }
                $printer->cut(Printer::CUT_PARTIAL);
            }

            $printer->close();
            unlink($tempFile);

            // Log successful print
            $this->printLogService->logSuccessfulPrint(
                $orderSerialNumber,
                $printType,
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Image sent to USB printer successfully'
            ]);

        } catch (Exception $e) {
            // Log failed print
            $this->printLogService->logFailedPrint(
                $orderSerialNumber,
                $printType,
                $e->getMessage(),
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json([
                'success' => false,
                'message' => 'USB printing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testConnection(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'sometimes|integer|min:1|max:65535',
            'order_serial_number' => 'sometimes|string|max:255'
        ]);

        // Get print details for logging
        $orderSerialNumber = $request->order_serial_number ?? 'TEST-PRINT-' . now()->timestamp;
        $printType = PrintType::INVOICE; // Test prints are considered as invoice type

        try {
            $port = $request->port ?? 9100;
            $connector = new NetworkPrintConnector($request->ip, $port);
            $printer = new Printer($connector);
            $printer->text("Printer connection test successful\n");
            $printer->cut();
            $printer->close();

            // Log successful test print
            $this->printLogService->logSuccessfulPrint(
                $orderSerialNumber,
                $printType,
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            // Log failed test print
            $this->printLogService->logFailedPrint(
                $orderSerialNumber,
                $printType,
                'Test connection failed: ' . $e->getMessage(),
                auth()->id(),
                auth()->user()->branch_id ?? null
            );

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
