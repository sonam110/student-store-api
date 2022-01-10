<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\ModuleType;

class PackageController extends Controller
{
    public function index()
    {
        try
        {
            $packages = Package::where('is_published', '1')->orderBy('auto_id','ASC')->get();
            return response(prepareResult(false, $packages, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function show($id)
    {
        try
        {
            $package = Package::find($id);
            return response(prepareResult(false, $package, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function packageByType($package_for)
    {
        try
        {
            $packages = Package::where('is_published', '1')->where('package_for', $package_for)->orderBy('auto_id','ASC')->get();
            return response(prepareResult(false, $packages, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function packageByModule($module)
    {
        try
        {
            $packages = Package::where('is_published', '1')->where('module', $module)->orderBy('auto_id','ASC')->get();
            return response(prepareResult(false, $packages, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function modules()
    {
        try
        {
            $modules = ModuleType::select('id','title','slug','description','status')->orderBy('auto_id','ASC')->get();
            return response(prepareResult(false, $modules, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
