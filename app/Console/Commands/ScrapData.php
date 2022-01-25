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
        $getScrapDataUrls = ScrapDataUrl::whereNull('read_at')->limit(1)->get();
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

            $description = trim($description[0],chr(0xC2).chr(0xA0));

            $jsonPrepare['products'] = [
                'url' => $url,
                'title' => $title[0],
                'selling_price' => $selling_price[0],
                'product_info' => $productInfo,
                'images' => $images,
                'description' => $description,
            ];
            dd($jsonPrepare['products']);
        }
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
