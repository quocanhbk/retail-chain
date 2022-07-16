<?php

namespace App\Http\Controllers;

use App\Models\EmploymentRole;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * @OA\Post(
     *   path="/role",
     *   summary="Create a new role",
     *   tags={"Role"},
     *   operationId="createRole",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateRoleInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successfull operation",
     *     @OA\JsonContent(ref="#/components/schemas/Role")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $store = Auth::user();

        $data = $request->all();

        $rules = [
            "name" => ["required", "string", "max:32"],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $role = $store->roles()->create($validator->validated());

        return response()->json($role);
    }

    /**
     * @OA\Get(
     *   path="/role",
     *   summary="Get roles",
     *   tags={"Role"},
     *   operationId="getRoles",
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
     *       @OA\Items(ref="#/components/schemas/Role")
     *     )
     *   )
     * )
     */
    public function getRoles(Request $request)
    {
        $store = Auth::user();

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $roles = Role::where("store_id", $store->id)
            ->where(
                fn($query) => $query->where("name", "iLike", "%$search%")->orWhere("description", "iLike", "%$search%")
            )
            ->orderBy($order_by, $order_type)
            ->offset($from)
            ->limit($to - $from)
            ->get();

        return response()->json($roles);
    }

    /**
     * @OA\Get(
     *   path="/role/{id}",
     *   summary="Get role by id",
     *   tags={"Role"},
     *   operationId="getRole",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Role")
     *   )
     * )
     */
    public function getRole($id)
    {
        $store = Auth::user();

        $role = Role::where("store_id", $store->id)->find($id);

        if (!$role) {
            return response()->json(["message" => "Role not found"], 404);
        }

        return response()->json($role);
    }

    /**
     * @OA\Put(
     *   path="/role/{id}",
     *   summary="Update role",
     *   tags={"Role"},
     *   operationId="updateRole",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpsertRoleInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Role")
     *   )
     * )
     */
    public function update(Request $request, $id)
    {
        $store = Auth::user();

        $role = Role::where("store_id", $store->id)->find($id);

        if (!$role) {
            return response()->json(["message" => "Role not found"], 404);
        }

        $data = $request->all();

        $rules = [
            "name" => ["nullable", "string", "max:32"],
            "description" => ["nullable", "string", "max:255"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $role->name = $data["name"] ?? $role->name;
        $role->description = $data["description"] ?? $role->description;

        $role->save();

        return response()->json($role);
    }

    /**
     * @OA\Delete(
     *   path="/role/{id}",
     *   summary="Delete role",
     *   tags={"Role"},
     *   operationId="deleteRole",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete($id)
    {
        $store = Auth::user();

        $role = Role::where("store_id", $store->id)->find($id);

        if (!$role) {
            return response()->json(["message" => "Role not found"], 404);
        }

        // return error when role is still in use
        $active_employment_roles = EmploymentRole::whereHas(
            "employment",
            fn($query) => $query->where("to", null)->whereRelation("branch", "store_id", $store->id)
        )
            ->where("role_id", $role->id)
            ->get();

        if (count($active_employment_roles) > 0) {
            return response()->json(["message" => "Role is still in use"], 400);
        }

        $role->delete();

        return response()->json(["message" => "Role deleted"]);
    }
}
