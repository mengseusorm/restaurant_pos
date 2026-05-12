<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PaymentMethodExport;
use App\Exports\PaymentMethodReportExport;
use App\Http\Resources\SimpleOrderResource;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\PaginateRequest;
use App\Http\Resources\PaymentMethodReportResource;
use App\Http\Resources\SimplePaymentMethodResource;
use App\Models\ThemeSetting;
use App\Services\CompanyService;
use App\Services\PaymentMethodService;
use App\Services\ThemeService;
use Smartisan\Settings\Facades\Settings;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

class PaymentMethodReportController extends AdminController
{
/**
 * Display a listing of the resource.
 *
 * @return \Illuminate\Http\Response
 */

    private PaymentMethodService $paymentMethodService;
    private CompanyService $companyService;
    private ThemeService $themeService;

    public function __construct(paymentMethodService $paymentMethodService,CompanyService $companyService, ThemeService $themeService)
    {
        parent::__construct();
        $this->paymentMethodService = $paymentMethodService;
        $this->companyService= $companyService;
        $this->themeService  = $themeService;
        $this->middleware(['permission:payment-method-report'])->only('index', 'export', 'pdf');
    }

    public function index(PaginateRequest $request): \Illuminate\Http\Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return PaymentMethodReportResource::collection($this->paymentMethodService->list($request));
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function export(PaginateRequest $request) : \Illuminate\Http\Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            return Excel::download(new PaymentMethodReportExport($this->paymentMethodService,$request),'payment_method_report.xlsx');
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    public function pdf(PaginateRequest $request):mixed
    {
        try {
            $company = $this->companyService->list();
            $theme_logo   = ThemeSetting::where(['key' => 'theme_logo'])->first()?->logo;
            $copyright   = Settings::group('site')->get('site_copyright');
            $items = $this->paymentMethodService->list($request);

            // Get branch open_time and close_time
            $branch = \App\Models\Branch::find(auth()->user()->branch_id);

            if ($request->get('from_date')) {
                $fromDate = \Carbon\Carbon::parse($request->get('from_date'))->format('m/d/Y, h:i A');
            } else {
                // Default to yesterday with branch open_time
                $fromDate = \Carbon\Carbon::now()->subDay()->startOfDay();
                if ($branch && $branch->open_time) {
                    $time = explode(':', $branch->open_time);
                    $fromDate->setTime((int)$time[0], (int)$time[1], 0);
                }
                $fromDate = $fromDate->format('m/d/Y, h:i A');
            }

            if ($request->get('to_date')) {
                $toDate = \Carbon\Carbon::parse($request->get('to_date'))->format('m/d/Y, h:i A');
            } else {
                // Default to today with branch close_time
                $toDate = \Carbon\Carbon::now()->startOfDay();
                if ($branch && $branch->close_time) {
                    $time = explode(':', $branch->close_time);
                    $toDate->setTime((int)$time[0], (int)$time[1], 59);
                } else {
                    $toDate->endOfDay();
                }
                $toDate = $toDate->format('m/d/Y, h:i A');
            }

            $html = view('pdf.payments_report', compact('company', 'theme_logo', 'items', 'copyright', 'fromDate', 'toDate'))->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'margin_top' => 10,
                'margin_bottom' => 20,
                'margin_left' => 15,
                'margin_right' => 15,
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
            ]);
            $mpdf->WriteHTML($html);

            return response()->stream(
                fn() => print($mpdf->Output('', 'S')),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="payments_report.pdf"',
                ]
            );
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

}
