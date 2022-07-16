<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Traits\EmployeeTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    use EmployeeTrait;

    /**
     * @OA\Post(
     *   path="/employee",
     *   tags={"Employee"},
     *   summary="Create a new employee",
     *   operationId="createEmployee",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/CreateEmployeeInput")
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Employee")
     *   )
     * )
     */
    public function create(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $rules = [
            "name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("employees")->where("store_id", $store_id),
            ],
            "branch_id" => ["required", "integer", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "role_ids" => ["required", "array", "min:1"],
            "role_ids.*" => ["required", "integer", Rule::exists("roles", "id")->where("store_id", $store_id)],
            "phone" => ["nullable", "string", "max:255"],
            "avatar" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
            "birthday" => ["nullable", "date", "date_format:Y-m-d"],
            "gender" => ["nullable", "string"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $avatar_exist = $request->hasFile("avatar");

        $employee = $this->createEmployee($store_id, $data, $avatar_exist ? $request->file("avatar") : null);

        return response()->json($employee);
    }

    /**
     * @OA\Put(
     *   path="/employee/{id}",
     *   tags={"Employee"},
     *   summary="Update employee",
     *   operationId="updateEmployee",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(ref="#/components/schemas/UpdateEmployeeInput")
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
        $store_id = Auth::guard("stores")->user()->id;

        $data = $request->all();

        $employee = Employee::where("store_id", $store_id)->find($id);

        if (!$employee) {
            return response()->json(["message" => "Employee not found"], 404);
        }

        $rules = [
            "name" => ["nullable", "string", "max:255"],
            "email" => [
                "nullable",
                "string",
                "email",
                "max:255",
                Rule::unique("employees")
                    ->where("store_id", $store_id)
                    ->ignore($id),
            ],
            "role_ids" => ["nullable", "array", "min:1"],
            "role_ids.*" => ["nullable", "integer", Rule::exists("roles", "id")->where("store_id", $store_id)],
            "phone" => ["nullable", "string", "max:255"],
            "birthday" => ["nullable", "date"],
            "gender" => ["nullable", "string"],
            "avatar" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $employee = Employee::find($id);
        $employee->name = $data["name"] ?? $employee->name;
        $employee->email = $data["email"] ?? $employee->email;
        $employee->phone = $data["phone"] ?? $employee->phone;
        $employee->birthday = $data["birthday"] ?? $employee->birthday;
        $employee->gender = $data["gender"] ?? $employee->gender;

        $has_avatar = $request->hasFile("avatar");
        // if there is avatar, update and delete old avatar
        if ($has_avatar) {
            $old_avatar = $employee->avatar;
            if (null != $old_avatar) {
                Storage::delete($old_avatar);
            }
            $employee->avatar_key = Str::uuid();

            $avatar_image_path = $request
                ->file("avatar")
                ->storeAs(
                    "images/{$store_id}/employees",
                    $store_id . Str::uuid() . "." . $request->file("avatar")->getClientOriginalExtension()
                );
            $employee->avatar = $avatar_image_path;
        }

        // update employment roles
        if (isset($data["role_ids"])) {
            $old_employment = $employee->employment;

            $old_employment->to = date("Y/m/d");
            $old_employment->save();

            $new_employment = Employment::create([
                "branch_id" => $old_employment->branch_id,
                "employee_id" => $old_employment->id,
                "from" => date("Y/m/d"),
            ]);

            foreach ($data["role_ids"] as $role_id) {
                EmploymentRole::create([
                    "employment_id" => $new_employment->id,
                    "role_id" => $role_id,
                ]);
            }
        }

        $employee->save();

        return response()->json(["message" => "Employee updated successfully"]);
    }

    /**
     * @OA\Put(
     *   path="/employee/password",
     *   tags={"Employee"},
     *   summary="Update employee password",
     *   operationId="changeEmployeePassword",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"current_password", "new_password", "new_password_confirmation"},
     *       @OA\Property(property="current_password", type="string", format="password"),
     *       @OA\Property(property="new_password", type="string", format="password"),
     *       @OA\Property(property="new_password_confirmation", type="string", format="password"),
     *     )
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   ),
     * )
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->all();

        $rules = [
            "current_password" => ["required", "string", "current_password"],
            "new_password" => ["required", "string", "min:6", "confirmed"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $user->update(["password" => Hash::make($data["new_password"])]);

        return response()->json(["message" => "Password changed successfully."]);
    }

    /**
     * @OA\Get(
     *   path="/employee/avatar/{avatar_key}",
     *   tags={"Employee"},
     *   summary="Get employee avatar",
     *   operationId="getEmployeeAvatar",
     *   @OA\Parameter(
     *     name="avatar_key",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Image retrieved successfully",
     *     @OA\MediaType(mediaType="image/*")
     *   ),
     * )
     */
    public function getAvatar($avatar_key)
    {
        $employee = Employee::where("avatar_key", $avatar_key)->first();

        if (!$employee) {
            return response()->json(["message" => "Avatar not found."], 404);
        }

        $avatar = $employee->avatar;
        if (!Storage::exists($avatar)) {
            return response()->json(["message" => "Avatar not found."], 404);
        }

        return response()->file(storage_path("app" . DIRECTORY_SEPARATOR . $avatar), [
            "Content-Type" => "image/*",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employee",
     *   tags={"Employee"},
     *   summary="Get employees",
     *   operationId="getEmployees",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="branch_id", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Employees retrieved successfully",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/EmployeeWithEmployment")
     *     )
     *   )
     * )
     */
    public function getMany(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $employees = Employee::with("employment.roles.role")
            ->where("store_id", $store_id)
            ->when(
                $request->query("branch_id"),
                fn($query) => $query->whereRelation("employment", "branch_id", $request->query("branch_id"))
            )
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($employees);
    }

    /**
     * @OA\Get(
     *   path="/employee/{id}",
     *   tags={"Employee"},
     *   summary="Get employee",
     *   operationId="getEmployee",
     *   @OA\Parameter(name="id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Employee retrieved successfully",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeWithEmployment")
     *   )
     * )
     */
    public function getOne($id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        // get employee
        $employee = Employee::with("employment.roles.role")
            ->where("store_id", $store_id)
            ->where("id", $id)
            ->first();

        if (!$employee) {
            return response()->json(["message" => "Employee not found."], 404);
        }

        return response()->json($employee);
    }

    /**
     * @OA\Post(
     *   path="/employee/login",
     *   tags={"Employee"},
     *   summary="Login as employee",
     *   operationId="loginEmployee",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeLoginInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Employee logged in successfully",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeWithEmployment")
     *   )
     * )
     */
    public function login(Request $request)
    {
        $data = $request->all();
        $rules = [
            "email" => ["required", "string", "email", "max:255"],
            "password" => ["required", "string", "min:6"],
            "remember" => ["boolean", "nullable"],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->failed()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        $remember = $request->input("remember") ? true : false;

        $check = Auth::guard("employees")->attempt(
            [
                "email" => $data["email"],
                "password" => $data["password"],
            ],
            $remember
        );

        if (!$check) {
            return response()->json(["message" => "Invalid credentials"], 401);
        }

        $id = Auth::guard("employees")->user()->id;

        $employee = Employee::with("employment.roles.role")->find($id);

        return response()->json($employee);
    }

    /**
     * @OA\Post(
     *   path="/employee/logout",
     *   tags={"Employee"},
     *   summary="Logout as employee",
     *   operationId="logoutEmployee",
     *   @OA\Response(
     *     response=200,
     *     description="Employee logged out successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function logout(Request $request)
    {
        Auth::guard("employees")->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            "message" => "Logged out.",
        ]);
    }

    /**
     * @OA\Get(
     *   path="/employee/me",
     *   tags={"Employee"},
     *   summary="Get employee information",
     *   operationId="getCurrentEmployee",
     *   @OA\Response(
     *     response=200,
     *     description="Employee retrieved successfully",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeWithEmploymentAndPermissions")
     *   )
     * )
     */
    public function me()
    {
        $id = Auth::guard("employees")->user()->id;

        $employee = Employee::with("employment.roles.role")->find($id);

        $permissions = Permission::whereIn(
            "id",
            PermissionRole::whereIn("role_id", $employee->employment->roles->pluck("id"))->pluck("permission_id")
        )->get();

        return response()->json([...$employee->toArray(), "permissions" => $permissions]);
    }

    /**
     * @OA\Post(
     *   path="/employee/transfer",
     *   tags={"Employee"},
     *   summary="Transfer employee",
     *   operationId="transferEmployee",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/TransferEmployeeInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Employee transferred successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function transfer(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $rules = [
            "branch_id" => ["required", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "employees" => ["required", "array", "min:1"],
            "employees.*.id" => [
                "required",
                Rule::exists("employees", "id")->where("store_id", $store_id),
                Rule::exists("employments", "employee_id")->where("to", null),
            ],
            "employees.*.role_ids" => ["required", "array", "min:1"],
            "employees.*.role_ids.*" => ["required", Rule::exists("roles", "id")->where("store_id", $store_id)],
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json(["message" => $this->formatValidationError($validator->errors())], 400);
        }

        foreach ($data["employees"] as $employee) {
            $this->transferEmployee($employee["id"], $data["branch_id"], $employee["role_ids"]);
        }

        return response()->json(["message" => "Employees transferred successfully."]);
    }

    /**
     * @OA\Delete(
     *   path="/employee/{id}",
     *   tags={"Employee"},
     *   summary="Delete employee",
     *   operationId="deleteEmployee",
     *   @OA\Parameter(name="id", in="path", description="Employee ID", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="force", in="query", description="Force delete", required=false, @OA\Schema(type="boolean")),
     *   @OA\Response(
     *     response=200,
     *     description="Employee deleted successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $force = $request->query("force") ? true : false;

        // get employee
        $employee = Employee::where("store_id", $store_id)->find($id);

        if (!$employee) {
            return response()->json(["message" => "Employee not found."], 404);
        }

        if ($force) {
            $employee->forceDelete();
        } else {
            $employee->delete();

            $employee->employment()->update([
                "to" => date("Y/m/d"),
            ]);
        }

        return response()->json(["message" => "Employee deleted."]);
    }

    /**
     * @OA\Get(
     *   path="/employee/deleted",
     *   tags={"Employee"},
     *   summary="Get deleted employees",
     *   operationId="getDeletedEmployees",
     *   @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="order_type", in="query", @OA\Schema(type="string", enum={"asc", "desc"})),
     *   @OA\Parameter(name="from", in="query", @OA\Schema(type="integer")),
     *   @OA\Parameter(name="to", in="query", @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Employees retrieved successfully",
     *     @OA\JsonContent(
     *       type="array",
     *       @OA\Items(ref="#/components/schemas/Employee")
     *     )
     *   )
     * )
     */
    public function getDeleted(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;

        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $employees = Employee::onlyTrashed()
            ->where("store_id", $store_id)
            ->where(
                fn($query) => $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%")
            )
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($employees);
    }

    /**
     * @OA\Post(
     *   path="/employee/{id}/restore",
     *   tags={"Employee"},
     *   summary="Restore employee",
     *   operationId="restoreEmployee",
     *   @OA\Parameter(name="id", in="path", description="Employee ID", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Employee restored successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function restore($id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        // get employee
        $employee = Employee::onlyTrashed()
            ->where("store_id", $store_id)
            ->find($id);

        if (!$employee) {
            return response()->json(["message" => "Employee not found."], 404);
        }

        $employee->restore();

        $employee
            ->employments()
            ->latest()
            ->first()
            ->update([
                "to" => null,
            ]);

        return response()->json(["message" => "Employee restored."]);
    }

    /**
     * @OA\Delete(
     *   path="/employee/{id}/force",
     *   tags={"Employee"},
     *   summary="Force delete employee",
     *   operationId="forceDeleteEmployee",
     *   @OA\Parameter(name="id", in="path", description="Employee ID", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Employee deleted successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Message")
     *   )
     * )
     */
    public function forceDelete($id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        // get employee
        $employee = Employee::withTrashed()
            ->where("store_id", $store_id)
            ->find($id);

        if (!$employee) {
            return response()->json(["message" => "Employee not found."], 404);
        }

        $employee->forceDelete();

        return response()->json(["message" => "Employee deleted."]);
    }
}
