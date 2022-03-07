<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\MessageBag;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function formatValidationError(MessageBag $errors) {
        return implode("\n", $errors->all());
    }

    public function getQuery(Request $request) {
        $search = $request->query('search') ?? '';
        $from = $request->query('from') ?? 0;
        $to = $request->query('to') ?? 10;
        $order_by = $request->query('order_by') ?? 'created_at';
        $order_type = $request->query('order_type') ?? 'desc';

        return [$search, $from, $to, $order_by, $order_type];
    }
}
