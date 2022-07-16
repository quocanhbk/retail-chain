<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employment;
use App\Traits\EmployeeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    use EmployeeTrait;

    /**
     * @OA\Post(
     *   path="/branch",
     *   operationId="createBranch",
     *   tags={"Branch"},
     *   summary="Create a new branch",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateBranchInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Created successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Branch")
     *   ),
     * )
     */
    public function create(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:255", Rule::unique("branches")->where("store_id", $store_id)],
            "address" => ["required", "string", "max:1000"],
            "new_employees" => ["nullable", "array"],
            "new_employees.*.name" => ["required", "string", "max:255"],
            "new_employees.*.email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("employees")->where("store_id", $store_id),
            ],
            "new_employees.*.role_ids" => ["required", "array", "min:1"],
            "new_employees.*.role_ids.*" => [
                "required",
                "integer",
                Rule::exists("roles", "id")->where("store_id", $store_id),
            ],
            "new_employess.*.phone" => ["nullable", "string", "max:255"],
            "new_employees.*.birthday" => ["nullable", "date"],
            "new_employees.*.gender" => ["nullable", "string"],
            "transfered_employees" => ["nullable", "array"],
            "transfered_employees.*.id" => ["required", Rule::exists("employees", "id")->where("store_id", $store_id)],
            "transfered_employees.*.role_ids" => ["required", "array", "min:1"],
            "transfered_employees.*.role_ids.*" => [
                "required",
                "integer",
                Rule::exists("roles", "id")->where("store_id", $store_id),
            ],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $branch = Branch::create([
            "name" => $data["name"],
            "address" => $data["address"],
            "store_id" => $store_id,
            "image" => "images/default/branch.png",
            "image_key" => "default",
        ]);

        // if there are new employees, create them
        if (isset($data["new_employees"])) {
            foreach ($data["new_employees"] as $employee) {
                $employee["branch_id"] = $branch->id;
                $this->createEmployee($store_id, $employee);
            }
        }

        // if there are employees to be transferred, transfer them
        if (isset($data["transfered_employees"])) {
            foreach ($data["transfered_employees"] as $employee) {
                $this->transferEmployee($employee["id"], $branch["id"], $employee["role_ids"]);
            }
        }

        return response()->json($branch);
    }

    /**
     * @OA\Get(
     *      path="/branch/image/{image_key}",
     *      operationId="getBranchImage",
     *      tags={"Branch"},
     *      summary="Get branch image",
     *      @OA\Parameter(
     *          name="image_key",
     *          in="path",
     *          required=true,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Image retrieved successfully",
     *          @OA\MediaType(
     *              mediaType="image/*",
     *          )
     *     ),
     * )
     */
    public function getBranchImage($image_key)
    {
        $store = Auth::guard("stores")->user();

        $branch = Branch::where(["image_key" => $image_key, "store_id" => $store->id])->first();

        if (!$branch) {
            return response()->json(["message" => "Branch not found."], 404);
        }

        $file_path = $branch->image;

        if (!Storage::exists($file_path)) {
            return response()->json(["message" => "Branch image not found."], 404);
        }

        return response()->file(storage_path("app" . DIRECTORY_SEPARATOR . $file_path), [
            "Content-Type" => "image/*",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/branch",
     *   operationId="getBranches",
     *   tags={"Branch"},
     *   summary="Get branches",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Branches retrieved successfully",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Branch")
     *     )
     *   ),
     * )
     */
    public function getBranches(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        // search branch by name, address
        $branches = Branch::where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("address", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($branches);
    }

    /**
     * @OA\Get(
     *   path="/branch/{id}",
     *   operationId="getBranch",
     *   tags={"Branch"},
     *   summary="Get branch",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *      response=200,
     *      description="Branch retrieved successfully",
     *      @OA\JsonContent(ref="#/components/schemas/BranchDetail")
     *   ),
     * )
     */
    public function getBranch($id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $branch = Branch::with([
            "employments" => fn($query) => $query->where("to", null)->with("employee"),
        ])
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$branch) {
            return response()->json(["message" => "Branch not found."], 404);
        }

        return response()->json($branch);
    }

    /**
     * @OA\Put(
     *   path="/branch/{id}",
     *   operationId="updateBranch",
     *   tags={"Branch"},
     *   summary="Update branch",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/UpdateBranchInput"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Branch updated successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Branch")
     *   )
     * )
     */
    public function update(Request $request, $id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $branch = Branch::where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$branch) {
            return response()->json(["message" => "Branch not found."], 404);
        }

        $data = $request->all();

        $rules = [
            "name" => ["nullable", "string", "max:255"],
            "address" => ["nullable", "string", "max:1000"],
            "image" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $has_image = $request->hasFile("image");

        if ($has_image) {
            // delete old image if new image is uploaded
            $old_image = $branch->image;
            if ("images/default/branch.png" != $old_image) {
                Storage::delete($old_image);
            }

            $image_name = $store_id . Str::uuid();

            $path = $request
                ->file("image")
                ->storeAs(
                    "images/{$store_id}/branches",
                    $image_name . "." . $request->file("image")->getClientOriginalExtension()
                );

            $branch->image = $path;

            $branch->image_key = Str::uuid();
        }

        $branch->name = $data["name"] ?? $branch->name;

        $branch->address = $data["address"] ?? $branch->address;

        $branch->save();

        return response()->json($branch);
    }

    /**
     * @OA\Delete(
     *   path="/branch/{id}",
     *   operationId="deleteBranch",
     *   tags={"Branch"},
     *   summary="Delete branch",
     *   @OA\Parameter(name="id", in="path", required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Branch deleted successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", description="Success message")
     *     )
     *   )
     * )
     */
    public function delete($id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        // make sure store owns branch
        $branch = Branch::where(["store_id" => $store_id, "id" => $id])->first();

        if (!$branch) {
            return response()->json(["message" => "Branch not found."], 404);
        }

        $employments = Employment::where("branch_id", $id)
            ->where("to", null)
            ->get();

        // return error if there are active employments
        if ($employments->isNotEmpty()) {
            return response()->json(["message" => "Còn nhân viên làm việc tại chi nhánh này."], 400);
        }

        $branch->delete();

        return response()->json([
            "message" => "Branch deleted successfully.",
        ]);
    }
}
