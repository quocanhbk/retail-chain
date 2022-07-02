<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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
     *   description="Create a shift (by admin or manager)",
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
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $data = $request->all();

        $rules = [
            "branch_id" => [
                Rule::requiredIf($as == "admin"),
                Rule::exists("branches", "id")->where("store_id", $store_id),
            ],
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("shifts")
                    ->when($as == "employee", function ($query) {
                        $query->where("branch_id", Auth::user()->employment->branch_id);
                    })
                    ->when($as == "admin", function ($query) use ($request) {
                        $query->where("branch_id", $request->get("branch_id"));
                    }),
            ],
            "start_time" => ["required", "date_format:H:i"],
            "end_time" => ["required", "date_format:H:i"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $shift = Shift::create([
            "name" => $data["name"],
            "start_time" => $data["start_time"],
            "end_time" => $data["end_time"],
            "branch_id" => $as == "admin" ? $data["branch_id"] : Auth::user()->employment->branch_id,
        ]);

        return response()->json($shift);
    }

    /**
     * @OA\Get(
     *   path="/shift",
     *   summary="Get all shifts",
     *   tags={"Shift"},
     *   operationId="getShifts",
     *   @OA\Parameter(name="branch_id", in="query", @OA\Schema(type="integer")),
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
    public function getShifts(Request $request)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        if ($as == "admin" && !$request->query("branch_id")) {
            return response()->json(["message" => "Missing branch_id"], 400);
        }

        $branch_id = $as == "admin" ? $request->query("branch_id") : Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $shifts = Shift::where("branch_id", $branch_id)
            ->whereHas("branch", function ($query) use ($store_id) {
                $query->where("store_id", $store_id);
            })
            ->where("name", "iLike", "%" . $search . "%")
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

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
    public function getShift(Request $request, $shift_id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::with("workSchedules.employee")
            ->when($as == "employee", function ($query) {
                $query->where("branch_id", Auth::user()->employment->branch_id);
            })
            ->when($as == "admin", function ($query) use ($store_id) {
                $query->whereHas("branch", function ($query) use ($store_id) {
                    $query->where("store_id", $store_id);
                });
            })
            ->find($shift_id);

        if (!$shift) {
            return response()->json(["message" => "Shift not found."], 404);
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
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::when($as == "employee", function ($query) {
            $query->where("branch_id", Auth::user()->employment->branch_id);
        })
            ->when($as == "admin", function ($query) use ($store_id) {
                $query->whereHas("branch", function ($query) use ($store_id) {
                    $query->where("store_id", $store_id);
                });
            })
            ->find($shift_id);

        if (!$shift) {
            return response()->json(["message" => "Shift not found."], 404);
        }

        $data = $request->all();
        $rules = [
            "name" => [
                "nullable",
                "string",
                "max:255",
                Rule::unique("shifts")
                    ->where("branch_id", $shift->branch_id)
                    ->ignore($shift_id),
            ],
            "start_time" => ["nullable", "date_format:H:i"],
            "end_time" => ["nullable", "date_format:H:i"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
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
     *     @OA\JsonContent(
     *       required={"message"},
     *       @OA\Property(property="message", type="string", description="Success message")
     *     )
     *   )
     * )
     */
    public function delete(Request $request, $shift_id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::when($as == "employee", function ($query) {
            $query->where("branch_id", Auth::user()->employment->branch_id);
        })
            ->when($as == "admin", function ($query) use ($store_id) {
                $query->whereHas("branch", function ($query) use ($store_id) {
                    $query->where("store_id", $store_id);
                });
            })
            ->find($shift_id);

        if (!$shift) {
            return response()->json(["message" => "Shift not found."], 404);
        }

        $shift->delete();

        return response()->json([
            "message" => "Shift deleted.",
        ]);
    }
}
