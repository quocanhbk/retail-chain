<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRoles;
use App\Traits\EmployeeTrait;
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

    // Create a new employee
    public function create(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees')->where('store_id', $store_id)],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'roles' => ['required', 'array'],
            'phone' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'string']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $avatar_exist = $request->hasFile('avatar');

        $employee = $this->createEmployee($store_id, $data, $avatar_exist ? $request->file('avatar') : null);

        return response()->json($employee);
    }

    // Create many employees
    public function createMany(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'employees' => ['required', 'array'],
            'employees.*.branch_id' => ['required', 'integer', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'employees.*.name' => ['required', 'string', 'max:255'],
            'employees.*.email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees')->where('store_id', $store_id)],
            'employees.*.password' => ['required', 'string', 'min:6', 'confirmed'],
            'employees.*.roles' => ['required', 'array'],
            'employees.*.phone' => ['nullable', 'string', 'max:255'],
            'employees.*.birthday' => ['nullable', 'date'],
            'employees.*.gender' => ['nullable', 'string']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        foreach ($data['employees'] as $employee_data) {
            $this->createEmployee($store_id, $employee_data);
        }

        return response()->json([
            'message' => 'Employees created successfully.'
        ]);

    }

    // Update employee's avatar
    public function updateAvatar(Request $request, $employee_id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $data['employee_id'] = $employee_id;
        $rules = [
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('store_id', $store_id)],
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $employee = Employee::find($employee_id);
        $avatar_image_path = $request->file('avatar')->storeAs('employees', $store_id . Str::uuid() . "." . $request->file('avatar')->getClientOriginalExtension());

        $employee->avatar = $avatar_image_path;
        $employee->avatar_key = Str::uuid();
        $employee->save();

        return response()->json([
            'message' => 'Avatar updated successfully.'
        ]);
    }

    // Update an employee
    public function update(Request $request, $id) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $data['id'] = $id;
        error_log(json_encode($data));
        $rules = [
            'id' => ['required', 'integer', Rule::exists('employees', 'id')->where('store_id', $store_id)],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('employees')->where('store_id', $store_id)->ignore($id)],
            'roles' => ['nullable', 'array'],
            'phone' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
            'gender' => ['nullable', 'string']
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $employee = Employee::find($id);
        $employee->name = $data['name'] ?? $employee->name;
        $employee->email = $data['email'] ?? $employee->email;
        $employee->phone = $data['phone'] ?? $employee->phone;
        $employee->birthday = $data['birthday'] ?? $employee->birthday;
        $employee->gender = $data['gender'] ?? $employee->gender;

        $has_avatar = $request->hasFile('avatar');
        // if there is avatar, update and delete old avatar
        if ($has_avatar) {
            $old_avatar = $employee->avatar;
            if ($old_avatar != null) {
                Storage::delete($old_avatar);
            }
            $employee->avatar_key = Str::uuid();
        }
        $avatar_image_path = $has_avatar ?
            $request->file('avatar')->storeAs('employees', $store_id . Str::uuid() . "." . $request->file('avatar')->getClientOriginalExtension()) : $employee->avatar;
        $employee->avatar = $avatar_image_path;

        // update employment roles
        if (isset($data['roles'])) {
            $employment = Employment::where('employee_id', $id)->first();
            EmploymentRoles::where('employment_id', $employment->id)->delete();
            foreach ($data['roles'] as $role) {
                EmploymentRoles::create([
                    'employment_id' => $employment->id,
                    'role' => $role
                ]);
            }
        }

        // if branch_id is provided, changed employee working branch
        if (isset($data['branch_id']) && $data['branch_id'] != $employee->employment->branch_id) {
            // terminate old employment, start new employment
            $old_employment = $employee->employment;
            $old_employment->to = date("Y/m/d");
            $old_employment->save();
            Employment::create([
                'employee_id' => $employee->id,
                'branch_id' => $data['branch_id'],
                'from' => date("Y/m/d")
            ]);
        }

        $employee->save();

        return response()->json($employee);

    }

    // Get avatar
    public function getAvatar($avatar_key) {
        $employee = Employee::where('avatar_key', $avatar_key)->first();

        if (!$employee) {
            return response()->json([
                'message' => 'Avatar not found.'
            ], 404);
        }

        $avatar = $employee->avatar;
        if (!Storage::exists($avatar)) {
            return response()->json([
                'message' => 'File not found.',
            ], 404);
        }

        return response()->file(storage_path('app' . DIRECTORY_SEPARATOR . $avatar));
    }

    // Get all employees
    public function getEmployees(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $employees = Employee::with('employment.roles')->where('store_id', $store_id)->get();

        return response()->json($employees);
    }

    // Get an employee
    public function getEmployee(Request $request, $id) {
        $store_id = Auth::guard('stores')->user()->id;

        // get employee
        $employee = Employee::where('store_id', $store_id)->where('id', $id)->first();
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        $employee = Employee::with('employment.roles')->where('store_id', $store_id)->where('id', $id)->first();

        return response()->json($employee);
    }

    public function getEmployeesByBranchId(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;

        $employees = Employee::with('employment.roles')->where('store_id', $store_id)->whereRelation('employment', 'branch_id', $branch_id)->get();
        return response()->json($employees);
    }

    // Login as an employee
    public function login(Request $request) {
        $data = $request->all();
        $rules = [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'remember' => ['boolean', 'nullable']
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->failed()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $remember = $request->input('remember') ? true : false;
        $check = Auth::guard('employees')->attempt([
            'email' => $data['email'],
            'password' => $data['password'],
        ], $remember);

        if (!$check) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $id = Auth::guard('employees')->user()->id;
        $employee = Employee::with('employment.roles')->where('id', $id)->first();

        return response()->json($employee);
    }

    // Logout as an employee
    public function logout(Request $request) {
        Auth::guard('employees')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out.']);
    }

    // Get the current employee
    public function me(Request $request) {
        $id = Auth::guard('employees')->user()->id;
        $employee = Employee::with('employment.roles')->where('id', $id)->first();
        return response()->json($employee);
    }

    // Transfer to other branch
    public function transfer(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rules = [
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('store_id', $store_id)],
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'roles' => ['required', 'array', 'min:1'],
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $this->transferEmployee($data['employee_id'], $data['branch_id'], $data['roles']);

        return response()->json([
            'message' => 'Employee transferred.',
        ]);
    }

    // Transfer many employees to other branch
    public function transferMany(Request $request) {
        $store_id = Auth::guard('stores')->user()->id;
        $data = $request->all();
        $rule = [
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('store_id', $store_id)],
            'employees' => ['required', 'array'],
            'employees.*.id' => ['required', Rule::exists('employees')->where('store_id', $store_id), Rule::unique('employments', 'employee_id')->where('branch_id', $data['branch_id'])->where('to', null)],
            'employees.*.roles' => ['required', 'array', 'min:1']
        ];

        $validator = Validator::make($data, $rule);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        foreach ($data['employees'] as $employee) {
            $this->transferEmployee($employee['id'], $data['branch_id'], $employee['roles']);
        }

        return response()->json([
            'message' => 'Employees transferred.',
        ]);
    }

    public function delete(Request $request, $id) {
        $store_id = Auth::guard('stores')->user()->id;

        // get employee
        $employee = Employee::where('store_id', $store_id)->where('id', $id)->first();
        if (!$employee) {
            return response()->json([
                'message' => 'Employee not found.',
            ], 404);
        }

        $employee->delete();

        // terminate employment
        $employment = Employment::where('employee_id', $id)->where('to', null)->first();
        if ($employment) {
            $employment->to = date("Y/m/d");
            $employment->save();
        }

        return response()->json([
            'message' => 'Employee deleted.',
        ]);
    }
}
