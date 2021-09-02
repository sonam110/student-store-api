<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Label;
use App\Http\Resources\LabelResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LabelsImport;

class LabelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $labels = Label::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $labels = Label::get();
            }
            return response(prepareResult(false, $labels, getLangByLabelGroups('messages','message_label_list')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {        
        $validation = Validator::make($request->all(), [
            'label_name'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $label = new Label;
            $label->label_group_id         = $request->label_group_id;
            $label->language_id            = $request->language_id;
            $label->label_name             = $request->label_name;
            $label->label_value            = $request->label_value;
            $label->status                 = $request->status;
            $label->save();
            DB::commit();
            return response()->json(prepareResult(false, new LabelResource($label), getLangByLabelGroups('messages','message_label_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Label  $label
     * @return \Illuminate\Http\Response
     */
    public function show(Label $label)
    {
        return response()->json(prepareResult(false, new LabelResource($label), getLangByLabelGroups('messages','message_label_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Label  $label
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,Label $label)
    {
        $validation = Validator::make($request->all(), [
            'label_name' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $label->label_group_id         = $request->label_group_id;
            $label->language_id            = $request->language_id;
            $label->label_name             = $request->label_name;
            $label->label_value            = $request->label_value;
            $label->status                 = $request->status;
            $label->save();
            DB::commit();
            return response()->json(prepareResult(false, new LabelResource($label), getLangByLabelGroups('messages','message_label_updated')), config('http_response.success'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Label $label
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Label $label)
    {
        $label->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_label_deleted')), config('http_response.success'));
    }

    public function labelsImport(Request $request)
    {
        $data = ['language_title' => $request->language_title, 'language_value' => $request->language_value];
        $import = Excel::import(new LabelsImport($data),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_products_services_book_imported')), config('http_response.success'));
    }
}
