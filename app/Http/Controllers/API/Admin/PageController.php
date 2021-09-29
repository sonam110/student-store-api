<?php

namespace App\Http\Controllers\API\Admin;


use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;

class PageController extends Controller
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
                $pages = Page::with('language:id,title')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $pages = Page::with('language:id,title')->get();
            }
            return response(prepareResult(false, $pages, getLangByLabelGroups('messages','message_page_list')), config('http_response.success'));
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
            $page = new Page;

            if($request->is_existing == true)
            {
                $page->slug                 = $request->slug;
            }
            else
            {

                $page->slug                 = Str::slug($request->title);
            }

            $page->language_id          = $request->language_id;
            $page->title                = $request->title;
            $page->description          = $request->description;
            $page->image_path           = $request->image_path;
            $page->status               = $request->status;
            $page->is_header_menu       = $request->is_header_menu;
            $page->is_footer_menu       = $request->is_footer_menu;
            $page->footer_section       = $request->footer_section;
            $page->meta_title           = $request->meta_title;
            $page->meta_keywords        = $request->meta_keywords;
            $page->meta_description     = $request->meta_description;
            $page->save();

            DB::commit();
            return response()->json(prepareResult(false, $page, getLangByLabelGroups('messages','message_page_created')), config('http_response.created'));
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
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return response()->json(prepareResult(false, $page, getLangByLabelGroups('messages','message_page_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,Page $page)
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
            $page->language_id          = $request->language_id;
            $page->title                = $request->title;
            $page->slug                 = Str::slug($request->title);
            $page->description          = $request->description;
            $page->image_path           = $request->image_path;
            $page->status               = $request->status;
            $page->is_header_menu       = $request->is_header_menu;
            $page->is_footer_menu       = $request->is_footer_menu;
            $page->footer_section       = $request->footer_section;
            $page->meta_title           = $request->meta_title;
            $page->meta_keywords        = $request->meta_keywords;
            $page->meta_description     = $request->meta_description;
            $page->save();
            DB::commit();
            return response()->json(prepareResult(false, $page, getLangByLabelGroups('messages','message_page_updated')), config('http_response.success'));
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
     * @param \App\Page $page
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Page $page)
    {
        $page->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_page_deleted')), config('http_response.success'));
    }
}