<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class CategoryController extends WebController
{
    private $sidebar_code = "sidebar_category";
    private $per_page = 10;

    public function listCategory(Request $request, $store_id, $branch_id){
        $items = DB::table('item_categories')
                    ->where('branch_id', $branch_id)
                    ->where('deleted',0);

        if($request->input('search')){
            $items = $items->where(function ($query) use ($request) {
                            $query->where('item_categories.name', 'like' ,'%'.$request->input('search').'%');
                        });
        }
        if($request->input('order_by') && $request->input('order')){
            $items = $items->orderBy($request->input('order_by'), $request->input('order'));
        } else {
            $items = $items->orderBy('item_categories.name', 'asc');
        }
        $sidebar_code = $this->sidebar_code;
        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        return view('inventory.category.list', compact('sidebar_code', 'per_page', 'items'));
    }

    public function editCategory(Request $request, $store_id, $branch_id, $category_id){
        if ($request->isMethod('post')) {
            $category = DB::table('item_categories')
                            ->where('id', $category_id)
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);

            DB::transaction(function () use ($request, $category){
                $category = $category->update([
                    'name'          => $request->input('category_name'),
                    'point_ratio'   => $request->input('point_ratio'),
                ]);
            });
            return redirect()->route('category.lsit', compact('store_id', 'branch_id'));
        } else if ($request->isMethod('get')) {
            $item = DB::table('item_categories')
                    ->where('branch_id', $branch_id)
                    ->where('deleted',0)
                    ->where('id', $category_id);
            $sidebar_code = $this->sidebar_code;
            $item = $item->first();
            return view('inventory.category.edit', compact('sidebar_code', 'item'));
        }
    }

    public function deleteCategory(Request $request, $store_id, $branch_id, $category_id){
        $category = DB::table('item_categories')
                ->where('id', $category_id)
                ->where('branch_id', $branch_id)
                ->where('deleted', 0);

        DB::transaction(function () use ($request, $category){
            $category = $category->update([
                'deleted'          => 1,
            ]);
        });
        return redirect()->route('category.list', compact('store_id', 'branch_id'));
    }

    public function createCategory(Request $request, $store_id, $branch_id){
        if ($request->isMethod('post')) {
            DB::transaction(function () use ($request, $branch_id) {
                $category_id = DB::table('item_categories')->insertGetId([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('category_name'),
                    'point_ratio'   => $request->input('point_ratio'),
                ]);
            });
            return redirect()->route('category.list', compact('store_id', 'branch_id'));
        } else if ($request->isMethod('get')) {

            $sidebar_code = $this->sidebar_code;
            return view('inventory.category.create', compact('sidebar_code'));
        }
    }
}