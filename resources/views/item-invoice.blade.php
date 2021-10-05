<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>INVOICE</title>

    <style>
    .invoice-box {
        margin: auto;
        padding: 10px;
        border: 1px solid #eee;
        font-size: 16px;
        line-height: 24px;
        font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        color: #555;
    }
    .text-center{
    	text-align: center!important;
    }
    .uppercase{
    	text-transform: uppercase;
    }
    .invoice-box table {
        width: 100%;
        line-height: inherit;
        text-align: left;
    }

    .invoice-box table td {
        padding: 5px;
        vertical-align: top;
    }

    .invoice-box table tr td:nth-child(2) {
        text-align: right;
    }

    .invoice-box table tr.top table td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.top table td.title {
        font-size: 45px;
        line-height: 45px;
        color: #333;
    }

    .invoice-box table tr.information table td {
        padding-bottom: 10px;
    }

    .invoice-box table tr.heading td {
        background: #eee;
        border-bottom: 1px solid #ddd;
        font-weight: bold;
    }

    .invoice-box table tr.details td {
        padding-bottom: 20px;
    }

    .invoice-box table tr.item td{
        border-bottom: 1px solid #eee;
    }

    .invoice-box table tr.item.last td {
        border-bottom: none;
    }

    .invoice-box table tr.total td:nth-child(2) {
        border-top: 2px solid #eee;
        font-weight: bold;
    }

    @media only screen and (max-width: 600px) {
        .invoice-box table tr.top table td {
            width: 100%;
            display: block;
            text-align: center;
        }

        .invoice-box table tr.information table td {
            width: 100%;
            display: block;
            text-align: center;
        }
    }

    /** RTL **/
    .rtl {
        direction: rtl;
        font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
    }

    .rtl table {
        text-align: right;
    }

    .rtl table tr td:nth-child(2) {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="4">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{!! $appsetting->logo_thumb_path !!}" class="" height="60px" width="150px">
                            </td>

                            <td>
                                <strong>{!! $appsetting->app_name !!}</strong><br>
                                {!! $appsetting->support_email !!}<br>
                                {!! $appsetting->support_contact_number !!}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="4">
                    <table>
                        <tr class="heading">
                            <td colspan="2">
                                <center><span class="uppercase">Order Information</span></center>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <br><br>
                                <strong>Seller Info</strong><br>
                                @if($order->productsServicesBook)
                                    @php $seller = $order->productsServicesBook->user @endphp
                                @elseif($order->contestApplication)
                                    @php $seller = $order->contestApplication->contest->user @endphp
                                @elseif($order->package)
                                    @php $seller = null @endphp
                                    {!! $appsetting->app_name !!}<br>
                                    {!! $appsetting->support_email !!}<br>
                                    {!! $appsetting->support_contact_number !!}
                                @endif

                                @if(!empty($seller))
                                {{\mervick\aesEverywhere\AES256::decrypt($seller->first_name, env('ENCRYPTION_KEY'))}} {{ !empty($seller->last_name) ? \mervick\aesEverywhere\AES256::decrypt($seller->last_name, env('ENCRYPTION_KEY')) : ''}} <br>
                                {{ !empty($seller->contact_number) ? \mervick\aesEverywhere\AES256::decrypt($seller->contact_number, env('ENCRYPTION_KEY')) : ''}}
                                <br>

                                {{$order->order->full_address}}
                                @endif
                            </td>
                            <td width="50%">
                                <strong>Order No.</strong> :#{{$order->order->order_number}}<br>
                                <strong>Date:</strong> {{date('Y-m-d', strtotime($order->order->created_at))}}

                                <br><br>
                                <strong>Buyer Info</strong><br>
                                {{\mervick\aesEverywhere\AES256::decrypt($order->order->first_name, env('ENCRYPTION_KEY'))}} {{ !empty($order->order->last_name) ? \mervick\aesEverywhere\AES256::decrypt($order->order->last_name, env('ENCRYPTION_KEY')) : ''}} <br>
                                {{ !empty($order->order->contact_number) ? \mervick\aesEverywhere\AES256::decrypt($order->order->contact_number, env('ENCRYPTION_KEY')) : ''}}
                                <br>

                                {{$order->order->full_address}}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Item
                </td>

                <td>
                    <center>Quantity</center>
                </td>

                <td>
                    <center>Price</center>
                </td>

                <td>
                    <center>Total</center>
                </td>
            </tr>

            <tr class="item">
                <td>
                    {{($order->productsServicesBook) ? $order->productsServicesBook->title : ''}}

                    {{($order->package) ? $order->package->slug : ''}}

                    {{($order->contestApplication) ? $order->contestApplication->contest_title : ''}}
                </td>
                <td>
                    <center>
                        {{$order->quantity}}
                    </center>
                </td>
                <td>
                    <center>
                        {{$order->price}} Kr
                    </center>
                </td>

                <td>
                    <center>
                        {{$order->quantity * $order->price}} Kr
                    </center>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td colspan="2"><strong>Total:</strong> </td>
                <td>
                   <strong><center>{{$order->quantity * $order->price}} Kr</center></strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
