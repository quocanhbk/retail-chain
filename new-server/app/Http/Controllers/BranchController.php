<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employment;
use App\Traits\EmployeeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @OA\Tag(
 *    name="Branch",
 * )
 */
class BranchController extends Controller
{
    use EmployeeTrait;

    /**
     * @OA\Post(
     *      path="/branch",
     *      operationId="createBranch",
     *      tags={"Branch"},
     *      summary="Create a new branch",
     *      @OA\RequestBody(
     *        required=true,
     *        @OA\JsonContent(ref="#/components/schemas/CreateBranchInput")
     *      ),
     *      @OA\Response(
     *        response=200,
     *        description="Created successfully",
     *        @OA\JsonContent(ref="#/components/schemas/Branch")
     *      ),
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
            "new_employees.*.password" => ["required", "string", "min:6", "confirmed"],
            "new_employees.*.roles" => ["required", "array"],
            "new_employees.*.roles.*" => ["required", Rule::in(["manage", "purchase", "sale"])],
            "new_employess.*.phone" => ["nullable", "string", "max:255"],
            "new_employees.*.birthday" => ["nullable", "date"],
            "new_employees.*.gender" => ["nullable", "string"],
            "transfered_employees" => ["nullable", "array"],
            "transfered_employees.*.id" => ["required", Rule::exists("employees", "id")->where("store_id", $store_id)],
            "transfered_employees.*.roles" => ["required", "array"],
            "transfered_employees.*.roles.*" => ["required", Rule::in(["manage", "purchase", "sale"])],
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

        $branch = Branch::create([
            "name" => $data["name"],
            "address" => $data["address"],
            "store_id" => $store_id,
            "image" => "branches/default.jpg",
            "image_key" => Str::uuid(),
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
                $this->transferEmployee($employee["id"], $branch["id"], $employee["roles"]);
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
        $branch = Branch::where("image_key", $image_key)->first();
        if (!$branch) {
            return response()->json(
                [
                    "message" => "Branch not found.",
                ],
                404
            );
        }

        $file_path = $branch->image;
        if (!Storage::exists($file_path)) {
            return response()->json(
                [
                    "message" => "Branch image not found.",
                ],
                404
            );
        }
        return response()->file(storage_path("app" . DIRECTORY_SEPARATOR . $file_path));
    }

    /**
     * @OA\Put(
     *   path="/branch/{branch_id}/image",
     *   operationId="updateBranchImage",
     *   tags={"Branch"},
     *   summary="Update branch image",
     *   @OA\Parameter(
     *     name="branch_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="image",
     *           type="file",
     *         ),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Image updated successfully",
     *     @OA\MediaType(
     *       mediaType="application/json",
     *       @OA\Schema(
     *         @OA\Property(property="message", type="string"),
     *         @OA\Property(property="image", type="string"),
     *       ),
     *     ),
     *   ),
     * )
     */
    public function updateBranchImage(Request $request, $branch_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $data["branch_id"] = $branch_id;
        $rules = [
            "branch_id" => ["required", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "image" => ["required", "image", "max:2048"],
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

        $branch = Branch::find($data["branch_id"]);
        $image_name = $store_id . Str::uuid();
        $path = $request
            ->file("image")
            ->storeAs(
                "images/" . $store_id . "/branches",
                $image_name . "." . $request->file("image")->getClientOriginalExtension()
            );

        $branch->image = $path;
        $branch->image_key = $image_name;
        $branch->save();
        return response()->json([
            "message" => "Branch image updated.",
            "image" => $branch->image,
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
            ->where(function ($query) use ($search) {
                $query->where("name", "iLike", "%" . $search . "%")->orWhere("address", "iLike", "%" . $search . "%");
            })
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($branches);
    }

    /**
     * @OA\Get(
     *   path="/branch/{branch_id}",
     *   operationId="getBranch",
     *   tags={"Branch"},
     *   summary="Get branch",
     *   @OA\Parameter(
     *     name="branch_id",
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
    public function getBranch($branch_id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $branch = Branch::with([
            "employments" => function ($query) {
                $query->where("to", null)->with("employee");
            },
        ])
            ->where("store_id", $store_id)
            ->where("id", $branch_id)
            ->first();
        if (!$branch) {
            return response()->json(
                [
                    "message" => "Branch not found.",
                ],
                404
            );
        }

        return response()->json($branch);
    }

    /**
     * @OA\Put(
     *   path="/branch/{branch_id}",
     *   operationId="updateBranch",
     *   tags={"Branch"},
     *   summary="Update branch",
     *   @OA\Parameter(name="branch_id", in="path", required=true),
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
    public function update(Request $request, $branch_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $data["branch_id"] = $branch_id;
        $rules = [
            "branch_id" => ["required", "integer", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "name" => ["nullable", "string", "max:255"],
            "address" => ["nullable", "string", "max:1000"],
            "image" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed.",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        $branch = Branch::where("store_id", $store_id)
            ->where("id", $branch_id)
            ->first();

        $has_image = $request->hasFile("image");

        // delete old image if new image is uploaded
        if ($has_image) {
            $old_image = $branch->image;
            if ($old_image != "branches/default.jpg") {
                Storage::delete($old_image);
            }
            // change image key when new image is uploaded
            $branch->image_key = Str::uuid();
        }

        $image_name = $store_id . Str::uuid();
        $path = $has_image
            ? $request
                ->file("image")
                ->storeAs("branches", $image_name . "." . $request->file("image")->getClientOriginalExtension())
            : $branch->image;

        $branch->name = $data["name"] ?? $branch->name;
        $branch->address = $data["address"] ?? $branch->address;
        $branch->image = $path;
        $branch->save();

        return response()->json($branch);
    }

    /**
     * @OA\Delete(
     *   path="/branch/{branch_id}",
     *   operationId="deleteBranch",
     *   tags={"Branch"},
     *   summary="Delete branch",
     *   @OA\Parameter(name="branch_id", in="path", required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Branch deleted successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Branch")
     *   )
     * )
     */
    public function delete(Request $request, $branch_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        // make sure store owns branch
        $branch = Branch::where("store_id", $store_id)
            ->where("id", $branch_id)
            ->first();
        if (!$branch) {
            return response()->json(
                [
                    "message" => "Branch not found.",
                ],
                404
            );
        }

        $employments = Employment::where("branch_id", $branch_id)
            ->where("to", null)
            ->get();
        // if employments is not empty, return error
        if ($employments->isNotEmpty()) {
            return response()->json(
                [
                    "message" => "Còn nhân viên làm việc tại chi nhánh này.",
                ],
                400
            );
        }

        $terminated_employments = Employment::where("branch_id", $branch_id)
            ->where("to", "!=", null)
            ->get();
        // delete all terminated employments roles and employment
        foreach ($terminated_employments as $employment) {
            $employment->roles()->delete();
            $employment->delete();
        }

        $branch->delete();

        return response()->json($branch);
    }
}
