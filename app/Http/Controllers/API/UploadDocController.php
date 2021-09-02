<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use PDF;

use Image;

class UploadDocController extends Controller
{
    public function store(Request $request)
    {
        if($request->is_multiple==1)
        {
            $validation = \Validator::make($request->all(),[ 
                'file'     => 'required|array|max:20000|min:1',
                "file.*"  => "required|min:1|mimes:doc,docx,png,jpeg,jpg,pdf,svg,mp4",
            ]);
        }
        else
        {
            $validation = \Validator::make($request->all(),[ 
                'file'     => 'required|max:10000|mimes:doc,docx,png,jpeg,jpg,pdf,svg,mp4',
            ]);
        }
        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        try
        {
            $file = $request->file;
            $destinationPath = 'uploads/';
            $fileArray = array();

            if($request->is_multiple==1)
            {
                foreach ($file as $key => $value) {
                    $fileName   = Str::slug($value).time().'-'.rand(0,99999).'.' . $value->getClientOriginalExtension();
                    $extension = $value->getClientOriginalExtension();

                    if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png')
                    {
                        $img = Image::make($value->getRealPath());
                        // $img->fit(250,250, function ($constraint) {
                        //     $constraint->aspectRatio();
                        // },'top')->save($destinationPath.'/'.$fileName);

                        $img->save($destinationPath.'/'.$fileName, 75);
                    }
                    else
                    {
                        $value->move($destinationPath, $fileName);
                    }
                    
                    $fileArray[] = [
                        'file_name'         => env('CDN_DOC_URL').$destinationPath.$fileName,
                        'file_extension'    => $value->getClientOriginalExtension()
                    ];
                }

                return response(prepareResult(false, $fileArray, getLangByLabelGroups('messages','messages_success')), config('http_response.success'));
            }
            else
            {
                $fileName   = Str::slug($request->file_title).time().'-'.rand(0,99999).'.' . $file->getClientOriginalExtension();
                $extension = $file->getClientOriginalExtension();
                if($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png')
                {
                    $img = Image::make($file->getRealPath());
                    // $img->fit(250,250, function ($constraint) {
                    //     $constraint->aspectRatio();
                    // },'top')->save($destinationPath.'/'.$fileName);

                    $img->save($destinationPath.'/'.$fileName, 75);
                }
                else
                {
                    $file->move($destinationPath, $fileName);
                }
                $fileInfo = [
                    'file_name'         => env('CDN_DOC_URL').$destinationPath.$fileName,
                    'file_extension'    => $file->getClientOriginalExtension()
                ];
                return response(prepareResult(false, $fileInfo, getLangByLabelGroups('messages','messages_success')), config('http_response.success'));
            }   
        }
        catch (\Throwable $exception)
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }
}
