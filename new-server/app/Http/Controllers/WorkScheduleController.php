<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkScheduleController extends Controller
{
    public function create(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;
        error_log($branch_id);
        $data = $request->all();
        $rules = [
            'shift_id' => ['required', Rule::exists('shifts', 'id')->where('branch_id', $branch_id)->where('is_active', true)],
            'employee_ids' => ['required', 'array', 'min:1', Rule::exists('employments', 'employee_id')->where('branch_id', $branch_id)],
            'date' => ['required', 'date_format:Y-m-d'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 400);
        }

        // map data into work schedules
        $work_schedules = [];
        foreach ($data['employee_ids'] as $employee_id) {
            $work_schedules[] = [
                'shift_id' => $data['shift_id'],
                'employee_id' => $employee_id,
                'date' => $data['date'],
                'branch_id' => $branch_id,
            ];
        }
        WorkSchedule::insert($work_schedules);

        return response()->json($work_schedules);
    }

    public function getWorkSchedules(Request $request) {
        $branch_id = Auth::user()->employment->branch_id;

        $work_schedules = WorkSchedule::where('branch_id', $branch_id)->get();
        return response()->json($work_schedules);
    }

    public function getWorkSchedulesByDate(Request $request, $date) {
        $branch_id = Auth::user()->employment->branch_id;
        $data = [
            'date' => $date,
        ];
        $rules = [
            'date' => ['required', 'date_format:Y-m-d'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 400);
        }
        error_log($date);
        $work_schedules = WorkSchedule::where('branch_id', $branch_id)->where('date', $date)->get();
        return response()->json($work_schedules);
    }

    public function update(Request $request, $work_schedule_id) {
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $data['work_schedule_id'] = $work_schedule_id;
        $rules = [
            'work_schedule_id' => ['required', Rule::exists('work_schedules', 'id')->where('branch_id', $branch_id)],
            'note' => ['nullable', 'string', 'max:255'],
            'is_absent' => ['nullable', 'boolean'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 400);
        }

        $work_schedule = WorkSchedule::find($data['work_schedule_id']);
        $work_schedule->update([
            'note' => $data['note'] ?? $work_schedule->note,
            'is_absent' => $data['is_absent'] ?? $work_schedule->is_absent,
        ]);

        return response()->json($work_schedule);
    }

    public function delete(Request $request, $work_schedule_id) {
        $branch_id = Auth::user()->employment->branch_id;

        $work_schedule = WorkSchedule::where('branch_id', $branch_id)->find($work_schedule_id);
        if (!$work_schedule) {
            return response()->json([
                'message' => 'Work Schedule not found.',
            ], 404);
        }
        // ensure work schedule date is in the future
        if (strtotime($work_schedule->date) < strtotime(date('Y-m-d'))) {
            return response()->json([
                'message' => 'Work Schedule date must be in the future.',
            ], 400);
        }

        $work_schedule->delete();
        return response()->json($work_schedule);
    }
}
