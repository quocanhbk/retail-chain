<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ShiftController extends Controller {

    public function create(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;
        error_log($branch_id);
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255', Rule::unique('shifts')->where('branch_id', $branch_id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $shift = Shift::create([
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'branch_id' => $branch_id,
        ]);

        return response()->json($shift);
    }

    public function getShifts(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;
        $shifts = Shift::where('branch_id', $branch_id)->where('is_active', true)->get();
        return response()->json($shifts);
    }

    public function getShift(Request $request, $shift_id) {
        $branch_id = Auth::user()->employment->branch_id;
        $shift = Shift::with('workSchedules.employee')->where('branch_id', $branch_id)->where('is_active', true)->find($shift_id);
        if (!$shift) {
            return response()->json([
                'message' => 'Shift not found.',
            ], 404);
        }
        return response()->json($shift);
    }

    public function deactivate(Request $request, $shift_id) {
        $branch_id = Auth::user()->employment->branch_id;

        $shift = Shift::where('branch_id', $branch_id)->where('is_active', true)->find($shift_id);
        if (!$shift) {
            return response()->json([
                'message' => 'Shift not found.',
            ], 404);
        }

        $shift->update([
            'is_active' => false,
        ]);

        return response()->json($shift);
    }
}
