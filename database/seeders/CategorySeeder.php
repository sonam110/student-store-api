<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CategoryMaster;
use App\Models\CategoryDetail;
use App\Models\Brand;
use App\Models\ModuleType;
use Str;

class CategorySeeder extends Seeder
{
    
    public function run()
    {
        $moduleType1 = new ModuleType;
        $moduleType1->title                = 'Job';
        $moduleType1->slug                = 'job';
        $moduleType1->description         = 'module type 1 description';
        $moduleType1->status               = true;
        $moduleType1->save();

        $moduleType2 = new ModuleType;
        $moduleType2->title                = 'Service';
        $moduleType2->slug                = 'service';
        $moduleType2->description         = 'module type 2 description';
        $moduleType2->status               = true;
        $moduleType2->save();

        $moduleType3 = new ModuleType;
        $moduleType3->title                = 'Product';
        $moduleType3->slug                = 'product';
        $moduleType3->description         = 'module type 3 description';
        $moduleType3->status               = true;
        $moduleType3->save();

        $moduleType4 = new ModuleType;
        $moduleType4->title                = 'Book';
        $moduleType4->slug                = 'book';
        $moduleType4->description         = 'module type 4 description';
        $moduleType4->status               = true;
        $moduleType4->save();

        $moduleType5 = new ModuleType;
        $moduleType5->title                = 'Contest';
        $moduleType5->slug                = 'contest';
        $moduleType5->description         = 'module type 5 description';
        $moduleType5->status               = true;
        $moduleType5->save();

        //Jobs
        $categories[1] = 'Accounting & Consulting, Admin Support, Customer Service, Data Science & Analytics, Design & Creative, Engineering & Architecture, IT & Networking, Legal, Sales & Marketing, Translation, Web - Mobile & Software Development, Writing';
        $subCategories[1][0] = 'Accounting, Bookkeeping, Business Analysis, Financial Analysis & Modeling, Financial Management / CFO, HR Administration, Instructional Design, Management Consulting, Recruiting, Tax Preparation, Training & Development';
        $subCategories[1][1] = 'Data Entry, Online Research, Order Processing, Project Management, Transcription, Virtual Assistance,';
        $subCategories[1][2] = 'Customer Service, Technical Support';
        $subCategories[1][3] = 'A/B Testing, Bandits    Multi-armed , Data Analytics, Data Engineering, Data Extraction, Data Mining, Data Processing, Data Visualization, Deep Learning, Experimentation & Testing, Knowledge Representation, Machine Learning';
        $subCategories[1][4] = '2D Animation, 3D Animation, Actor, Art Direction, Audio Editing, Audio Production, Brand Identity Design, Brand Strategy, Cartoonist, Creative Direction, Editorial Design, Exhibit Design, Fashion Design, Graphic Design, Illustration, Image Editing , Motion Graphics Design, Musician, Music Composition, Music Production, Photography, Presentation Design, Scriptwriting, Store Design, Videographer, Video Editing, Video Production, Vocalist, Voice Talent, VR & AR Design';
        $subCategories[1][5] = '3D Modeling , 3D Rendering, 3D Visualization, Architecture, BIM Modeling, Biology, CAD  Computer-aided design (CAD), Chemical Engineering, Chemistry, Civil Engineering, Electrical Engineering, Electronic Engineering, Energy Management & Modeling, Engineering Tutoring, Jewelry Design, Landscape Design, Oil & Gas Engineering, Process Engineering, Quantity Surveying, Structural Engineering, HVAC & MEP Design, Hydraulics Engineering, Industrial Design, Interior Design, Logistics & Supply Chain Management, PCB Design, Physics, Product Design, Science Tutoring, Solar Energy, Wind Energy';
        $subCategories[1][6] = 'Database Administration, DevOps Engineering, Information Security, Network Administration, Network Security, Solutions Architecture, System Administration, Systems Architecture, Systems Compliance, Systems Engineering';
        $subCategories[1][7] = 'Business & Corporate Law, General Counsel, Immigration Law, Intellectual Property Law, Regulatory Law, Securities & Finance Law, Tax Law';
        $subCategories[1][8] = 'Campaign Management, Community Management, Content Strategy, Digital Marketing, Email Marketing, Lead Generation, Marketing Automation, Marketing Strategy, Market Research, Public Relations, Search Engine Marketing, Search Engine Optimization, Social Media Marketing, Social Media Strategy, Telemarketing';
        $subCategories[1][9] = 'Language Localization, Language Tutoring, Legal Translation, Medical Translation, Technical Translation, Translation';
        $subCategories[1][10] = 'AR / VR Development, Automation QA, Back-End Development, CMS Customization, CMS Development   , Database Development, Desktop Software Development, Ecommerce Development, Emerging Tech, Firmware Development, Front-End Development, Full Stack Development, Functional QA, Game Development, Mobile App Development, Mobile Design, Mobile Game Development, Product Management, Prototyping, Scripting & Automation, Scrum Master, User Research, UX/UI Design, Web Design';
        $subCategories[1][11] = 'Business Writing, Career Coaching, Content Writing, Copywriting, Creative Writing, Editing & Proofreading, Ghostwriting, Grant Writing, Technical Writing, Writing Tutoring';

        //Service
        $categories[2] = 'Economics & Law,Interior & Architect,Webb, It & Design,Teaching/Tutor,Others,Gaming,Music & Audio,Video & Animation,Writing & Translation,Digital Marketing,Graphics & Design';

        $subCategories[2][0] = 'Financial statements, Declaration, Financial management for Brf., Factoring & Debt Collection, Payroll management, Current accounting, Registration of companies, Audit, Finance - Other, Business law, Criminal law, Family law';
        $subCategories[2][1] = 'Architectural drawing, Building permit drawing, Construction drawing, Architect - Other, Home Decor & Homestyling, Office furniture';
        $subCategories[2][2] = 'Graphic Design, IT support, Mobile development, Programming, Web design, Web development, Web / IT & Design - Other';
        $subCategories[2][3] = 'Math, Physics, chemistry, Sciences / Biographic, Languages, History, Geographic, Law, Engineers, Medical, International Sciences magazines, White paper, Researching';
        $subCategories[2][4] = 'Startup, Exam, Application form, Governmental , Business plan, Project management, Mentor, Virtual Assitant, Consulting, Branding service, Financial  Consulting, Business Consulting, Presentations, Career Counseling, Flyer Distribution, Lead Generation';
        $subCategories[2][5] = 'Online Lesson, Life Coaching, Fitness Lessons, Personal Stylists, Cooking Lessons, Craft Lessons, Arts & Crafts, Health, Nutrition & Fitness, Family & Genealogy, Greeting Cards & Videos, Your Message On..., Viral Videos, Celebrity Impersonators, Collectibles, Traveling';
        $subCategories[2][6] = 'Voice Over, Mixing & Mastering, Producers & Composers, Singers & Vocalists, Session Musicians, Online Music Lessons, Songwriters, Beat Making, Podcast Editing, Audiobook Production, Audio Ads Production, Sound Design, Dialogue Editing, Music Transcription, Vocal Tuning, Jingles & Intros, DJ Drops & Tags, DJ Mixing, Remixing & Mashups';
        $subCategories[2][7] = 'Whiteboard & Animated Explainers, Video Editing, Short Video Ads, Animated GIFs, Logo Animation, Intros & Outros, App & Website Previews, Live Action Explainers, Character Animation, 3D Product Animation, Spokespersons Videos, Unboxing Videos, Lyric & Music Videos, eLearning Video Production, Subtitles & Captions, Visual Effects, Animation for Kids, Slideshows Videos, Screencasting Videos, Game Trailers, Book Trailers, Animation for Streamers, Article to Video, Real Estate Promos, Product Photography, Local Photography';
        $subCategories[2][8] = 'Articles & Blog Posts, Translation, Proofreading & Editing, Website Content, Book & eBook Writing, Brand Voice & Tone, UX Writing, Resume Writing, Cover Letters, Technical Writing, LinkedIn Profiles, White Papers, Podcast Writing, Case Studies, Social Media Copy, Ad Copy, Sales Copy, Press Releases, Product Descriptions, Scriptwriting, Book Editing, Email Copy, Speechwriting, Business Names & Slogans, Creative Writing, eLearning Content Development, Beta Reading, Grant Writing, Transcripts, Legal Writing, Online Language Lessons, Research & Summaries';
        $subCategories[2][9] = 'Social Media Marketing, SEO, Social Media Advertising, Public Relations, Content Marketing, Podcast Marketing, Video Marketing, Email Marketing, Crowdfunding, SEM, Marketing Strategy, Surveys, Web Analytics, Book & eBook Marketing, Influencer Marketing, Community Management, Local SEO, Domain Research, E-Commerce Marketing, Affiliate Marketing, Mobile Marketing & Advertising, Music Promotion, Web Traffic';
        $subCategories[2][10] = 'Logo Design, Brand Style Guides, Game Art, Graphics for Streamers, Business Cards & Stationery, Illustration, Pattern Design, Brochure Design, Poster Design, Signage Design, Flyer Design, Book Design, Album Cover Design, Podcast Cover Art, Packaging Design, Storyboards, Web & Mobile Design, Social Media Design, AR Filters & Lenses, Postcard Design, Catalog Design, Menu Design, Invitation Design, Portraits & Caricatures, Cartoons & Comics, Tattoo Design, Web Banners, Photoshop Editing, Architecture & Interior Design, Landscape Design, Building Information Modeling, Character Modeling, Industrial & Product Design, Trade Booth Design, Fashion Design, T-Shirts & Merchandise, Presentation Design, Infographic Design, Resume Design, Car Wraps, Vector Tracing, Twitch Store';


        //Product
        $categories[3] = 'Vehicle,For the home,Personal,Electronics,Sparetime Hobby,Business,Residence,Library';

        $subCategories[3][0] = 'Cars,Car parts & car accessories,Boats,Boat parts & accessories,Caravans & mobile homes,Mopeds & A-tractor,Motorcycles,Motorcycle parts & accessories,Truck,truck & construction,Forestry & agricultural machinery,Snowmobiles,Snowmobile parts & accessories';


        $subCategories[3][1] = 'Construction & garden,Furniture & home decor,Housewares & appliances,Tool,Craftsman';


        $subCategories[3][2] = 'Clothes shoes,Accessories & watches,Children\'s clothes & shoes,Children\'s items & toys';



        $subCategories[3][3] = 'Computers & Video Games,Sound & video,Phones & accessories';



        $subCategories[3][4] = 'Experiences & fun,Bicycles,Animal,Hobbies & collectibles,Hunting & fishing,Music equipment,Sports & leisure equipment';



        $subCategories[3][5] = 'Business transfers,Equipment & machines,Premises & properties,Services';


        $subCategories[3][6] = 'Apartments,Holiday accommodation';



        $subCategories[3][7] = 'Sale & Purchase (New,Old) Books,Rent Books,Borrowing Books,Gifts Books,Quote from Books';

        //Books
        $categories[4] = 'Arts & Music, Biographies, Business, Comics, Computers & Tech, Cooking';

        $subCategories[4][0] = 'Art History, Calligraphy, Drawing, Fashion, Film';
        $subCategories[4][1] = 'Ethnic & Cultural, Europe, Historical, Leaders & Notable People, Military';
        $subCategories[4][2] = 'Careers, Economics, Finance, Industries, International';
        $subCategories[4][3] = 'Comic Books, Comic Strips, Dark Horse, DC Comics, Fantasy';
        $subCategories[4][4] = 'Apple, CAD, Certification, Computer Science, Databases';
        $subCategories[4][5] = 'Asian, Baking, BBQ, Culinary Arts, Desserts';

        $categories[5] = 'Seminar, Cultural Programs, Quiz Contest';
        $subCategories[5][0] = 'Business Seminar, Spiritual Seminar';
        $subCategories[5][0] = 'Dance, Singing';
        $subCategories[5][0] = 'KBC, Online Quiz';

        $moduleTypeId[1] = $moduleType1->id;
        $moduleTypeId[2] = $moduleType2->id;
        $moduleTypeId[3] = $moduleType3->id;
        $moduleTypeId[4] = $moduleType4->id;
        $moduleTypeId[5] = $moduleType5->id;


        for ($i=1; $i <= 5 ; $i++) { 
            if(!empty($categories[$i])) {
                foreach (explode(',', $categories[$i]) as $key => $value) {
                    $uuid = (string) \Uuid::generate(4);
            		$cat = new CategoryMaster;
                    $cat->id = $uuid;
        	        $cat->module_type_id = $moduleTypeId[$i];
        	        $cat->title = $value;
        	        $cat->slug = $uuid.'-'.Str::slug($value);
        	        $cat->status = 1;
        	        $cat->save();
        	        if($cat)
        	        {
        	        	$catDetail = new CategoryDetail;
        		        $catDetail->category_master_id = $cat->id;
        		        $catDetail->language_id = 1;
        		        $catDetail->is_parent = 1;
        		        $catDetail->title = $value;
        		        $catDetail->slug = $uuid.'-'.Str::slug($value);
        		        $catDetail->status = 1;
        		        $catDetail->save();
        		    }
                    
                    foreach (explode(',', @$subCategories[$i][$key]) as $newKey => $subCat) {
                        if(!empty($subCat))
                        {
                            $newUuid = (string) \Uuid::generate(4);
                            $childCat = new CategoryMaster;
                            $childCat->id = $newUuid;
                            $childCat->module_type_id = $moduleTypeId[$i];
                            $childCat->category_master_id = $cat->id;
                            $childCat->title = $subCat;
                            $childCat->slug = $newUuid.'-'.Str::slug($subCat);
                            $childCat->status = 1;
                            $childCat->save();
                            if($childCat)
                            {
                                $childCatDetail = new CategoryDetail;
                                $childCatDetail->category_master_id = $cat->id;
                                // $childCatDetail->category_master_id = $childCat->id;
                                $childCatDetail->language_id = 1;
                                $childCatDetail->title = $subCat;
                                $childCatDetail->slug = $newUuid.'-'.Str::slug($subCat);
                                $childCatDetail->status = 1;
                                $childCatDetail->save();
                            }
                        }
        		    }	
                }
            }
        }
    }
}
