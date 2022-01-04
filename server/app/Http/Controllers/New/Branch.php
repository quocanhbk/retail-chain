<?php

namespace App\Http\Controllers\New;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Branch extends Controller
{
    /**
     * Create new branch
     * @middleware jwt.confirm, only-owner
     * @route /branch
     */
    public function create(Request $request) {
        $user = auth()->user();

        $data = $request->all();

        $rules = [
            'name' => 'required',
            'address' => 'required'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->failed()) {
            $state = 'failed';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'), 401);
        }

        $branch_id = DB::table('branches')->insertGetId([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'store_id' => $user->store_id
        ]);

        $state = 'success';
        $info = compact('branch_id');
        return response()->json(compact('state', 'info'), 200);
    }

    public function getBranches(Request $request) {
        $user = auth()->user();

        $data = $request->all();

        $branches = DB::table('branches')->where('store_id', $user->store_id)->where('deleted', false)->get();

        $info = compact('branches');
        $state = 'success';

        return response()->json(compact('state', 'info'), 200);
    }

    public function deleteBranch(Request $request) {
        $user = auth()->user();
        $data = $request->all();

        $rules = [
            'branch_id' => 'required|number'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->failed()) {
            $state = 'failed';
            $errors = $validator->errors();
            return response()->json(compact('state', 'errors'), 400);
        }

        DB::table('branches')->where('id', $request->input('id'))->update(['deleted' => true]);

        $state = 'success';
        return response()->json(compact('state'), 200);
    }

}
