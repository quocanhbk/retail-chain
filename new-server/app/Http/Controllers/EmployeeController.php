<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRole;
use App\Traits\EmployeeTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
     *     @OA\JsonContent(ref="#/components/schemas/CreateEmployeeInput")
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
            "password" => ["required", "string", "min:6", "confirmed"],
            "branch_id" => ["required", "integer", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "roles" => ["required", "array", Rule::in(["manage", "purchase", "sale"]), "min:1"],
            "phone" => ["nullable", "string", "max:255"],
            "avatar" => ["nullable", "image", "mimes:jpeg,png,jpg", "max:2048"],
            "birthday" => ["nullable", "date"],
            "gender" => ["nullable", "string"],
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

        $avatar_exist = $request->hasFile("avatar");

        $employee = $this->createEmployee($store_id, $data, $avatar_exist ? $request->file("avatar") : null);

        return response()->json($employee);
    }

    /**
     * @OA\Post(
     *   path="/employee/many",
     *   tags={"Employee"},
     *   summary="Create many new employees",
     *   operationId="createManyEmployees",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/CreateManyEmployeesInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *     )
     *   )
     * )
     */
    public function createMany(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $rules = [
            "employees" => ["required", "array"],
            "employees.*.branch_id" => [
                "required",
                "integer",
                Rule::exists("branches", "id")->where("store_id", $store_id),
            ],
            "employees.*.name" => ["required", "string", "max:255"],
            "employees.*.email" => [
                "required",
                "string",
                "email",
                "max:255",
                Rule::unique("employees")->where("store_id", $store_id),
            ],
            "employees.*.password" => ["required", "string", "min:6", "confirmed"],
            "employees.*.roles" => ["required", "array", Rule::in(["manage", "purchase", "sale"]), "min:1"],
            "employees.*.phone" => ["nullable", "string", "max:255"],
            "employees.*.birthday" => ["nullable", "date"],
            "employees.*.gender" => ["nullable", "string"],
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

        foreach ($data["employees"] as $employee_data) {
            $this->createEmployee($store_id, $employee_data);
        }

        return response()->json([
            "message" => "Employees created successfully.",
        ]);
    }

    /**
     * @OA\Put(
     *   path="/employee/{employee_id}/avatar",
     *   tags={"Employee"},
     *   summary="Update employee avatar",
     *   operationId="updateEmployeeAvatar",
     *   @OA\Parameter(
     *     name="employee_id",
     *     in="path",
     *     required=true,
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *       mediaType="multipart/form-data",
     *       @OA\Schema(
     *         @OA\Property(
     *           property="avatar",
     *           type="file",
     *         ),
     *       ),
     *     ),
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string"),
     *     )
     *   )
     * )
     */
    public function updateAvatar(Request $request, $employee_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $data["employee_id"] = $employee_id;
        $rules = [
            "employee_id" => ["required", "integer", Rule::exists("employees", "id")->where("store_id", $store_id)],
            "avatar" => ["required", "image", "mimes:jpeg,png,jpg", "max:2048"],
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

        $employee = Employee::find($employee_id);
        $avatar_image_path = $request
            ->file("avatar")
            ->storeAs(
                "employees",
                $store_id . Str::uuid() . "." . $request->file("avatar")->getClientOriginalExtension()
            );

        $employee->avatar = $avatar_image_path;
        $employee->avatar_key = Str::uuid();
        $employee->save();

        return response()->json([
            "message" => "Avatar updated successfully.",
        ]);
    }

    /**
     * @OA\Put(
     *   path="/employee/{employee_id}",
     *   tags={"Employee"},
     *   summary="Update employee",
     *   operationId="updateEmployee",
     *   @OA\Parameter(
     *     name="employee_id",
     *     in="path",
     *     required=true,
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/UpdateEmployeeInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Successful operation",
     *     @OA\JsonContent(ref="#/components/schemas/Employee")
     *   )
     * )
     */
    public function update(Request $request, $employee_id)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $data["employee_id"] = $employee_id;
        $rules = [
            "employee_id" => ["required", "integer", Rule::exists("employees", "id")->where("store_id", $store_id)],
            "branch_id" => ["nullable", "integer", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "name" => ["nullable", "string", "max:255"],
            "email" => [
                "nullable",
                "string",
                "email",
                "max:255",
                Rule::unique("employees")
                    ->where("store_id", $store_id)
                    ->ignore($employee_id),
            ],
            "roles" => ["nullable", "array"],
            "phone" => ["nullable", "string", "max:255"],
            "birthday" => ["nullable", "date"],
            "gender" => ["nullable", "string"],
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

        $employee = Employee::find($employee_id);
        $employee->name = $data["name"] ?? $employee->name;
        $employee->email = $data["email"] ?? $employee->email;
        $employee->phone = $data["phone"] ?? $employee->phone;
        $employee->birthday = $data["birthday"] ?? $employee->birthday;
        $employee->gender = $data["gender"] ?? $employee->gender;

        $has_avatar = $request->hasFile("avatar");
        // if there is avatar, update and delete old avatar
        if ($has_avatar) {
            $old_avatar = $employee->avatar;
            if ($old_avatar != null) {
                Storage::delete($old_avatar);
            }
            $employee->avatar_key = Str::uuid();
        }
        $avatar_image_path = $has_avatar
            ? $request
                ->file("avatar")
                ->storeAs(
                    "employees",
                    $store_id . Str::uuid() . "." . $request->file("avatar")->getClientOriginalExtension()
                )
            : $employee->avatar;
        $employee->avatar = $avatar_image_path;

        // update employment roles
        if (
            isset($data["roles"]) &&
            !(isset($data["branch_id"]) && $data["branch_id"] != $employee->employment->branch_id)
        ) {
            $employment = Employment::where("employee_id", $employee_id)->first();
            EmploymentRole::where("employment_id", $employment->id)->delete();
            foreach ($data["roles"] as $role) {
                EmploymentRole::create([
                    "employment_id" => $employment->id,
                    "role" => $role,
                ]);
            }
        }

        // if branch_id is provided, changed employee working branch
        if (isset($data["branch_id"]) && $data["branch_id"] != $employee->employment->branch_id) {
            // terminate old employment, start new employment
            $old_employment = $employee->employment;
            $old_employment->to = date("Y/m/d");
            $old_employment->save();

            $new_employment = Employment::create([
                "employee_id" => $employee->id,
                "branch_id" => $data["branch_id"],
                "from" => date("Y/m/d"),
            ]);

            $roles = isset($data["roles"])
                ? $data["roles"]
                : $old_employment
                    ->roles()
                    ->pluck("role")
                    ->toArray();

            foreach ($roles as $role) {
                EmploymentRole::create([
                    "employment_id" => $new_employment->id,
                    "role" => $role,
                ]);
            }
        }

        $employee->save();

        return response()->json($employee);
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
            return response()->json(
                [
                    "message" => "Avatar not found.",
                ],
                404
            );
        }

        $avatar = $employee->avatar;
        if (!Storage::exists($avatar)) {
            return response()->json(
                [
                    "message" => "Avatar not found.",
                ],
                404
            );
        }

        return response()->file(storage_path("app" . DIRECTORY_SEPARATOR . $avatar));
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
    public function getEmployees(Request $request)
    {
        [$search, $from, $to, $order_by, $order_type] = $this->getQuery($request);

        $store_id = Auth::guard("stores")->user()->id;
        $employees = Employee::with("employment.roles")
            ->where("store_id", $store_id)
            ->where(function ($query) use ($search) {
                $query
                    ->where("name", "iLike", "%" . $search . "%")
                    ->orWhere("email", "iLike", "%" . $search . "%")
                    ->orWhere("phone", "iLike", "%" . $search . "%");
            })
            ->orderBy($order_by, $order_type)
            ->skip($from)
            ->take($to - $from)
            ->get();

        return response()->json($employees);
    }

    /**
     * @OA\Get(
     *   path="/employee/{employee_id}",
     *   tags={"Employee"},
     *   summary="Get employee",
     *   operationId="getEmployee",
     *   @OA\Parameter(name="employee_id", in="path", @OA\Schema(type="integer"), required=true),
     *   @OA\Response(
     *     response=200,
     *     description="Employee retrieved successfully",
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeWithEmployment")
     *   )
     * )
     */
    public function getEmployee($employee_id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        // get employee
        $employee = Employee::with("employment.roles")
            ->where("store_id", $store_id)
            ->where("id", $employee_id)
            ->first();

        if (!$employee) {
            return response()->json(
                [
                    "message" => "Employee not found.",
                ],
                404
            );
        }

        return response()->json($employee);
    }

    /**
     * @OA\Get(
     *   path="/employee/branch/{branch_id}",
     *   tags={"Employee"},
     *   summary="Get employees by branch",
     *   operationId="getEmployeesByBranch",
     *   @OA\Parameter(name="branch_id", in="path", @OA\Schema(type="integer"), required=true),
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
    public function getEmployeesByBranchId($branch_id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        $employees = Employee::with("employment.roles")
            ->where("store_id", $store_id)
            ->whereRelation("employment", "branch_id", $branch_id)
            ->get();
        return response()->json($employees);
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

        $mesages = [
            "email.required" => "Email là bắt buộc",
            "email.email" => "Email không hợp lệ",
            "email.max" => "Email không hợp lệ",
            "password.required" => "Mật khẩu là bắt buộc",
            "password.min" => "Mật khẩu phải có ít nhất 6 ký tự",
        ];

        $validator = Validator::make($data, $rules, $mesages);

        if ($validator->failed()) {
            return response()->json(
                [
                    "message" => $this->formatValidationError($validator->errors()),
                ],
                400
            );
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
            $employee = Employee::where("email", $data["email"])->first();
            $message = $employee ? "Mật khẩu không hợp lệ" : "Tài khoảng không tồn tại";
            return response()->json(
                [
                    "message" => $message,
                ],
                401
            );
        }

        $id = Auth::guard("employees")->user()->id;
        $employee = Employee::with("employment.roles")
            ->where("id", $id)
            ->first();

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
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", description="Logout successfully")
     *     )
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
     *     @OA\JsonContent(ref="#/components/schemas/EmployeeWithEmployment")
     *   )
     * )
     */
    public function me()
    {
        $id = Auth::guard("employees")->user()->id;
        $employee = Employee::with("employment.roles")
            ->where("id", $id)
            ->first();
        return response()->json($employee);
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
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", description="Employee transferred successfully")
     *     )
     *   )
     * )
     */
    public function transfer(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $rules = [
            "employee_id" => ["required", Rule::exists("employees", "id")->where("store_id", $store_id)],
            "branch_id" => ["required", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "roles" => ["required", "array", "min:1", Rule::in(["manage", "purchase", "sale"])],
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

        $this->transferEmployee($data["employee_id"], $data["branch_id"], $data["roles"]);

        return response()->json([
            "message" => "Employee transferred.",
        ]);
    }

    /**
     * @OA\Post(
     *   path="/employee/transfer/many",
     *   tags={"Employee"},
     *   summary="Transfer many employees",
     *   operationId="transferManyEmployees",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(ref="#/components/schemas/TransferManyEmployeesInput")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Employees transferred successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", description="Employees transferred successfully")
     *     )
     *   )
     * )
     */
    public function transferMany(Request $request)
    {
        $store_id = Auth::guard("stores")->user()->id;
        $data = $request->all();
        $rule = [
            "branch_id" => ["required", Rule::exists("branches", "id")->where("store_id", $store_id)],
            "employees" => ["required", "array"],
            "employees.*.id" => [
                "required",
                Rule::exists("employees")->where("store_id", $store_id),
                Rule::unique("employments", "employee_id")
                    ->where("branch_id", $data["branch_id"])
                    ->where("to", null),
            ],
            "employees.*.roles" => ["required", "array", "min:1", Rule::in(["manage", "purchase", "sale"])],
        ];

        $validator = Validator::make($data, $rule);
        if ($validator->fails()) {
            return response()->json(
                [
                    "message" => "Validation failed.",
                    "errors" => $validator->errors(),
                ],
                400
            );
        }

        foreach ($data["employees"] as $employee) {
            $this->transferEmployee($employee["id"], $data["branch_id"], $employee["roles"]);
        }

        return response()->json([
            "message" => "Employees transferred.",
        ]);
    }

    /**
     * @OA\Delete(
     *   path="/employee/{employee_id}",
     *   tags={"Employee"},
     *   summary="Delete employee",
     *   operationId="deleteEmployee",
     *   @OA\Parameter(name="employee_id", in="path", description="Employee ID", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(
     *     response=200,
     *     description="Employee deleted successfully",
     *     @OA\JsonContent(
     *       @OA\Property(property="message", type="string", description="Employee deleted successfully")
     *     )
     *   )
     * )
     */
    public function delete(Request $request, $id)
    {
        $store_id = Auth::guard("stores")->user()->id;

        // get employee
        $employee = Employee::where("store_id", $store_id)
            ->where("id", $id)
            ->first();
        if (!$employee) {
            return response()->json(
                [
                    "message" => "Employee not found.",
                ],
                404
            );
        }

        $employee->delete();

        // terminate employment
        $employment = Employment::where("employee_id", $id)
            ->where("to", null)
            ->first();
        if ($employment) {
            $employment->to = date("Y/m/d");
            $employment->save();
        }

        return response()->json([
            "message" => "Employee deleted.",
        ]);
    }
}
