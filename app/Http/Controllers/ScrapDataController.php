<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Goutte\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use App\Models\ScrapDataUrl;
use App\Models\CategoryMaster;

class ScrapDataController extends Controller
{
    public function getAllScrappingUrl()
    {
        $categories = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug','vat')
        ->with(['categoryParent' => function($q) use ($language_id) {
                $q->select('id','category_master_id','title','description')
                ->where('language_id', $language_id)
                ->where('is_parent', 1);
            }])
        ->where('status', '1')
        ->orderBy('created_at','DESC')
        ->where('category_master_id', null)
        ->where('module_type_id', $moduleId)
        ->get();
                
        $data = ScrapDataUrl::get();
        return View('get-all-scrapping-url', compact('data'));
    }

    public function postAllScrappingUrl(Request $request)
    {
        $category = $request->category;
        $subcategory = $request->subcategory;
        $client = new Client();
        $crawler = $client->request('GET', $request->url);

        $allLinks[] = $crawler->filter('.image > a')->each(function ($node) {
            return $node->attr('href');
        });

        $totalRecord = $crawler->filter('.inner .results')->each(function ($node) {
            return preg_replace("/[^0-9]/", "", $node->text()); 
        });
        $loop = ceil($totalRecord[0] / 20);

        for ($i=2; $i <= $loop; $i++) {
            $crawler = $client->request('GET', $request->url.'?page='.$i);
            $allLinks[] = $crawler->filter('.image > a')->each(function ($node) {
                return $node->attr('href');
            });
        }

        foreach ($allLinks as $key => $value) 
        {
            foreach ($value as $nkey => $link) 
            {
                if(ScrapDataUrl::where('url', $link)->count()<1)
                {
                    $insert = new ScrapDataUrl;
                    $insert->category = $category;
                    $insert->subcategory = $subcategory;
                    $insert->url = $link;
                    $insert->save();
                }
            }
        }
        return redirect()->route('get-all-scrapping-url');
    }
}
