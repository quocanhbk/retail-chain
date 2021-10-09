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
use DateTime;
use DatePeriod;
use DateInterval;

class AttendanceController extends Controller
{
    public function createAttendance(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $current_date = date('Y-m-d');
        $rules = [
            'schedule_id_list'  => ['required','array'],
            'schedule_id_list.*'=> ['required',Rule::exists('schedules','id')
                                                ->where(function ($query) use ($current_date){
                                                    $query = $query->orWhereNull('end_date');
                                                    $query = $query->orWhere('end_date', '>=', $current_date);
                                                })],
        ];
        $validator = Validator::make($data, $rules);

        $schedule_id_list = $request->input('schedule_id_list');

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);

        if($check_permission && !$validator->fails()){
            //check user in branch and valid date
            foreach($schedule_id_list as $schedule_id){
                $schedule_info = DB::table('schedules')->where('schedules.id', $schedule_id)->first();

                $is_work_in_branch = FrequentQuery::getEmployeeList($branch_id);
                $is_work_in_branch = $is_work_in_branch->where('works.user_id', $schedule_info->user_id)->exists();

                $day = date('l', strtotime($current_date));
                $day = strtolower($day);
                $is_correct_day = DB::table('shifts')
                                    ->where(function ($query) use ($current_date){
                                        $query = $query->orWhereNull('shifts.end_date');
                                        $query = $query->orWhere('shifts.end_date', '>=', $current_date);
                                    })
                                    ->leftJoin('schedules','schedules.shift_id','=','shifts.id')
                                    ->where("shifts.$day",1)->where('schedules.id', $schedule_id)
                                    ->exists();

                if(!$is_work_in_branch || !$is_correct_day){
                    $state = "fail";
                    $errors = !$is_work_in_branch? "user_id: ".$schedule_info->user_id." is not belonged to branch_id: ".$branch_id : "user_id: ".$schedule_info->user_id." doesnt work in $day of schedule_id: ".$schedule_id;
                    // $errors = "user_id: ".$user_id." is not belonged to branch_id: ".$branch_id;
                    return response()->json(compact('state','errors','data'));
                }
            }
            // start inserting
            foreach($schedule_id_list as $schedule_id){
                DB::transaction(function () use ($request, $schedule_id, $current_date){
                    $is_duplicate = DB::table('attendances')->whereRaw("attendances.schedule_id = $schedule_id AND attendances.date = '$current_date'")->exists();
                    if(!$is_duplicate){
                        DB::table('attendances')->insert([
                            'schedule_id'   => $schedule_id,
                            'date'          => $current_date,
                        ]);
                    }
                });
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

    public function getEmployeeToCheckList(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'date'          => 'required|date_format:Y-m-d',
            'have_attended' => 'nullable|boolean'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);

        if(!$validator->fails() && $check_permission){
            $day = date('l', strtotime($request->input('date')));
            $day = strtolower($day);

            $attendance_list = DB::table('shifts')->where('branch_id', $branch_id)
                                ->where(function ($query) use ($request){
                                        $query = $query->orWhereNull('shifts.end_date');
                                        $query = $query->orWhere('shifts.end_date', '>=', $request->input('date'));
                                })
                                ->where('shifts.start_date', '<=', $request->input('date'))
                                ->where("shifts.$day",1)

                                ->leftJoin('schedules','schedules.shift_id','=','shifts.id')
                                ->where(function ($query) use ($request){
                                        $query = $query->orWhereNull('schedules.end_date');
                                        $query = $query->orWhere('schedules.end_date', '>=', $request->input('date'));
                                })
                                ->where('schedules.start_date', '<=', $request->input('date'))
                                ->where('schedules.deleted', 0)
                                
                                ->leftJoin('attendances','attendances.schedule_id','=','schedules.id')
                                ->leftJoin('users','users.id','=','schedules.user_id')->where('users.status','enable');
            $attendance_list = $request->input('have_attended') === null? $attendance_list : 
                                ($request->input('have_attended')? $attendance_list->whereNotNull('attendances.date') : $attendance_list->whereNull('attendances.date'));
            $attendance_list = $attendance_list->selectRaw('shifts.id AS shifts_id, schedules.id AS schedule_id, users.id AS user_id, users.name, attendances.date');
            $attendance_list = $attendance_list->orderByRaw('shifts.id ASC, schedules.id ASC');
            $attendance_list = $attendance_list->get();
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'attendance_list'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if($validator->fails()){
                $errors = $validator->errors();
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getEmployeeAttendanceList(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'from_date'     => 'required|date_format:Y-m-d',
            'to_date'       => 'required|date_format:Y-m-d',
            'shift_id'      => ['nullable',Rule::exists('shifts','id')->where('branch_id', $branch_id)],
            'schedule_id'   => ['nullable',Rule::exists('schedules','id')],
            'user_id'       => ['nullable',Rule::exists('user','id')],
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['managing']);
        if($request->input('schedule_id')){
            $is_schedule_in_branch = DB::table('schedules')->where('schedules.id', $request->input('schedule_id'))
                                        ->leftJoin('shifts','shifts.id','=','schedules.shift_id')
                                        ->exists();
        } else {
            $is_schedule_in_branch = 1;
        }

        if(!$validator->fails() && $check_permission && $is_schedule_in_branch){
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');

            $begin = new DateTime($from_date);
            $end = new DateTime($to_date);
            $end = $end->modify( '+1 day' );

            $interval = DateInterval::createFromDateString('1 day');
            $period = new DatePeriod($begin, $interval, $end);

            $list = [];
            foreach ($period as $dt) {
                $day = $dt->format("l");
                $day = strtolower($day);
    
                $attendance_list = DB::table('shifts')->where('branch_id', $branch_id)
                                    ->where("shifts.$day",1)
                                    ->leftJoin('schedules','schedules.shift_id','=','shifts.id')
                                    ->where(function ($query) use ($from_date, $to_date){
                                        $query = $query->orWhereNull('schedules.end_date');
                                        $query = $query->orWhereRaw("(schedules.start_date BETWEEN '$from_date' AND '$to_date')");
                                        $query = $query->orWhereRaw("(schedules.end_date BETWEEN '$from_date' AND '$to_date')");
                                    })
                                    ->leftJoin('attendances', function($join) use ($dt){
                                        $join->on('attendances.schedule_id','=','schedules.id')
                                             ->where('attendances.date',$dt->format("Y-m-d"));
                                    })
                                    // ->leftJoin('attendances','attendances.schedule_id','=','schedules.id')
                                    // ->where('attendances.date',$dt->format("Y-m-d"))
                                    ->leftJoin('users','users.id','=','schedules.user_id')->where('users.status','enable');
    
                $attendance_list = $request->input('shift_id')? $attendance_list->where('shifts.id', $request->input('shift_id')) : $attendance_list;
                $attendance_list = $request->input('schedule_id')? $attendance_list->where('schedules.id', $request->input('schedule_id')) : $attendance_list;
                $attendance_list = $request->input('user_id')? $attendance_list->where('users.id', $request->input('user_id')) : $attendance_list;
    
                $attendance_list = $attendance_list->selectRaw('shifts.id AS shift_id, schedules.id AS schedule_id, users.id AS user_id, users.name, attendances.date');
                $attendance_list = $attendance_list->orderByRaw('shifts.id ASC, schedules.id ASC');
                $attendance_list = $attendance_list->get();

                $buffer['date'] = $dt->format("Y-m-d");
                $buffer['data'] = $attendance_list;
                $list[] = $buffer;
            }
            
            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'list'));
        } else {
            $state = 'fail';
            if(!$check_permission){
                $errors = 'You dont have the permission to do this';
            } else if($validator->fails()){
                $errors = $validator->errors();
            } else if($is_schedule_in_branch){
                $errors = "schedule_id: ".$request->input('schedule_id')." doesnt belong to branch_id: $branch_id";
            }
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
    
    // public function editShift(Request $request, $store_id, $branch_id)
    // {
    //     $user = auth()->user();
    //     $data = $request->except('token');
    //     $rules = [
    //         'shift_id'   => ['required',Rule::exists('shifts','id')->where('branch_id', $branch_id)],
            
    //         'name'          => 'required',
    //         'start_time'    => 'required',
    //         'end_time'      => 'required',
    //         'monday'        => 'required|boolean',
    //         'tuesday'       => 'required|boolean',
    //         'wednesday'     => 'required|boolean',
    //         'thursday'      => 'required|boolean',
    //         'friday'        => 'required|boolean',
    //         'saturday'      => 'required|boolean',
    //         'sunday'        => 'required|boolean',
    //         'start_date'    => 'date_format:Y-m-d',
    //         'end_date'      => 'nullable|date_format:Y-m-d',
    //     ];
    //     $validator = Validator::make($data, $rules);

    //     if(!$validator->fails()){
    //         DB::transaction(function () use ($request, $branch_id) {
    //             DB::table('shifts')->where('id', $request->input('shift_id'))
    //                 ->update([
    //                     'name'          => $request->input('name'),
    //                     'start_time'    => $request->input('start_time'),
    //                     'end_time'      => $request->input('end_time'),
    //                     'monday'        => $request->input('monday'),
    //                     'tuesday'       => $request->input('tuesday'),
    //                     'wednesday'     => $request->input('wednesday'),
    //                     'thursday'      => $request->input('thursday'),
    //                     'friday'        => $request->input('friday'),
    //                     'sunday'        => $request->input('sunday'),
    //                     'start_date'    => $request->input('start_date'),
    //                     'end_date'      => $request->input('end_date'),
    //                 ]);
    //         });
            
    //         $state ='success';
    //         $errors = 'none';
    //         return response()->json(compact('state', 'errors', 'data'));
    //     } else {
    //         $state = 'fail';
    //         $errors = $validator->errors();
            
    //         return response()->json(compact('state', 'errors', 'data'));
    //     }
    // }
}