<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Validation\Rule;
use App\FrequentQuery;

class ShiftController extends Controller
{
    public function createShift(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'          => 'required',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'monday'        => 'required|boolean',
            'tuesday'       => 'required|boolean',
            'wednesday'     => 'required|boolean',
            'thursday'      => 'required|boolean',
            'friday'        => 'required|boolean',
            'saturday'      => 'required|boolean',
            'sunday'        => 'required|boolean',
            'start_date'    => 'date_format:Y-m-d',
            'end_date'      => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        // $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);
        // $check_invoice_id = DB::table('invoices')->where('invoices.branch_id',$branch_id)->where('invoices.id',$refund_sheet['invoice_id'])->exists();

        if(!$validator->fails()){
            DB::transaction(function () use ($request, $branch_id) {
                DB::table('shifts')->insert([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('name'),
                    'start_time'    => $request->input('start_time'),
                    'end_time'      => $request->input('end_time'),
                    'monday'        => $request->input('monday'),
                    'tuesday'       => $request->input('tuesday'),
                    'wednesday'     => $request->input('wednesday'),
                    'thursday'      => $request->input('thursday'),
                    'friday'        => $request->input('friday'),
                    'saturday'      => $request->input('saturday'),
                    'sunday'        => $request->input('sunday'),
                    'start_date'    => $request->input('start_date'),
                    'end_date'      => $request->input('end_date'),
                ]);
            });
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state ='fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getShift(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'shift_id'   => ['integer',Rule::exists('shifts','id')->where('branch_id', $branch_id)],
        ];
        $validator = Validator::make($data, $rules);

        // $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['selling']);

        if(!$validator->fails()){
            $shift = DB::table('shifts')->where('branch_id', $branch_id)
                        ->where(function ($query){
                            $current_date = date('Y-m-d');
                            $query = $query->orWhereNull('end_date');
                            $query = $query->orWhere('end_date', '>=', $current_date);
                        });

            $shift = $request->input('shift_id')? $shift->where('id', $request->input('shift_id')) : $shift;         

            $shift = $shift->selectRaw('id AS shift_id, name, start_time, end_time, monday, tuesday, wednesday, thursday, friday, saturday, sunday, start_date, end_date');
            $shift = $shift->orderByRaw('start_date DESC');
            $shift = $shift->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'shift'));
        } else {
            $state = 'fail';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    public function editShift(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'shift_id'   => ['required',Rule::exists('shifts','id')->where('branch_id', $branch_id)],
            
            'name'          => 'required',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'monday'        => 'required|boolean',
            'tuesday'       => 'required|boolean',
            'wednesday'     => 'required|boolean',
            'thursday'      => 'required|boolean',
            'friday'        => 'required|boolean',
            'saturday'      => 'required|boolean',
            'sunday'        => 'required|boolean',
            'start_date'    => 'required|date_format:Y-m-d',
            'end_date'      => 'nullable|date_format:Y-m-d',
        ];
        $validator = Validator::make($data, $rules);

        if(!$validator->fails()){
            DB::transaction(function () use ($request, $branch_id) {
                DB::table('shifts')->where('id', $request->input('shift_id'))
                    ->update([
                        'name'          => $request->input('name'),
                        'start_time'    => $request->input('start_time'),
                        'end_time'      => $request->input('end_time'),
                        'monday'        => $request->input('monday'),
                        'tuesday'       => $request->input('tuesday'),
                        'wednesday'     => $request->input('wednesday'),
                        'thursday'      => $request->input('thursday'),
                        'friday'        => $request->input('friday'),
                        'saturday'      => $request->input('saturday'),
                        'sunday'        => $request->input('sunday'),
                        'start_date'    => $request->input('start_date'),
                        'end_date'      => $request->input('end_date'),
                    ]);
            });
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = $validator->errors();
            
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}