<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use App\Models\ScrapDataUrl;
use App\Models\ProductsServicesBook;
use App\Models\ProductImage;
use App\Models\ProductTag;
use App\Models\CategoryMaster;
use App\Models\User;
use DB;
use Str;
use Log;

class ScrapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrap:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $getScrapDataUrls = ScrapDataUrl::whereNull('read_at')->get();
        $user_id = '939fa9ca-3b9c-4e89-a72f-31502089549b';
        $address_detail_id = '53f0be33-90fe-4cc2-a834-9e4eb9a80f97';
        foreach($getScrapDataUrls as $key => $urlData)
        {
            $jsonPrepare = [];
            $url = $urlData->url;
            $title = null;
            $selling_price = null;
            $images = [];
            $productInfo = [];
            $description = null;

            $client = new Client();
            $crawler = $client->request('GET', $url);

            $title = $crawler->filter('h1')->each(function ($node) {
                return $node->text();
            });

            $selling_price = $crawler->filter('.price-recommended')->each(function ($node) {
                $price = $node->text();
                $actual_price = preg_replace("/[^0-9]/", "", $price);
                return ($actual_price/100);
            });

            $images[] = $crawler->filter('img')->each(function ($node) {
                return $node->attr('src');
            });

            $productInfo = $crawler->filter('#product-card-bottom')->each(function ($node) {
                $specification = $node->text();
                $packaging_dimensions = $this->get_string_between($specification, 'Förpackningsmått (LxBxH)', 'Bruttovikt');
                $gross_weight = $this->get_string_between($specification, 'Bruttovikt ', 'Beskrivning');
                $species_no = $this->get_string_between($specification, 'Art. nr:', 'EAN-kod');
                $EAN_code = $this->get_string_between($specification, 'EAN-kod:', 'För hel kartong beställ');
                $for_whole_cartons_order= $this->get_string_between($specification, 'För hel kartong beställ: ', ' ');
                $productInfo = [
                    'packaging_dimensions' => $packaging_dimensions,
                    'gross_weight' => $gross_weight,
                    'species_no' => $species_no,
                    'EAN_code' => $EAN_code,
                    'for_whole_cartons_order' => $for_whole_cartons_order
                ];

                return $productInfo;
            });

            $description = $crawler->filter('.show-for-large > .tabs-content-description')->each(function ($node) {
                return $node->html();
            });

            $description = trim(@$description[0],chr(0xC2).chr(0xA0));
            if(sizeof($selling_price)>0 && sizeof($title)>0)
            {
                $jsonPrepare['products'] = [
                    'url' => $url,
                    'title' => $title[0],
                    'selling_price' => $selling_price[0],
                    'product_info' => $productInfo,
                    'images' => $images,
                    'description' => $description,
                ];
            }
            $urlData->read_at = date('Y-m-d H:i:s');
            $urlData->save();

            if(sizeof($selling_price)>0 && sizeof($title)>0)
            {
                $getCommVal = updateCommissions($selling_price[0],0,0,0,$urlData->vat,$user_id,'product');

                $products = new ProductsServicesBook;
                $products->user_id = $user_id;
                $products->address_detail_id = $address_detail_id;
                $products->category_master_id = $urlData->category;
                $products->sub_category_slug = $urlData->subcategory;
                $products->type = 'product';
                $products->title = $title[0];
                $products->slug = Str::slug($title[0]);
                $products->gtin_isbn = @$productInfo['EAN_code'];
                $products->sku = @$productInfo['species_no'];
                $products->quantity = (@$productInfo['for_whole_cartons_order']==null) ? 100 : @$productInfo['for_whole_cartons_order'];
                $products->basic_price_wo_vat = $selling_price[0];
                $products->is_on_offer = 0;
                $products->discount_type = 0;
                $products->discount_value = 0;

                $products->delivery_type = 'deliver_to_location';

                $products->price = $getCommVal['price_with_all_com_vat'];
                $products->discounted_price = $getCommVal['totalAmount'];

                $products->vat_percentage = $urlData->vat;
                $products->vat_amount = $getCommVal['vat_amount'];
                $products->ss_commission_percent = $getCommVal['ss_commission_percent'];
                $products->ss_commission_amount = $getCommVal['ss_commission_amount'];
                $products->cc_commission_percent_all = $getCommVal['totalCCPercent'];
                $products->cc_commission_amount_all = $getCommVal['totalCCAmount'];
                $products->short_summary = Str::words(strip_tags($description), '50');
                $products->description = $description;
                $products->is_published = 1;
                $products->published_at = date('Y-m-d H:i:s');
                $products->status = 2;
                $products->save();
                if(is_array($images[0]) && sizeof($images[0])>0)
                {
                    $count = 1;
                    foreach ($images[0] as $key => $image) {
                        if(str_contains($image, '120x0') || str_contains($image, 'ab-logo'))
                        {
                            // thumb image
                        }
                        else
                        {
                            $productImage = new ProductImage;
                            $productImage->products_services_book_id = $products->id;
                            $productImage->image_path = 'https://order.se'.$image;
                            $productImage->thumb_image_path = 'https://order.se'.$image;
                            $productImage->cover = ($count==1) ? 1 : 0;
                            $productImage->save();
                            $count++;
                        }
                    }
                }
            }
        }
        echo 'done';
    }

    private function get_string_between($string, $start, $end)
    {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }
}
