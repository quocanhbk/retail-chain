<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    /**
     * @OA\Post(
     *   path="/shift",
     *   summary="Create a new shift",
     *   tags={"Shift"},
     *   operationId="createShift",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateShiftInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Shift")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $branch_id = Auth::user()->employment->branch_id;
        $data = $request->all();
        $rules = [
            "name" => ["required", "string", "max:255", Rule::unique("shifts")->where("branch_id", $branch_id)],
            "start_time" => ["required", "date_format:H:i"],
            "end_time" => ["required", "date_format:H:i"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
        }

        $shift = Shift::create([
            "name" => $data["name"],
            "start_time" => $data["start_time"],
            "end_time" => $data["end_time"],
            "branch_id" => $branch_id,
        ]);

        return response()->json($shift);
    }

    /**
     * @OA\Get(
     *   path="/shift",
     *   summary="Get all shifts",
     *   tags={"Shift"},
     *   operationId="getAllShifts",
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Shift")
     *     )
     *   )
     * )
     */
    public function getShifts()
    {
        $branch_id = Auth::user()->employment->branch_id;

        $shifts = Shift::where("branch_id", $branch_id)->get();

        return response()->json($shifts);
    }

    /**
     * @OA\Get(
     *   path="/shift/{shift_id}",
     *   summary="Get a shift",
     *   tags={"Shift"},
     *   operationId="getShift",
     *   @OA\Parameter(
     *     name="shift_id",
     *     in="path",
     *     description="Shift ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Shift")
     *   )
     * )
     */
    public function getShift($shift_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $shift = Shift::with("workSchedules.employee")
            ->where("branch_id", $branch_id)
            ->find($shift_id);

        if (!$shift) {
            return response()->json(
                [
                    "message" => "Shift not found.",
                ],
                404
            );
        }

        return response()->json($shift);
    }

    /**
     * @OA\Put(
     *   path="/shift/{shift_id}",
     *   summary="Update a shift",
     *   tags={"Shift"},
     *   operationId="updateShift",
     *   @OA\Parameter(
     *     name="shift_id",
     *     in="path",
     *     description="Shift ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpsertShiftInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Shift")
     *   )
     * )
     */
    public function update(Request $request, $shift_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $shift = Shift::where("branch_id", $branch_id)->find($shift_id);

        if (!$shift) {
            return response()->json(
                [
                    "message" => "Shift not found.",
                ],
                404
            );
        }

        $data = $request->all();
        $rules = [
            "name" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("shifts")
                    ->where("branch_id", $branch_id)
                    ->ignore($shift_id),
            ],
            "start_time" => ["nullable", "date_format:H:i"],
            "end_time" => ["nullable", "date_format:H:i"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
        }

        $shift->name = $data["name"] ?? $shift->name;
        $shift->start_time = $data["start_time"] ?? $shift->start_time;
        $shift->end_time = $data["end_time"] ?? $shift->end_time;
        $shift->save();

        return response()->json($shift);
    }

    /**
     * @OA\Delete(
     *   path="/shift/{shift_id}",
     *   summary="Delete a shift",
     *   tags={"Shift"},
     *   operationId="deleteShift",
     *   @OA\Parameter(
     *     name="shift_id",
     *     in="path",
     *     description="Shift ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Shift")
     *   )
     * )
     */
    public function delete($shift_id)
    {
        $branch_id = Auth::user()->employment->branch_id;

        $shift = Shift::where("branch_id", $branch_id)->find($shift_id);

        if (!$shift) {
            return response()->json(
                [
                    "message" => "Shift not found.",
                ],
                404
            );
        }

        $shift->delete();

        return response()->json($shift);
    }
}
