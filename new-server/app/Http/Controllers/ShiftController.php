<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller {

    public function create(Request $request) {
        $data = $request->all();
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

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }
        $branch = Branch::where('id', $data['branch_id'])->where('name', $name)->first();
       // check if shift belong to brach
        $shift = Shift::where('name', $data['name'])->where('brach_id', $branch_id)->first();
        error_log($shift);
        if (!$shift) {
            return response()->json([
                'message' => 'Shift not found.',
            ], 404);
        }

        $shift = Shift::create([
            'branch_id' => $data['branch_id'],
            'name' => $data['name'],
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

        return response()->json($shift);
    }

    // Get all shift
    public function getShiftes() {
        $branch = Branch::where('id', $data['branch_id'])->where('name', $name)->first();
        $shiftes = Shift::where('branch_id', $branch_id)->get();
        return response()->json($shiftes);
    }

    // Get a shift
    public function getShift(Request $request, $name) {
        $branch = Branch::where('id', $data['branch_id'])->where('name', $name)->first();
        $shift = Shift::where('branch_id', $branch_id)->where('name', $name)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return response()->json($shift);
    }

    public function update(Request $request, $name) {
        $data = $request->all();
        $branch = Branch::where('id', $data['branch_id'])->where('name', $name)->first();
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'datetime:Y-m-d'],
            'end_time' => ['required', 'datetime:Y-m-d'],
            'monday'=> 'required|boolean',
            'tuesday'=> 'required|boolean',
            'wednesday' => 'required|boolean',
            'thursday'  => 'required|boolean',
            'friday'  => 'required|boolean',
            'saturday' => 'required|boolean',
            'sunday'  => 'required|boolean',
            'start_date' => 'date_format:Y-m-d',
            'end_date'  => 'nullable|date_format:Y-m-d',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 400);
        }
        // make sure branch owns shift
        $shift = shift::where('branch_id', $branch_id)->where('name', $name)->first();
        if (!$shift) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $shift->name = $data['name'];
        $shift->start_time = $data['start_time'];
        $shift->end_time = $data['end_time'];
        $shift->monday = $data['monday'];
        $shift->tuesday = $data['tuesday'];
        $shift->wednesday = $data['wednesday'];
        $shift->thursday = $data['thursday'];
        $shift->friday = $data['friday'];
        $shift->saturday = $data['saturday'];
        $shift->sunday = $data['sunday'];
        $shift->start_date = $data['start_date'];
        $shift->end_date = $data['end_date'];
        $shift->save();

        return response()->json($shift);
    }

    public function delete(Request $request, $name) {
        $data = $request->all();
        
        $branch = Branch::where('id', $data['branch_id'])->where('name', $name)->first();
        // make sure branch owns shift
        $shift = shift::where('branch_id', $branch_id)->where('name', $name)->first();
        $shift->delete();

        return response()->json($shift);
    }    

}
