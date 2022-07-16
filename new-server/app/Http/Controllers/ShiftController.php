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
                Rule::requiredIf("admin" == $as),
                Rule::exists("branches", "id")->where("store_id", $store_id),
            ],
            "name" => [
                "required",
                "string",
                "max:255",
                Rule::unique("shifts")->when(
                    "employee" == $as,
                    fn($query) => $query->where("branch_id", Auth::user()->employment->branch_id),
                    fn($query) => $query->where("branch_id", $request->get("branch_id"))
                ),
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
            "branch_id" => "admin" == $as ? $data["branch_id"] : Auth::user()->employment->branch_id,
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
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
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

        if ("admin" == $as && !$request->query("branch_id")) {
            return response()->json(["message" => "Missing branch_id"], 400);
        }

        $branch_id = "admin" == $as ? $request->query("branch_id") : Auth::user()->employment->branch_id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $shifts = Shift::where("branch_id", $branch_id)
            ->whereRelation("branch", "store_id", $store_id)
            ->where("name", "iLike", "%" . $search . "%")
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($shifts);
    }

    /**
     * @OA\Get(
     *   path="/shift/{id}",
     *   summary="Get a shift",
     *   tags={"Shift"},
     *   operationId="getShift",
     *   @OA\Parameter(
     *     name="id",
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
    public function getShift(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::with("workSchedules.employee")
            ->when(
                "employee" == $as,
                fn($query) => $query->where("branch_id", Auth::user()->employment->branch_id),
                fn($query) => $query->whereRelation("branch", "store_id", $store_id)
            )
            ->find($id);

        if (!$shift) {
            return response()->json(["message" => "Shift not found."], 404);
        }

        return response()->json($shift);
    }

    /**
     * @OA\Put(
     *   path="/shift/{id}",
     *   summary="Update a shift",
     *   tags={"Shift"},
     *   operationId="updateShift",
     *   @OA\Parameter(
     *     name="id",
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
    public function update(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::when(
            "employee" == $as,
            fn($query) => $query->where("branch_id", Auth::user()->employment->branch_id),
            fn($query) => $query->whereRelation("branch", "store_id", $store_id)
        )->find($id);

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
                    ->ignore($id),
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
     *   path="/shift/{id}",
     *   summary="Delete a shift",
     *   tags={"Shift"},
     *   operationId="deleteShift",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="Shift ID",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = $request->get("store_id");

        $as = $request->get("as");

        $shift = Shift::when(
            "employee" == $as,
            fn($query) => $query->where("branch_id", Auth::user()->employment->branch_id),
            fn($query) => $query->whereRelation("branch", "store_id", $store_id)
        )->find($id);

        if (!$shift) {
            return response()->json(["message" => "Shift not found."], 404);
        }

        $shift->delete();

        return response()->json([
            "message" => "Shift deleted.",
        ]);
    }
}
