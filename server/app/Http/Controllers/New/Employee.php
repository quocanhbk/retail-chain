<?php

namespace App\Http\Controllers\New;

use App\CommonApi;
use App\CommonQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Employee extends Controller {

    public function login(Request $request) {
        return CommonApi::login($request, false);
    }

    public function logout(Request $request) {
        return CommonApi::logout($request);
    }

    public function refreshToken(Request $request) {
        return CommonApi::refreshToken($request);
    }

    public function getInfo(Request $request){

        $info = CommonQuery::getInfo(auth()->user());

        if (!$info) {
            $state = 'failed';
            $errors = 'User not found';
            return response()->json(compact('state', 'errors'), 404);
        }

        $state = 'success';
        return response()->json(compact('state', 'info'), 200);

    }

}
