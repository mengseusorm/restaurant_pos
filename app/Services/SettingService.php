<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smartisan\Settings\Facades\Settings;
use Illuminate\Http\Request;

class SettingService
{
    public function list($request = null) : array
    {
        // Check if last_updated parameter is provided
        if ($request && $request->has('last_updated')) {
            $lastUpdated = $request->get('last_updated');
            
            // Query the settings table to check if any settings were updated after the timestamp
            $hasUpdates = DB::table('settings')
                ->where('updated_at', '>', $lastUpdated)
                ->exists();
            
            // If no updates, return indicator
            if (!$hasUpdates) {
                return ['has_updates' => false];
            }
        }
        
        // Fetch all settings
        $array = [];
        $array = array_merge($array, Settings::group('company')->all());
        $array = array_merge($array, Settings::group('site')->all());
        $array = array_merge($array, Settings::group('theme')->all());
        $array = array_merge($array, Settings::group('otp')->all());
        $array = array_merge($array, Settings::group('social_media')->all());
        $array = array_merge($array, Settings::group('notification')->all()); 
        return $array = array_merge($array, Settings::group('order_setup')->all());
    }
}
