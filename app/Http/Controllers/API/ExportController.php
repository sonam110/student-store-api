<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProductsServicesBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Exports\JobsExport;
use App\Exports\ContestsExport;
use App\Exports\OrdersExport;


class ExportController extends Controller
{
    // public function productsExport(Request $request) 
    // {
    // 	$rand = rand(1,9);
    // 	$data = ['auth_applicable' => true, 'ids' => $request->product_id, 'type' => $request->type ];
    //     $excel = Excel::store(new ProductsExport($data), 'export/products'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/products'.$rand.'.xlsx';
    // }

    // public function jobsExport(Request $request) 
    // {
    // 	$rand = rand(1,9);
    // 	$data = ['auth_applicable' => true, 'ids' => $request->job_id];
    //     $excel = Excel::store(new JobsExport($data), 'export/jobs'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/jobs'.$rand.'.xlsx';
    // }

    // public function contestsExport(Request $request) 
    // {
    // 	$rand = rand(1,9);
    // 	$data = ['auth_applicable' => true, 'ids' => $request->contest_id, 'type' => $request->type ];
    //     $excel = Excel::store(new ContestsExport($data), 'export/contests'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/contests'.$rand.'.xlsx';
    // }

    // public function ordersExport(Request $request) 
    // {
    // 	$rand = rand(1,9);
    // 	$data = ['auth_applicable' => true, 'ids' => $request->order_id, 'product_type' => $request->product_type, 'contest_type' => $request->contest_type, 'order_for' => $request->order_for ];
    //     $excel = Excel::store(new OrdersExport($data), 'export/orders'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/orders'.$rand.'.xlsx';
    // }

    public function productsExport(Request $request) 
    {
        $rand = rand(1,9);
        $data = ['auth_applicable' => true, 'ids' => $request->product_id, 'type' => $request->type ];
        $excel = Excel::store(new ProductsExport($data), 'export/products'.$rand.'.xlsx' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/products'.$rand.'.xlsx'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }

    public function jobsExport(Request $request) 
    {
        $rand = rand(1,9);
        $data = ['auth_applicable' => true, 'ids' => $request->job_id];
        $excel = Excel::store(new JobsExport($data), 'export/jobs'.$rand.'.xlsx' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/jobs'.$rand.'.xlsx' ], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }

    public function contestsExport(Request $request) 
    {
        $rand = rand(1,9);
        $data = ['auth_applicable' => true, 'ids' => $request->contest_id, 'type' => $request->type ];
        $excel = Excel::store(new ContestsExport($data), 'export/contests'.$rand.'.xlsx' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/contests'.$rand.'.xlsx'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
    }

    public function ordersExport(Request $request) 
    {
        $rand = rand(1,9);
        $data = ['auth_applicable' => true, 'ids' => $request->order_id, 'product_type' => $request->product_type, 'contest_type' => $request->contest_type, 'order_for' => $request->order_for ];
        $excel = Excel::store(new OrdersExport($data), 'export/orders'.$rand.'.xlsx' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/orders'.$rand.'.xlsx'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }
}
