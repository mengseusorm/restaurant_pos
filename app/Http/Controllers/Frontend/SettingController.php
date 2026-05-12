<?php

namespace App\Http\Controllers\Frontend;


use App\Http\Resources\SettingResource;
use App\Services\SettingService;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private SettingService $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }

    public function index(Request $request) : \Illuminate\Http\Response | SettingResource | \Illuminate\Contracts\Foundation\Application | \Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $settings = $this->settingService->list($request);
            
            // Check if last_updated parameter was provided and no updates were found
            if ($request->has('last_updated') && isset($settings['has_updates']) && $settings['has_updates'] === false) {
                return response(['status' => true, 'message' => 'No settings updated', 'has_updates' => false], 200);
            }
            
            return new SettingResource($settings);
        } catch (Exception $exception) {
            return response(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
