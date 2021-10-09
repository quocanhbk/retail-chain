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

class ItemCategoryController extends Controller
{
    public function createCategory(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'name'          => 'required'
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);
        if($check_permission && !$validator->fails()){
            DB::transaction(function () use ($request, $branch_id) {
                $category_id = DB::table('item_categories')->insertGetId([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('name'),
                ]);
            });

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function getCategory(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'category_id'   => ['integer',Rule::exists('item_categories','id')->where('branch_id',$branch_id)]
        ];
        $validator = Validator::make($data, $rules);

        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing', 'selling']);
        if($check_permission && !$validator->fails()){
            $category = DB::table('item_categories')
                        ->where('branch_id', $branch_id)
                        ->where('deleted',0);
            $category = $request->input('category_id')? $category->where('id', $request->input('category_id')) : $category;
            $category = $category->get();

            $state ='success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data', 'category'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this': $validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }

    public function editCategory(Request $request, $store_id, $branch_id)
    {
        $user = auth()->user();
        $data = $request->except('token');
        $rules = [
            'category_id'   => ['required', Rule::exists('item_categories', 'id')->where('branch_id', $branch_id)->where('deleted', 0)],
            'deleted'       => 'required|boolean',
            'name'          => Rule::requiredIf($request->input('deleted') == 0),
            'point_ratio'   => Rule::requiredIf($request->input('deleted') == 0),
        ];
        $validator = Validator::make($data, $rules);
        $check_permission = FrequentQuery::checkPermission($user->id, $branch_id, ['purchasing']);

        if($check_permission && !$validator->fails()){
            $category = DB::table('item_categories')
                            ->where('id', $request->input('category_id'))
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);

            DB::transaction(function () use ($request, $category){
                if($request->input('deleted')){
                    $category = $category->update([
                        'deleted'       => 1
                    ]);
                } else {
                    $category = $category->update([
                        'name'          => $request->input('name'),
                        'point_ratio'   => $request->input('point_ratio'),
                    ]);
                }
            });

            $state = 'success';
            $errors = 'none';
            return response()->json(compact('state', 'errors', 'data'));
        } else {
            $state = 'fail';
            $errors = !$check_permission? 'You dont have the permission to do this':$validator->errors();
            return response()->json(compact('state', 'errors', 'data'));
        }
    }
}