<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use App\Models\CartDetail;
use App\Http\Resources\CartDetailResource;
use App\Models\ProductsServicesBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Str;
use DB;
use Auth;

class CartDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        try
        {
            $cartDetails = CartDetail::where('user_id',Auth::id())->with('product.user','product.user.shippingConditions','product.user.serviceProviderDetail','product.categoryMaster','product.subCategory','product.addressDetail','product.coverImage','product.productTags')->get();
            return response(prepareResult(false, $cartDetails, getLangByLabelGroups('messages','message_cart_list')), config('http_response.success'));
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
            'products_services_book_id'  => 'required',
            'quantity'                   => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $productsServicesBook = ProductsServicesBook::find($request->products_services_book_id); 

            if($productsServicesBook->is_on_offer == 1)
            {
                $price = $productsServicesBook->discounted_price;
            }
            else
            {
                $price = $productsServicesBook->discounted_price;
            }

            if($productsServicesBook->discount_type == 1)
            {
                $discount = $productsServicesBook->discount_value.'%';
            }
            else
            {
                $discount = $productsServicesBook->discount_value.'Rupees';
            }

            $quantity = $request->quantity;


            if(($productsServicesBook->type != 'service') && $productsServicesBook->quantity < $quantity)
            {
                return response()->json(prepareResult(true, ['quantity exceeded.Only '.$productsServicesBook->quantity.' left.'], getLangByLabelGroups('messages','message_quantity_exceeded')), config('http_response.bad_request'));
            }

            if(CartDetail::where('user_id',Auth::id())->where('products_services_book_id',$request->products_services_book_id)->count() > 0)
            {
                $cartDetail = CartDetail::where('user_id',Auth::id())->where('products_services_book_id',$request->products_services_book_id)->first();
                $quantity = $quantity + $cartDetail->quantity;

                $left_quantity = $productsServicesBook->quantity - $cartDetail->quantity;


                if(($productsServicesBook->type != 'service') && ($productsServicesBook->quantity < $quantity))
                {
                    return response()->json(prepareResult(true, ['quantity exceeded.Only '.$left_quantity.' left.'], getLangByLabelGroups('messages','message_quantity_exceeded')), config('http_response.bad_request'));
                }

                $cartDetail->user_id                    = Auth::id();
                $cartDetail->products_services_book_id  = $productsServicesBook->id;
                $cartDetail->sku                        = $productsServicesBook->sku;
                $cartDetail->price                      = $price;
                $cartDetail->discount                   = $discount;
                $cartDetail->quantity                   = $quantity;
                $cartDetail->sub_total                  = $quantity * $price;
                $cartDetail->item_status                = $request->item_status;
                $cartDetail->note_to_seller             = $request->note_to_seller;
                $cartDetail->save();
            }
            else
            {
                $cartDetail = new CartDetail;
                $cartDetail->user_id                    = Auth::id();
                $cartDetail->products_services_book_id  = $productsServicesBook->id;
                $cartDetail->sku                        = $productsServicesBook->sku;
                $cartDetail->price                      = $price;
                $cartDetail->discount                   = $discount;
                $cartDetail->quantity                   = $quantity;
                $cartDetail->sub_total                  = $quantity * $price;
                $cartDetail->item_status                = $request->item_status;
                $cartDetail->note_to_seller             = $request->note_to_seller;
                $cartDetail->save();
            }
            $cartDetail =CartDetail::where('id', $cartDetail->id)->with('product.user','product.user.serviceProviderDetail','product.categoryMaster','product.subCategory','product.addressDetail','product.coverImage','product.productTags')->first();

            // Notification Start

            $title = 'Product added to cart';
            $body =  'product '.$productsServicesBook->title.' is added to cart';
            $user = Auth::user();
            $type = 'Cart';
            $user_type = 'buyer';
            if($productsServicesBook->type == 'book')
            {
                $module = 'book';
            }
            else
            {
                $module = 'product_service';
            }

            if($user->is_minor==1)
            {
                pushNotification($title,$body,$user,$type,true,$user_type,$module,$cartDetail->id,'my-cart');
            }
            
            // Notification End
            
            DB::commit();
            return response()->json(prepareResult(false, $cartDetail, getLangByLabelGroups('messages','message_cart_created')), config('http_response.created'));
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
     * @param  \App\CartDetail  $cartDetail
     * @return \Illuminate\Http\Response
     */
    public function show(CartDetail $cartDetail)
    {
        return response()->json(prepareResult(false, $cartDetail, getLangByLabelGroups('messages','message_cart_list')), config('http_response.success'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CartDetail  $cartDetail
     * @return \Illuminate\Http\Response
     */
    
    public function update(Request $request,CartDetail $cartDetail)
    {
        $validation = Validator::make($request->all(), [
            'quantity' => 'required'
        ]);

        if ($validation->fails()) {
            return response(prepareResult(false, $validation->messages(), getLangByLabelGroups('messages','message_validation')), config('http_response.bad_request'));
        }

        DB::beginTransaction();
        try
        {
            $cartDetail->quantity					= $request->quantity;
            $cartDetail->save();

            $cartDetail =CartDetail::where('id', $cartDetail->id)->with('product.user','product.user.serviceProviderDetail','product.categoryMaster','product.subCategory','product.addressDetail','product.coverImage','product.productTags')->first();
            DB::commit();
            return response()->json(prepareResult(false, $cartDetail, getLangByLabelGroups('messages','message_cart_updated')), config('http_response.success'));
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
     * @param \App\CartDetail $cartDetail
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(CartDetail $cartDetail)
    {
        $cartDetail->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_cart_deleted')), config('http_response.success'));
    }

    public function emptyCart()
    {
        CartDetail::where('user_id',Auth::id())->delete();
        return response()->json(prepareResult(false, [], getLangByLabelGroups('messages','message_cart_deleted')), config('http_response.success'));
    }
} 