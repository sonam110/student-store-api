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
            ->with(['categoryParent' => function($q) {
                $q->select('id','category_master_id','title','description')
                ->where('language_id', 1)
                ->where('is_parent', 1);
            }])
        ->where('category_masters.status', 1)
        ->where('category_master_id', null)
        ->where('module_type_id','8ebd6d86-767e-40b8-b784-f0e90712d1c5')
        ->orderBy('title','ASC')
        ->get();

        $data = ScrapDataUrl::get();
        return View('get-all-scrapping-url', compact('data', 'categories'));
    }

    public function subCategoryList($catId, $language_id)
    {
        $subcategory = CategoryMaster::select('id', 'title', 'category_master_id', 'module_type_id','slug','vat')
            ->with(['categoryDetails' => function($q) use ($language_id) {
                    $q->select('id','category_master_id','title','description','slug')
                    ->where('language_id', $language_id)
                    ->where('is_parent', 0);
                }])
            ->where('status', '1')
            ->orderBy('created_at','DESC')
            ->where('category_master_id', null)
            ->where('id', $catId)
            ->first();
        ?>
        <select name="subcategory" id="subcategory" required class="form-control">
        <?php
        foreach ($subcategory->categoryDetails as $key => $cat) 
        {
            ?>
            <option value="<?php echo $cat->id; ?>"><?php echo $cat->title; ?></option>
            <?php
        }
        ?>
        </select>
        <?php
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
