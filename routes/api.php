<?php

use App\Jobs\BiometricPunchResolutionJob;
use App\Models\BiometricDevice;
use App\Models\BiometricPunch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/adms/punch', function (Request $request) {
    $request->validate([
        'SN' => 'required|string',
        'table' => 'required|array',
        'table.*.SN' => 'required|string',
        'table.*.PIN' => 'required|integer',
        'table.*.T' => 'required|date_format:Y-m-d\TH:i:s',
        'table.*.ST' => 'required|in:0,1',
    ]);

    $serialNumber = $request->input('SN');

    $device = BiometricDevice::where('serial_number', $serialNumber)->first();

    if (!$device) {
        return response()->json(['message' => 'Device not registered'], 404);
    }

    $device->markSeen($request->ip());

    $punchIds = [];

    foreach ($request->input('table') as $entry) {
        $punch = BiometricPunch::create([
            'biometric_device_id' => $device->id,
            'device_user_id' => $entry['PIN'],
            'punch_time' => $entry['T'],
            'punch_state' => $entry['ST'] === '1' ? 'check_in' : 'check_out',
            'resolution_status' => 'pending',
        ]);

        $punchIds[] = $punch->id;
    }

    BiometricPunchResolutionJob::dispatch($punchIds);

    return response()->json(['message' => 'OK', 'punches' => count($punchIds)]);
})->middleware('throttle:60,1');
