<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\FrequentQuery;

class SupplierController extends WebController
{
    private $sidebar_code = "sidebar_supplier";
    private $per_page = 10;

    public function listSupplier(Request $request, $store_id, $branch_id){
        $items = DB::table('suppliers')
                    ->where('branch_id', $branch_id)
                    ->where('deleted',0);
        $items = $request->input('search')? $items->where(function ($query) use($request){
                        $query->orWhere('suppliers.name','like', '%'.$request->input('search').'%');
                        $query->orWhere('suppliers.phone','like', '%'.$request->input('search').'%');
                    }) : $items;

        if($request->input('order_by') && $request->input('order')){
            $items = $items->orderBy($request->input('order_by'), $request->input('order'));
        } else {
            $items = $items->orderBy('id','desc');
        }
        
        $per_page = $this->per_page;
        $items = $items->paginate($per_page);
        $sidebar_code = $this->sidebar_code;
        return view('inventory.supplier.list', compact('sidebar_code', 'items'));
    }

    public function deleteSupplier(Request $request, $store_id, $branch_id, $supplier_id){
        if ($request->isMethod('post')) {
            $supplier = DB::table('suppliers')
                            ->where('id', $supplier_id)
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);
            DB::transaction(function () use ($supplier) {
                $supplier = $supplier->update([
                    'deleted'       => 1,
                ]);
            });
        }
    }

    public function editSupplier(Request $request, $store_id, $branch_id, $supplier_id){
        if ($request->isMethod('post')) {
            $supplier = DB::table('suppliers')
                            ->where('id', $supplier_id)
                            ->where('branch_id', $branch_id)
                            ->where('deleted', 0);
            DB::transaction(function () use ($request, $supplier) {
                $supplier = $supplier->update([
                    'name'       => $request->input('name'),
                    'phone'      => $request->input('phone'),
                    'address'    => $request->input('address'),
                    'email'      => $request->input('email'),
                ]);
            });
            return redirect()->route('supplier.list', compact('store_id', 'branch_id'));
        } else if ($request->isMethod('get')){
            $item = DB::table('suppliers')
                    ->where('branch_id', $branch_id)
                    ->where('deleted',0)
                    ->where('id', $supplier_id)
                    ->first();
            $sidebar_code = $this->sidebar_code;
            return view('inventory.supplier.edit', compact('sidebar_code', 'item'));
        }
    }

    public function createSupplier(Request $request, $store_id, $branch_id){
        if ($request->isMethod('post')) {
            DB::transaction(function () use ($request, $branch_id) {
                $supplier_id = DB::table('suppliers')->insertGetId([
                    'branch_id'     => $branch_id,
                    'name'          => $request->input('name'),
                    'email'         => $request->input('email'),
                    'phone'         => $request->input('phone'),
                    'address'       => $request->input('address'),
                ]);
            });
            return redirect()->route('supplier.list', compact('store_id', 'branch_id'));
        } else if ($request->isMethod('get')){
            $sidebar_code = $this->sidebar_code;
            return view('inventory.supplier.create', compact('sidebar_code'));
        }
    }
}