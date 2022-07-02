<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WorkScheduleController extends Controller
{
    /**
     * @OA\Post(
     *   path="/work-schedule",
     *   tags={"Work Schedule"},
     *   summary="Create a work schedule",
     *   operationId="createWorkSchedule",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateWorkScheduleInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string")
     *     )
     *   ),
     * )
     */
    public function create(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();

        $rules = [
            "shift_id" => ["required", Rule::exists("shifts", "id")->where("branch_id", $branch_id)],
            "employee_ids" => ["required", "array", "min:1"],
            "employee_ids.*" => [
                "distinct",
                Rule::exists("employments", "employee_id")
                    ->where("branch_id", $branch_id)
                    ->where("to", null),
            ],
            "date" => ["required", "date_format:Y-m-d", "after:today"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        // map data into work schedules
        $work_schedules = [];
        foreach ($data["employee_ids"] as $employee_id) {
            $is_work_schedule_existed = WorkSchedule::where("employee_id", $employee_id)
                ->where("date", $data["date"])
                ->where("shift_id", $data["shift_id"])
                ->exists();

            if ($is_work_schedule_existed) {
                return response()->json(
                    ["message" => "Work schedule already exists for this employee on this date."],
                    400
                );
            }

            $work_schedules[] = [
                "shift_id" => $data["shift_id"],
                "employee_id" => $employee_id,
                "date" => $data["date"],
            ];
        }

        WorkSchedule::insert($work_schedules);

        return response()->json([
            "message" => "Work schedule created successfully",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/work-schedule",
     *   tags={"Work Schedule"},
     *   summary="Get work schedules",
     *   operationId="getWorkSchedules",
     *   @OA\Parameter(
     *     name="date",
     *     in="query",
     *     description="Filter by date",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/WorkScheduleWithShiftAndEmployee")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $date = $request->query("date") ?? null;

        if ($date) {
            $validator = Validator::make(["date" => $date], ["date" => ["nullable", "date_format:Y-m-d"]]);

            if ($validator->fails()) {
                return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
            }
        }

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        // get all work schedules by branch_id
        $work_schedules = WorkSchedule::with(["shift", "employee"])
            ->whereHas("shift", function ($query) use ($search, $branch_id) {
                $query->where("branch_id", $branch_id)->where("name", "iLike", "%$search%");
            })
            ->when($date, function ($query) use ($date) {
                return $query->where("date", $date);
            })
            ->whereHas("employee", function ($query) use ($search) {
                return $query
                    ->where("name", "iLike", "%$search%")
                    ->orWhere("email", "iLike", "%$search%")
                    ->orWhere("phone", "iLike", "%$search%");
            })
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($work_schedules);
    }

    /**
     * @OA\Put(
     *   path="/work-schedule/{id}",
     *   tags={"Work Schedule"},
     *   summary="Update a work schedule",
     *   operationId="updateWorkSchedule",
     *   @OA\Parameter(
     *     name="work_schedule_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateWorkScheduleInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/WorkSchedule")
     *   ),
     * )
     */
    public function update(Request $request, $work_schedule_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $data = $request->all();

        $work_schedule = WorkSchedule::whereHas("shift", function ($query) use ($branch_id) {
            return $query->where("branch_id", $branch_id);
        })->find($work_schedule_id);

        if (!$work_schedule) {
            return response()->json(["message" => "Work schedule not found."], 404);
        }

        $rules = [
            "note" => ["nullable", "string", "max:255"],
            "is_absent" => ["nullable", "boolean"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $work_schedule->note = $data["note"] ?? $work_schedule->note;
        $work_schedule->is_absent = $data["is_absent"] ?? $work_schedule->is_absent;
        $work_schedule->save();

        return response()->json($work_schedule);
    }

    /**
     * @OA\Delete(
     *   path="/work-schedule/{id}",
     *   tags={"Work Schedule"},
     *   summary="Delete a work schedule",
     *   operationId="deleteWorkSchedule",
     *   @OA\Parameter(
     *     name="work_schedule_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string", description="Success message")
     *     )
     *   )
     * )
     */
    public function delete($work_schedule_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $work_schedule = WorkSchedule::whereHas("shift", function ($query) use ($branch_id) {
            $query->where("branch_id", $branch_id);
        })
            ->where("id", $work_schedule_id)
            ->first();

        if (!$work_schedule) {
            return response()->json(["message" => "Work schedule not found."], 404);
        }

        $work_schedule->delete();

        return response()->json(["message" => "Work schedule deleted successfully"]);
    }
}
