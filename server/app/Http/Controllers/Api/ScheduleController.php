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

class ScheduleController extends Controller
{
    public function createSchedule(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'shift_id'      => ['required',
                                Rule::exists('shifts','id')->where('branch_id', $branch_id)],
            'user_id_list'  => 'required|array',
            'start_date'    => ['required','date_format:Y-m-d'],
            'end_date'      => ['nullable','date_format:Y-m-d'],
        ];
        $validator = Validator::make($data, $rules);

        $user_id_list = $request->input('user_id_list');

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);

        if($check_permission && !$validator->fails() && count($user_id_list)){
            //check user in branch
            foreach($user_id_list as $user_id){
                $is_work_in_branch = FrequentQuery::getEmployeeList($branch_id);
                $is_work_in_branch = $is_work_in_branch->where('works.user_id', $user_id)->exists();

                $is_schedule_same_shift = DB::table('schedules')
                                            ->where('shift_id', $request->input('shift_id'))
                                            ->where('user_id', $user_id)
                                            ->where('deleted',0)
                                            ->whereNull('end_date')
                                            ->exists();

                if(!$is_work_in_branch || $is_schedule_same_shift){
                    $state = "fail";
                    $errors = !$is_work_in_branch? "user_id: ".$user_id." is not belonged to branch_id: ".$branch_id : "user_id: ".$user_id." is already working in shift_id: ".$request->input('shift_id');
                    return response()->json(compact('state','errors','data'));
                }
            }

            
            $shift = DB::table('shifts')->where('id', $request->input('shift_id'))->first();
            $check_valid_date = DB::table('shifts')->where('id',$request->input('shift_id'))
                                    ->where('start_date','<=',$request->input('start_date'))
                                    ->where(function ($query) use ($request, $shift){
                                        if($shift->end_date){
                                            $query = $request->input('end_date')? $query->orWhere('end_date', '>=', $request->input('end_date')) : $query;
                                        }
                                    })
                                    ->exists();
            if($check_valid_date){
                //start inserting
                foreach($user_id_list as $user_id){
                    //for existing employee in shift
                    $employee_in_shift = DB::table('schedules')->where('user_id', $user_id)->where('shift_id', $request->input('shift_id'));
                    if($employee_in_shift->exists()){
                        $employee_in_shift->update([
                            'deleted'       => 0,
                            'start_date'    => $request->input('start_date'),
                            'end_date'      => $request->input('end_date'),
                        ]);
                    } else {
                        DB::transaction(function () use ($request, $user_id){
                            DB::table('schedules')->insert([
                                'shift_id'      => $request->input('shift_id'),
                                'user_id'       => $user_id,
                                'start_date'    => $request->input('start_date'),
                                'end_date'      => $request->input('end_date'),
                            ]);
                        });
                    }
                }
            } else {
                $state ='fail';
                $errors = 'unvalid date';
                return response()->json(compact('state', 'errors', 'data'));
            }
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            // $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getEmployeeSchedule(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'shift_id'      => ['nullable',Rule::exists('shifts','id')->where('branch_id', $branch_id)],
            'user_id'       => ['nullable',Rule::exists('users','id')],
        ];
        $validator = Validator::make($data, $rules);


        if(!$validator->fails()){
            $schedule_list = DB::table('shifts')->where('branch_id', $branch_id)
                                ->join('schedules','schedules.shift_id','=','shifts.id')->where('schedules.deleted', 0)
                                ->join('users','users.id','=','schedules.user_id');

            $schedule_list = $request->input('shift_id')? $schedule_list->where('shifts.id', $request->input('shift_id')) : $schedule_list;
            $schedule_list = $request->input('user_id')? $schedule_list->where('users.id', $request->input('user_id')) : $schedule_list;

            $schedule_list = $schedule_list->selectRaw('shifts.id AS shifts_id, schedules.id AS schedule_id, schedules.start_date AS schedule_start_date, schedules.end_date AS schedule_end_date, users.id AS user_id, users.name, users.status');
            $schedule_list = $schedule_list->orderByRaw('shifts.id ASC, schedules.id ASC');
            $schedule_list = $schedule_list->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'schedule_list'));
        } else {
            $state = 'fail';
            if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    public function deleteSchedule(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'schedule_id'   => ['required',Rule::exists('schedules','id')],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);
        $check_schedule_belong_branch = DB::table('schedules')->where('schedules.id', $request->input('schedule_id'))
                                            ->leftJoin('shifts', 'shifts.id', '=', 'schedules.shift_id')
                                            ->where('shifts.branch_id', $branch_id)
                                            ->exists();

        if(!$validator->fails() && $check_permission && $check_schedule_belong_branch){
            DB::table('schedules')
                ->where('schedules.id', $request->input('schedule_id'))
                ->update([
                    'schedules.deleted' => 1,
                ]);
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if(!$check_schedule_belong_branch){
                $errors = 'schedule_id doesnt belong to branch';
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}