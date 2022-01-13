<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Employment;
use App\Models\EmploymentRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    private function getEmployeeDetail ($id) {
        $employee = Employee::find($id);
        $employment = Employment::where('employee_id', $id)->first();
        // get employment roles, convert to array of strings
        $roles = EmploymentRoles::where('employment_id', $employment->id)->get()->pluck('role')->toArray();
        // get branch
        $branch = Branch::find($employment->branch_id);
        // assign roles to employment
        $employment->roles = $roles;
        // assign branch to employment
        $employment->branch = $branch;
        // assign employment to employee
        $employee->employment = $employment;

        return $employee;
    }

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
        $avatar_image_path = $avatar_exist ?
            $request->file('avatar')->storeAs('employees', $store_id . Str::uuid() . "." . $request->file('avatar')->getClientOriginalExtension()) : null;


        $employee = Employee::create([
            'store_id' => $store_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $request->input('phone'),
            'birthday' => $request->input('birthday'),
            'avatar_url' => $avatar_image_path,
            'gender' => $request->input('gender')
        ]);

        $employment = Employment::create([
            'employee_id' => $employee->id,
            'branch_id' => $data['branch_id'],
            'from' => date("Y/m/d")
        ]);

        // create employment roles
        foreach ($data['roles'] as $role) {
            EmploymentRoles::create([
                'employment_id' => $employment->id,
                'role' => $role
            ]);
        }

        return response()->json($employee);
    }

    // Get avatar
    public function getAvatar($employee_id) {
        $employee = Employee::find($employee_id);
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

        $employee = $this->getEmployeeDetail($id);

        return response()->json($employee);
    }

    public function getEmployeesByBranchId(Request $request, $branch_id) {
        $store_id = Auth::guard('stores')->user()->id;

        $employees = Employee::with('employment')->where('store_id', $store_id)->whereRelation('employment', 'branch_id', $branch_id)->get();
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
        $employee = $this->getEmployeeDetail($id);

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
        $employee = $this->getEmployeeDetail(Auth::guard('employees')->user()->id);
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

        // employee must currently not working in the destination branch
        $employment = Employment::where('employee_id', $data['employee_id'])
            ->where('branch_id', $data['branch_id'])
            ->where('to', null)
            ->first();

        if ($employment) {
            return response()->json([
                'message' => 'Employee is already working in this branch.',
            ], 400);
        }

        $validator = Validator::make($data, $rules);
        if ($validator->failed()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }

        $old_employment = Employment::where('employee_id', $data['employee_id'])->where('to', null)->first();
        $old_employment->to = date("Y/m/d");
        $old_employment->save();

        $new_employment = Employment::create([
            'employee_id' => $data['employee_id'],
            'branch_id' => $data['branch_id'],
            'from' => date("Y/m/d")
        ]);

        // create employment roles
        foreach ($data['roles'] as $role) {
            EmploymentRoles::create([
                'employment_id' => $new_employment->id,
                'role' => $role
            ]);
        }

        $employee = Employee::with(['employment' => function ($query) {
            $query->with(['roles:id,role,employment_id'])->get();
        }])->where('store_id', $store_id)->where('id', $data['employee_id'])->first();

        return response()->json($employee);
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
