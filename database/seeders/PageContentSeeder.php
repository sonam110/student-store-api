<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PageContent;
use DB;
use Str;

class PageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $page = Page::where('slug', 'about-us')->where('language_id', 1)->first();
        if($page)
        {

            //About Us
            DB::table('page_contents')->where('page_id', $page->id)->delete();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Make it Reachable';
            $pageContent->section_name = 'banner_section';
            $pageContent->description = 'Student store is an easy and secure platform for people to discover and shop the products, services, and books they love. With fast delivery, easy payment and return options and a 24-hour customer service, find everything you need at competitive prices.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Our Vision';
            $pageContent->section_name = 'our-vision';
            $pageContent->description = 'To be Student’s most customer-centric company, where customers can find and discover anything they might want to buy online.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Our Mission';
            $pageContent->section_name = 'our-mission';
            $pageContent->description = 'Make It Reachable To Every Customer And Offer The Lowest Possible Prices, The Best Available Selection, And The Utmost Convenience.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Our Value';
            $pageContent->section_name = 'sustainability';
            $pageContent->description = 'When we are no longer using the goods we have in our possession, it is important for it to move on to someone who can. Students generate some income while removing clutter and unused items, ultimately participating in sustainability practices.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Our Value';
            $pageContent->section_name = 'sociality';
            $pageContent->description = 'Students Are Provided Ways To Interact With Other Students Across The Nation. Interactions Through Exchanging Goods And Knowledge Between Each Other. Participating In Competitions To Gain Points In The Loyalty Program.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Our Value';
            $pageContent->section_name = 'peace';
            $pageContent->description = 'Sharing Knowledge And Goods Is A Way To Show Caring And Love To Others, Which Inherently Builds A Sense Of Peace.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Contact Us';
            $pageContent->section_name = 'peace';
            $pageContent->description = 'We are here for you. Contact us for more help';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = "Call Us";
            $pageContent->button_link = "https://www.studentstore.se/contact-us";
            $pageContent->save();
        }

        // Why to join us
        $page = Page::where('slug', 'why-join-us')->where('language_id', 1)->first();
        if($page)
        {

            //About Us
            DB::table('page_contents')->where('page_id', $page->id)->delete();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Student Store is a total solutions platform for students and providers.';
            $pageContent->section_name = 'banner_section';
            $pageContent->description = 'Generating Income/Profit<br>
                                        Saving Your Time<br>
                                        Participating in Sustainability<br>
                                        Socializing with Others';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

            $pageContent = new PageContent;
            $pageContent->language_id = 1;
            $pageContent->page_id = $page->id;
            $pageContent->title = 'Student store';
            $pageContent->section_name = 'second_section';
            $pageContent->description = 'Is the main gateway and comprehensive solution to all world outlets, through which you can get discounts assigned to you as a student of the most famous international brands, as well as your comprehensive solution in obtaining all services in all areas such as software development, writing, data entry, design, and access to Engineering, Science, Sales, Marketing, Accounting, Legal Services, Clubs, Health, Work, and Study Opportunities All Over the World.';
            $pageContent->image_path = "http://localhost:3000/static/media/banner.afc9045c.jpg";
            $pageContent->icon_name = null;
            $pageContent->button_text = null;
            $pageContent->button_link = null;
            $pageContent->save();

        }
    }
}
