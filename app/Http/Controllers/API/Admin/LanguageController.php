<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Http\Resources\LanguageResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LanguagesImport;
use Auth;
use App\Models\Label;

class LanguageController extends Controller
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
                $languages = Language::simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $languages = Language::get();
            }
            return response(prepareResult(false, LanguageResource::collection($languages), getLangByLabelGroups('messages','message_language_list')), config('http_response.success'));
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
            'title'  => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $language = new Language;
            $language->title               	= $request->title;
            $language->value         		= $request->value;
            $language->status               = $request->status;
            $language->direction      		= $request->direction;
            $language->save();
            DB::commit();
            return response()->json(prepareResult(false, new LanguageResource($language), getLangByLabelGroups('messages','message_language_created')), config('http_response.created'));
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
     * @param  \App\Language  $language
     * @return \Illuminate\Http\Response
     */
    public function show(Language $language)
    {
        return response()->json(prepareResult(false, new LanguageResource($language), getLangByLabelGroups('messages','message_language_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Language  $language
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,Language $language)
    {
        $validation = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $language->title               	= $request->title;
            $language->value         		= $request->value;
            $language->status      			= $request->status;
            $language->direction            = $request->direction;
            $language->save();
            DB::commit();
            return response()->json(prepareResult(false, new LanguageResource($language), getLangByLabelGroups('messages','message_language_updated')), config('http_response.success'));
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
     * @param \App\Language $language
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Language $language)
    {
        if($language->title == 'english')
        {
            return response()->json(prepareResult(true, ['English language can not be deleted'], getLangByLabelGroups('messages','message_language_cannot_be_deleted')), config('http_response.success'));
        }
        else
        {  
            Label::where('language_id',$language->id)->delete();
            $language->delete();
        }

        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_language_deleted')), config('http_response.success'));
    }

    public function languagesImport(Request $request)
    {
        $import = Excel::import(new LanguagesImport(),request()->file('file'));

        return response(prepareResult(false, [], getLangByLabelGroups('messages','messages_languages_imported')), config('http_response.success'));
    }
}