<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterController extends Controller
{
    public function login()
    {
        return response()->json(prepareResult(true, 'User not authorized.', getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
    }
}
