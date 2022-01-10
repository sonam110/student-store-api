<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BucketGroup;
use App\Models\BucketGroupDetail;
use App\Models\BucketGroupAttribute;
use App\Models\BucketGroupAttributeDetail;
use App\Models\AttributeMaster;
use Str;
use DB;
use Auth;

class BucketGroupController extends Controller
{

    public function index(Request $request)
    {
        try
        {
            if(!empty($request->per_page_record))
            {
                $bucketGroups = BucketGroup::with('bucketGroupAttributes')->orderBy('created_at','DESC')->simplePaginate($request->per_page_record)->appends(['per_page_record' => $request->per_page_record]);
            }
            else
            {
                $bucketGroups = BucketGroup::with('bucketGroupAttributes')->orderBy('created_at','DESC')->get();
            }
            return response(prepareResult(false, $bucketGroups, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'group_name'        => 'required',
            'language_id'       => 'required',
            'type'              => 'required',
            'is_multiple'       => 'required|boolean'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        if(BucketGroup::where('group_name', $request->group_name)->count()>0)
        {
            return response(prepareResult(true, [], getLangByLabelGroups('messages','message_already_exist')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $bucketGroup = new BucketGroup;
            $bucketGroup->group_name    = $request->group_name;
            $bucketGroup->slug          = Str::slug($request->group_name);
            $bucketGroup->type          = $request->type;
            if($request->type=='text')
            {
                $bucketGroup->text_type          = $request->text_type;
            }
            $bucketGroup->is_multiple   = $request->is_multiple;
            $bucketGroup->save();
            if($bucketGroup)
            {
                $bucketGroupDetail = new BucketGroupDetail;
                $bucketGroupDetail->bucket_group_id = $bucketGroup->id;
                $bucketGroupDetail->language_id   = $request->language_id;
                $bucketGroupDetail->name          = $request->group_name;
                $bucketGroupDetail->save();

                foreach ($request->attributes_list as $key => $attribute) 
                {
                    if(!empty($attribute))
                    {
                        $attr = new BucketGroupAttribute;
                        $attr->bucket_group_id = $bucketGroup->id;
                        $attr->name = $attribute;
                        $attr->save();

                        $attrDetail = new BucketGroupAttributeDetail;
                        $attrDetail->bucket_group_attribute_id = $attr->id;
                        $attrDetail->language_id = $request->language_id;
                        $attrDetail->name = $attribute;
                        $attrDetail->save();
                    }
                }
            }
            DB::commit();
            
            return response()->json(prepareResult(false, $bucketGroup, getLangByLabelGroups('messages','messages_bucket_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function show($id)
    {
        try
        {
            $bucketGroup = BucketGroup::with('bucketGroupAttributes')->find($id);
            return response(prepareResult(false, $bucketGroup, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function update(Request $request, BucketGroup $bucketGroup)
    {
        $validation = Validator::make($request->all(), [
            'group_name'        => 'required',
            'language_id'       => 'required',
            'type'              => 'required',
            'is_multiple'       => 'required|boolean'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            $bucketGroup->group_name    = $request->group_name;
            $bucketGroup->slug          = Str::slug($request->group_name);
            $bucketGroup->type          = $request->type;
            if($request->type=='text')
            {
                $bucketGroup->text_type          = $request->text_type;
            }
            $bucketGroup->is_multiple   = $request->is_multiple;
            $bucketGroup->save();
            if($bucketGroup)
            {
                $bucketGroupDetail = BucketGroupDetail::where('bucket_group_id', $bucketGroup->id)->first();
                $bucketGroupDetail->bucket_group_id = $bucketGroup->id;
                $bucketGroupDetail->language_id   = $request->language_id;
                $bucketGroupDetail->name          = $request->group_name;
                $bucketGroupDetail->save();
            }
            DB::commit();
            return response()->json(prepareResult(false, $bucketGroup, getLangByLabelGroups('messages','messages_bucket_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function destroy(BucketGroup $bucketGroup)
    {
        $bucketGroup->delete();
        BucketGroupAttribute::where('bucket_group_id',$bucketGroup->id)->delete();
        AttributeMaster::where('bucket_group_id',$bucketGroup->id)->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_bucket_deleted')), config('http_response.success'));
    }

    public function attributeListByBucketGroup($bucketGroupId, $language_id)
    {
        try
        {
            $bucketGroupAttributes = BucketGroupAttribute::select('id','bucket_group_id','name')
                ->with(['attributeDetails' => function($q) use ($language_id) {
                        $q->select('id','bucket_group_attribute_id','language_id','name')
                        ->where('language_id', $language_id);
                    }])
                ->where('bucket_group_id', $bucketGroupId)
                ->get();
            return response(prepareResult(false, $bucketGroupAttributes, getLangByLabelGroups('messages','message_success_title')), config('http_response.success'));
        }
        catch (\Throwable $exception) 
        {
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function bucketAttributeCreate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'bucket_group_id'   => 'required|exists:bucket_groups,id',
            'language_id'       => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            foreach ($request->attributes_list as $key => $attribute) 
            {
                if(!empty($attribute))
                {
                    $attr = new BucketGroupAttribute;
                    $attr->bucket_group_id = $request->bucket_group_id;
                    $attr->name = $attribute;
                    $attr->save();

                    $attrDetail = new BucketGroupAttributeDetail;
                    $attrDetail->bucket_group_attribute_id = $attr->id;
                    $attrDetail->language_id = $request->language_id;
                    $attrDetail->name = $attribute;
                    $attrDetail->save();
                } 
            }
            DB::commit();
            
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_bucket_attributes_created')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function bucketAttributeUpdate(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'bucket_group_id'   => 'required|exists:bucket_groups,id',
            'language_id'       => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $bucketGroup = BucketGroup::find($request->bucket_group_id);
            $bucketGroup->group_name    = $request->group_name;
            $bucketGroup->slug          = Str::slug($request->group_name);
            $bucketGroup->type          = $request->type;
            if($request->type=='text')
            {
                $bucketGroup->text_type          = $request->text_type;
            }
            $bucketGroup->is_multiple   = $request->is_multiple;
            $bucketGroup->save();

            if(BucketGroupDetail::where('bucket_group_id',$request->bucket_group_id)->where('language_id',$request->language_id)->count() > 0)
            {
                $bucketGroupDetail = BucketGroupDetail::where('bucket_group_id',$request->bucket_group_id)->where('language_id',$request->language_id)->first();
                $bucketGroupDetail->bucket_group_id = $bucketGroup->id;
                $bucketGroupDetail->language_id     = $request->language_id;
                $bucketGroupDetail->name            = $request->group_name;
                $bucketGroupDetail->save();
            }
            else
            {
                $bucketGroupDetail = new BucketGroupDetail;
                $bucketGroupDetail->bucket_group_id = $bucketGroup->id; 
                $bucketGroupDetail->language_id     = $request->language_id;
                $bucketGroupDetail->name            = $request->group_name;
                $bucketGroupDetail->save();
            }
            

            BucketGroupAttribute::join('bucket_group_attribute_details', function ($join) {
                                        $join->on('bucket_group_attributes.id', '=', 'bucket_group_attribute_details.bucket_group_attribute_id');
                                    })
                                    ->where('bucket_group_attributes.bucket_group_id',$request->bucket_group_id)
                                    ->where('bucket_group_attribute_details.language_id',$request->language_id)
                                    ->delete();
            foreach ($request->attributes_list as $key => $attribute) 
            {
                if(!empty($attribute))
                { 
                    $attr = new BucketGroupAttribute;
                    $attr->bucket_group_id = $request->bucket_group_id;
                    $attr->name = $attribute;
                    $attr->save();

                    $attrDetail = new BucketGroupAttributeDetail;
                    $attrDetail->bucket_group_attribute_id = $attr->id;
                    $attrDetail->language_id = $request->language_id;
                    $attrDetail->name = $attribute;
                    $attrDetail->save();
                    
                } 
            }
            DB::commit();
            
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_bucket_attributes_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function bucketAttributeDestroy($id)
    {
        BucketGroupAttribute::find($id)->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_bucket_attribute_deleted')), config('http_response.success'));
    }

    public function createBucketGroupAttributeCategoryRelation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            // 'category_id'   => 'required|exists:category_masters,id',
            'bucket_groups' => 'required|array',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            foreach ($request->bucket_groups as $key => $grpId) {
                if(AttributeMaster::where('category_master_slug', $request->category_master_slug)->where('bucket_group_id', $grpId)->count()>0)
                { }
                else
                {
                    $attr = new AttributeMaster;
                    $attr->category_master_slug     = $request->category_master_slug;
                    $attr->bucket_group_id          = $grpId;
                    $attr->save();
                }
            }
            DB::commit();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_attribute_added')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

    public function updateBucketGroupAttributeCategoryRelation(Request $request)
    {
        $validation = Validator::make($request->all(), [
            // 'category_id'   => 'required|exists:category_masters,id',
            'bucket_groups' => 'required|array',
        ]);

        if ($validation->fails()) {
            return response(prepareResult(true, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }
        DB::beginTransaction();
        try
        {
            AttributeMaster::where('category_master_slug', $request->category_master_slug)->delete();
            foreach ($request->bucket_groups as $key => $grpId) {
                $attr = new AttributeMaster;
                $attr->category_master_slug     = $request->category_master_slug;
                $attr->bucket_group_id          = $grpId;
                $attr->save();
            }
            DB::commit();
            return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','messages_attribute_updated')), config('http_response.created'));
        }
        catch (\Throwable $exception)
        {
            DB::rollback();
            \Log::error($exception);
            return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
        }
    }

}
