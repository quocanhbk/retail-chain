<?php

namespace App;

use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class TokenController 
{
    public static function invalidateByID($user_id)
    {
        $jwt_list = self::getJWTListByID($user_id, 0);

        foreach($jwt_list as $jwt){
            self::customInvalidateToken($jwt->token);
            DB::transaction(function () use ($jwt) {
                DB::table('jwt_info')->where('jwt_info.id', $jwt->id)
                    ->update([
                        'is_invalidated'    => 1
                    ]);
            });
        }
    }

    public static function invalidateAllByIDExceptToken($user_id, $except_jwt)
    {
        $jwt_list = self::getJWTListByIDExceptToken($user_id, $except_jwt, 0);

        foreach($jwt_list as $jwt){
            self::customInvalidateToken($jwt->token);
            DB::transaction(function () use ($jwt) {
                DB::table('jwt_info')->where('jwt_info.id', $jwt->id)
                    ->update([
                        'is_invalidated'    => 1
                    ]);
            });
        }
    }

    public static function create($user_id, $token){
        DB::transaction(function () use ($user_id, $token) {
            DB::table('jwt_info')->insert([
                'user_id'   => $user_id,
                'token'     => $token,
            ]);
        });
    }

    private static function getJWTListByID($user_id, $is_invalidated = null){
        $jwt_list = DB::table('jwt_info')->where('jwt_info.user_id', $user_id);
        
        if($is_invalidated !== null){
            $jwt_list = $jwt_list->where('jwt_info.is_invalidated', $is_invalidated);
        }

        return $jwt_list->get();
    }

    private static function getJWTListByIDExceptToken($user_id, $except_jwt, $is_invalidated = null){
        $jwt_list = DB::table('jwt_info')->where('jwt_info.user_id', $user_id)
                                        ->where('jwt_info.token', '!=', $except_jwt);
        
        if($is_invalidated !== null){
            $jwt_list = $jwt_list->where('jwt_info.is_invalidated', $is_invalidated);
        }

        return $jwt_list->get();
    }

    private static function customInvalidateToken($token){
        try {
            auth()->setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {

        } catch (TokenInvalidException $e) {

        }
    }
}