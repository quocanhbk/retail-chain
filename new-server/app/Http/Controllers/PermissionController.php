<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * @OA\Put(
     *   path="/permission/{id}",
     *   tags={"Permission"},
     *   summary="Update a permission",
     *   description="Update a permission",
     *   operationId="updatePermission",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"role_ids"},
     *       @OA\Property(property="role_ids", type="array", @OA\Items(type="integer")),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function update(Request $request, $id)
    {
        $store = Auth::user();

        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json(["message" => "Permission not found"], 404);
        }

        $data = $request->all();

        $rules = [
            "role_ids" => ["required", "array", "min:1", Rule::exists("roles", "id")->where("store_id", $store->id)],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        PermissionRole::where(["store_id" => $store->id, "permission_id" => $permission->id])->delete();

        foreach ($data["role_ids"] as $role_id) {
            PermissionRole::create([
                "store_id" => $store->id,
                "permission_id" => $permission->id,
                "role_id" => $role_id,
            ]);
        }

        return response()->json(["message" => "Permission updated"]);
    }
}