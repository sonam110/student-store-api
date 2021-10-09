<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>INVOICE</title>

    <style>
    .invoice-box {
        max-width: 800px;
        margin: auto;
        padding: 10px;
        border: 1px solid #eee;
        box-shadow: 0 0 10px rgba(0, 0, 0, .15);
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
                                <img src="{!! $appsetting->logo_thumb_path !!}" class="" height="80px" width="200px">
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
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td width="50%">
                                <strong>Buyer Info</strong><br>
                                {{\mervick\aesEverywhere\AES256::decrypt($order->first_name, env('ENCRYPTION_KEY'))}} {{ !empty($order->last_name) ? \mervick\aesEverywhere\AES256::decrypt($order->last_name, env('ENCRYPTION_KEY')) : ''}} <br>
                                {{ !empty($order->contact_number) ? \mervick\aesEverywhere\AES256::decrypt($order->contact_number, env('ENCRYPTION_KEY')) : ''}}
                                <br>

                                @if($order->addressDetail)
                                    {{$order->addressDetail->full_address}}
                                @endif
                            </td>
                            <td>
                                Order No. :#{{$order->order_number}}<br>
                                Date: {{date('Y-m-d', strtotime($order->created_at))}}
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
            @php
                $student_store_commission = 0;
                $cool_company_commission = 0;
                $amount_transferred_to_vendor = 0;
            @endphp
            @foreach($order->orderItems as $item)
            @php
                $student_store_commission += $item->student_store_commission;
                $cool_company_commission += $item->cool_company_commission;
                $amount_transferred_to_vendor += $item->amount_transferred_to_vendor;
            @endphp
            <tr class="item">
                <td>
                    {{($item->productsServicesBook) ? $item->productsServicesBook->title : ''}}

                    {{($item->package) ? $item->package->slug : ''}}

                    {{($item->contestApplication) ? $item->contestApplication->contest_title : ''}}
                </td>
                <td>
                    <center>
                        {{$item->quantity}}
                    </center>
                </td>
                <td>
                    <center>
                        {{$item->price}} Kr
                    </center>
                </td>

                <td>
                    <center>
                        {{$item->quantity * $item->price}} Kr
                    </center>
                </td>
            </tr>
            @endforeach
            <tr class="total">
                <td></td>
                <td colspan="2"><strong>Total Student Store Commission:</strong> </td>
                <td>
                   <strong><center>{{ $student_store_commission }} Kr</center></strong>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td colspan="2"><strong>Total Cool Company Commission:</strong> </td>
                <td>
                   <strong><center>{{ $cool_company_commission }} Kr</center></strong>
                </td>
            </tr>

            <tr class="total">
                <td></td>
                <td colspan="2"><strong>Total Amount Transferred to Vendor:</strong> </td>
                <td>
                   <strong><center>{{ $amount_transferred_to_vendor }} Kr</center></strong>
                </td>
            </tr>
            <tr class="total">
                <td></td>
                <td colspan="2"><strong>Total Order Amount:</strong> </td>
                <td>
                   <strong><center>{{$order->grand_total}} Kr</center></strong>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
