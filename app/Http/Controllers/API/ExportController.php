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
    // 	$rand = date('d-m-y--H-i-s');
    // 	$data = ['auth_applicable' => true, 'ids' => $request->product_id, 'type' => $request->type ];
    //     $excel = Excel::store(new ProductsExport($data), 'export/products'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/products'.$rand.'.xlsx';
    // }

    // public function jobsExport(Request $request) 
    // {
    // 	$rand = date('d-m-y--H-i-s');
    // 	$data = ['auth_applicable' => true, 'ids' => $request->job_id];
    //     $excel = Excel::store(new JobsExport($data), 'export/jobs'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/jobs'.$rand.'.xlsx';
    // }

    // public function contestsExport(Request $request) 
    // {
    // 	$rand = date('d-m-y--H-i-s');
    // 	$data = ['auth_applicable' => true, 'ids' => $request->contest_id, 'type' => $request->type ];
    //     $excel = Excel::store(new ContestsExport($data), 'export/contests'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/contests'.$rand.'.xlsx';
    // }

    // public function ordersExport(Request $request) 
    // {
    // 	$rand = date('d-m-y--H-i-s');
    // 	$data = ['auth_applicable' => true, 'ids' => $request->order_id, 'product_type' => $request->product_type, 'contest_type' => $request->contest_type, 'order_for' => $request->order_for ];
    //     $excel = Excel::store(new OrdersExport($data), 'export/orders'.$rand.'.xlsx' , 'export_path');
    //     return env('ASSET_URL').'export/orders'.$rand.'.xlsx';
    // }

    public function productsExport(Request $request) 
    {
        $rand = 'product-service-book-'.date('d-m-y--H-i-s');
        $data = ['auth_applicable' => true, 'ids' => null, 'type' => $request->type ];
        if(file_exists('public/export/products/'.$rand.'.csv')){ 
            unlink('public/export/products/'.$rand.'.csv');
        }
        $excel = Excel::store(new ProductsExport($data), 'export/products/'.$rand.'.csv' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/products/'.$rand.'.csv'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }

    public function jobsExport(Request $request) 
    {
        $rand = 'jobs-'.date('d-m-y--H-i-s');
        $data = ['auth_applicable' => true, 'ids' => null];
        if(file_exists('public/export/jobs/'.$rand.'.csv')){ 
            unlink('public/export/jobs/'.$rand.'.csv');
        }
        $excel = Excel::store(new JobsExport($data), 'export/jobs/'.$rand.'.csv' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/jobs/'.$rand.'.csv' ], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }

    public function contestsExport(Request $request) 
    {
        $rand = 'contests-'.date('d-m-y--H-i-s');
        $data = ['auth_applicable' => true, 'ids' => null, 'type' => $request->type ];
        if(file_exists('public/export/contests/'.$rand.'.csv')){ 
            unlink('public/export/contests/'.$rand.'.csv');
        }
        $excel = Excel::store(new ContestsExport($data), 'export/contests/'.$rand.'.csv' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/contests/'.$rand.'.csv'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
    }

    public function ordersExport(Request $request) 
    {
        $rand = 'orders-'.date('d-m-y--H-i-s');
        $data = ['auth_applicable' => true, 'ids' => null, 'product_type' => $request->product_type, 'contest_type' => $request->contest_type, 'order_for' => $request->order_for ];
        if(file_exists('public/export/orders/'.$rand.'.csv')){ 
            unlink('public/export/orders/'.$rand.'.csv');
        }
        $excel = Excel::store(new OrdersExport($data), 'export/orders/'.$rand.'.csv' , 'export_path');
         return response(prepareResult(false, ['url' => env('ASSET_URL').'export/orders'.$rand.'.csv'], getLangByLabelGroups('messages','message_created')), config('http_response.success'));
         
    }
}
