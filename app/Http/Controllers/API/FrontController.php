<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UserType;
use App\Models\User;
use App\Models\ServiceProviderTypeDetail;
use App\Models\RegistrationTypeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\LabelGroup;
use App\Models\Language;
use Auth;
use App\Models\Job;
use App\Models\AppSetting;
use App\Models\Page;
use App\Models\FAQ;
use App\Models\LangForDDL;
use App\Models\ProductsServicesBook;
use DB;
use Edujugon\PushNotification\PushNotification;
use App\Models\StudentDetail;
use App\Models\JobTag;
use App\Models\Label;
use App\Models\Slider;
use Stripe;


class FrontController extends Controller
{

	public function getUserType()
	{
		$labels = [
        "tabBarLabels" => [
            "my_cv" => "My CV",
            "jobs" => "Jobs",
            "messages" => "Messages",
            "my_jobs" => "My Jobs",
            "favourites" => "Favourites",
            "products" => "Products",
            "marketPlace" => "Marker Place",
            "cart" => "Cart",
            "my_listing" => "My Listings",
            "my_orders" => "My Orders",
            "statistics" => "Statistics",
            "books" => "Books",
            "contest" => "Contest",
            "joined" => "Joined",
            "created" => "Created",
            "orders" => "Orders",
            "add" => "Add",
            "applicants" => "Applicants",
            "new_vacancy" => "New Vacancy"
        ],

        "initialScreen" => [
            "contest_text" => "Time For A Great Giveaway!!!",
            "job_text" => "Get Your Dream Job",
            "product_text" => "Get Your Special Offers",
            "book_text" => "Book Center",
            "login" => "Login",
            "contest" => "Contest",
            "jobs" => "Jobs",
            "products" => "Products",
            "services" => "Services",
            "library" => "Library"
        ],

        "Login" => [
            "forgot_password" => "Forgot Password",
            "password" => "Password",
            "phone_number" => "Phone Number",
            "remember_me" => "Remember Me",
            "sign_in" => "Sign In",
            "sign_up_text" => "You don't have an account? Sign up",
            "terms_and_policy" => "Terms of Use, Privacy Policy",
            "welcome_text" => "Welcome to Student Store"
        ],
        "SignUp" => [
            "continue_as_service_provider" => "Continue As Service Provider",
            "continue_as_student" => "Continue As Student",
            "continue_as_buyer" => "Continue As Guest",
            "welcome_text" => "Welcome to Student Store"
        ],
        "add_new_vacancy" => [
	    "report_abuse"=> "Report Abuse",
            "already_reported"=> "Already Reported",
            "application_period" => "Application Period",
            "duties_and_reponsibilities" => "Duties and Responsibilities",
            "from" => "From",
            "job_address" => "Job address",
            "job_description" => "Job Description",
            "job_env" => "Job Environment",
            "job_hours" => "Job Hours",
            "job_start_date" => "Job Start Date",
            "languages" => "Languages",
            "nice_to_have_skills" => "Nice To Have Skills",
            "page_title" => "Add New Vacancy",
            "publish_vacancy" => "Publish Vacancy",
            "save" => "Save",
            "skills" => "Skills",
            "to" => "To",
            "work_type" => "Work Type",
            "years_of_exp" => "Years of Experience",
            "duration" => "Duration",
            "category" => "Category",
            "sub_category" => "Sub Category",
            "job_title" => "Job Title - Position",
            "edit_vacancy" => "Edit Vacancy",
            "hours" => "Hours",
            "start" => "Start",
            "till" => "Till",
            "view_on_map" => "View On Map",
            "view_applicants" => "View Applicants",
            "apply_now" => "Apply Now",
            "application_will_start" => "Applications will start from",
            "already_applied" => "Already Applied",
            "complete_cv" => "Please complete your CV to apply",
            "update_cv" => "Update My CV",
            "years" => "Years",
            "boost" => "Boost",
            "upgrade_package" => "Upgrade Package",
            "boost_details" => "Boost Details",
            "promotion" => "Promotion",
            "end_on" => "End on",
            "job_environment" => "Job Environment",
            "job_address" => "Job Address",
            "save" => "Save",
            "upgrade_package" => "Upgrade Package",
            "job_applied" => "Job Applied",
 	    "seo_title" => "SEO Title",
            "seo_keywords" => "SEO Keywords",
            "seo_description" => "SEO Description",
        ],
        "address" => [
            "address" => "Address",
            "default_address" => "Set As Default Address",
            "pincode" => "Postal Code",
            "save" => "Save",
            "search" => "Search",
            "title" => "Address Title (Home. Office, ...)"
        ],
        "education_and_training" => [
            "activities" => "Main Activities",
            "activities_placeholder" => "Describe your Activities",
            "add" => "Add",
            "city" => "City",
            "country" => "Country",
            "education_and_training" => "Education and Training",
            "from" => "From",
            "from_sweden" => "I'm From Sweden",
            "new_education_and_training" => "New Education and Training",
            "ongoing" => "Ongoing",
            "save" => "Save",
            "state" => "State",
            "till" => "Till"
        ],
        "forgot_password" => [
            "change_number" => "Change Phone Number",
            "enter_code" => "Enter The Code",
            "next" => "Next",
            "password" => "Password",
            "phone_or_email" => "Phone Number",
            "please_enter_text" => "Please Enter Your",
            "policy_text" => "Terms of Use and Privacy Policy",
            "repeat_password" => "Repeat Password",
            "resend_code" => "Resend Code",
            "sent_code" => "We Have Sent A Code To",
            "set_password" => "Set Your Password",
            "submit" => "Submit"
        ],
        "job_application_status" => [
            "job_application_status_accepted" => "job_application_status_accepted",
            "job_application_status_applied" => "job_application_status_applied",
            "job_application_status_dispute " => "",
            "job_application_status_rejected" => "job_application_status_rejected",
            "job_application_status_withdrawn" => "job_application_status_withdrawn"
        ],
        "job_environment" => [
            "offline" => "At Office",
            "online" => "Online",
            "title" => "Job Environment"
        ],
        "job_filter" => [
            "apply_filter" => "Apply Filter",
            "reset_filter" => "Reset Filter",
            "applying_date" => "Application Date",
            "choose" => "Choose",
            "city" => "City",
            "country" => "Country",
            "distance" => "Distance",
            "language" => "Language",
            "maximum" => "Maximum",
            "minimum" => "Minimum",
            "published_date" => "Published Date",
            "select_date" => "Select Date",
            "skills" => "Skills",
            "state" => "State",
            "work_type" => "Work Type",
            "years_of_experience" => "Years of Experience"
        ],
        "job_home" => [
            "applicants" => "Applicants",
            "applied" => "Applied",
            "best_matches" => "Best Matches",
            "closing_soon" => "Closing Soon",
            "experience" => "Experience",
            "favourite" => "Favourite",
            "job_env" => "Job Environment",
            "job_title" => "Job Title",
            "jobs" => "Jobs",
            "latest" => "Latest",
            "my" => "My",
            "no_data_found" => "Data Not Found.",
            "promotion" => "Promotions",
            "promotional" => "Promotional",
            "random" => "Random",
            "recent" => "Recent",
            "search_placeholder_sa" => "Get yourself a Job",
            "search_placeholder_sp" => "Find an Applicant",
            "search_placeholder_view_all" => "Search",
            "skills_text" => " Skills",
            "vacancies" => "Vacancies",
            "view_all" => "View All",
            "work_type" => "Work Type",
            "year" => "Year",
            "years" => "Years",
            "details" => "Details",
            "my_jobs" => "My Jobs",
            "view_applications" => "View Applications",
            "resumes" => "Resumes",
            "all_applicants" => "All Applicants",
            "search_results" => "Search Results",
            "hours" => "Hours",
	    "pending_for_approval" => "Pending for approval",
            "approved_but_not_published" => "Approved but not published",
            "approved_and_published" => "Approved and published",
            "rejected" => "Rejected",
        ],
        "job_nature" => [
            "1_month" => "1 Month",
            "2_months" => "2 Months",
            "3_months" => "3 Months",
            "4_months" => "4 Months",
            "5_months" => "5 Months",
            "6_months" => "6 Months",
            "7_months" => "7 Months",
            "8_months" => "8 Months",
            "9_months" => "9 Months",
            "10_months" => "10 Months",
            "11_months" => "11 Months",
            "12_months" => "12 Months",
            "13_months" => "13 Months",
            "14_months" => "14 Months",
            "15_months" => "15 Months",
            "16_months" => "16 Months",
            "17_months" => "17 Months",
            "18_months" => "18 Months",
            "19_months" => "19 Months",
            "20_months" => "20 Months",
            "21_months" => "21 Months",
            "22_months" => "22 Months",
            "23_months" => "23 Months",
            "24_months" => "24 Months",
            "25_months" => "25 Months",
            "26_months" => "26 Months",
            "27_months" => "27 Months",
            "28_months" => "28 Months",
            "29_months" => "29 Months",
            "30_months" => "30 Months",
            "31_months" => "31 Months",
            "32_months" => "32 Months",
            "33_months" => "33 Months",
            "34_months" => "34 Months",
            "35_months" => "35 Months",
            "36_months" => "36 Months",
        ],
        "job_tabs" => [
            "applicants" => "Applicants",
            "favourites" => "Favourites",
            "jobs" => "Jobs",
            "message" => "Messages",
            "my_cv" => "My CV",
            "my_jobs" => "My Jobs",
            "new_vacancy" => "New Vacancy"
        ],
        "job_type" => [
            "contract" => "Contract",
            "full_time" => "Full Time",
            "internship" => "Internship",
            "one_time" => "One Time",
            "part_time" => "Part Time"
        ],
        "languages" => [
            "english" => "English",
            "swedish" => "Swedish"
        ],
        "messages" => [
	"message_agree_on_terms" => "You must agree on Terms before proceeding.",
  	"message_please_upgrade_package_to_add" => "Please upgrade package to add",
	"message_password_required"=> "Password Required",
 	"message_contest_tag_required"=> "Please select contest tags.",
 	"message_unable_to_file"=> "Unable to upload file, try another one",
	"message_you_can_not_participate_before_application_start_date" => "You can not participate before application start date",
	"message_not_accepting_any_application_now" => "Not accepting any application now",
	"message_not_approved" => "Please wait for your account approval.",
	"message_account_blocked" => "Your account is blocked, please contact admin",
	"account_is_blocked"=>"Your account is blocked, please contact admin",
	"message_bank_account_number" => "Bank Account Number",
	"message_valid_gtin_required" => "GTIN/ISBN length must be between 10 to 13 characters.",
            "message_valid_sku_required" => "SKU length must be between 5 to 13 characters.",
            "message_bank_name" => "Bank Name",
            "message_bank_identity_code" => "Bank Identification Code",
	     "message_no_items_to_order"=> "No items to order",
 	     "message_number_of_items_to_be_ordered"=> "Number of Items can be ordered",
 	    "message_out_of_stock"=> "Out of Stock",
	    "message_max_qty_reached"=> "Maximum Quantity Reached.",
            "message_not_allowed" => "You are not allowed to view this section",
            "message_ask_parent" => "Ask your parent to perform this operation",
            "message_same_gaurdian_contact" => "Gaurdian and child contact number must be different.",
            "message_same_gaurdian_email" => "Gaurdian and child email must be different.",
            "message_switch_to_buyer" => "Switch to Buyer from Profile.",
            "message_login_to_continue" => "Please login to continue.",
            "message_payment_success" => "Payment successful",
            "message_payment_failed" => "Payment failed,Please try again",
            "message_enter_valid_code" => "Please enter valid code",
            "message_are_you_sure" => "Are you sure ?",
            "message_uploaded_successfully" => "Uploaded successfully",
            "message_rated_successfully" => "Rated Successfully",
            "message_valid_rating" => "Please give a valid rating",
            "message_enter_valid_reason" => "Please enter a valid reason.",
            "message_invalid_username" => "Please enter a valid email or phone number",
            "message_boosted_successfully" => "Boosted Successfully",
            "message_removed_successfully" => "Removed Successfully",
            "message_copied_to_clipboard" => "Copied to Clipboard",
            "message_upgrade_package_to_promote" => "Upgrade your package to promote this item",
            "message_address_detail_created" => "message_address_detail_created",
            "message_address_detail_deleted" => "message_address_detail_deleted",
            "message_address_detail_list" => "message_address_detail_list",
            "message_address_detail_updated" => "message_address_detail_updated",
            "message_alert_title" => "Alert",
            "message_already_exist" => "message_already_exist",
            "message_basic_detail_updated" => "message_basic_detail_updated",
            "message_birthdate_required" => "Birthdate Required",
            "message_valid_birthdate" => "You must be 15 years or above to register as Guest.",
            "message_cancel" => "Cancel",
            "message_category_master_created" => "Category Master Created Successfully.",
            "message_category_master_deleted" => "Category Master Deleted Successfully.",
            "message_category_master_list" => "Category Master Retrieved Successfully.",
            "message_category_master_updated" => "Category Master Updated Successfully.",
            "message_created" => "Created Successfully.",
            "message_cv_detail_updated" => "CV Detail Updated Successfully.",
            "message_deleted" => "Deleted Successfully.",
            "message_dublicate_entry" => "Dublicate Entry.",
            "message_education_detail_created" => "Education Detail Created Successfully.",
            "message_education_detail_deleted" => "Education Detail Deleted Successfully.",
            "message_education_detail_list" => "Education Detail Retrieved Successfully.",
            "message_education_detail_updated" => "Education Detail Updated Successfully.",
            "message_email_required" => "Email Required",
            "message_error" => "Oops!! Something went wrong. Please try again later.",
            "message_error_title" => "Error",
            "message_extra_detail_updated" => "message_extra_detail_updated",
            "message_faq_created" => "Faq Created Successfully.",
            "message_faq_deleted" => "Faq Deleted Successfully.",
            "message_faq_list" => "Faq Retrieved Successfully.",
            "message_faq_updated" => "Faq Updated Successfully.",
            "message_fill_all_details" => "Fill All Details",
            "message_first_name_required" => "First Name Required",
            "message_g_contact_number_required" => "Guardian Mobile Number Required",
            "message_g_email_required" => "Guardian Email Required",
            "message_g_first_name_required" => "Guardian First Name Required",
            "message_g_last_name_required" => "Guardian Last Name Required",
            "message_gender_required" => "Gender Required",
            "message_institute_name" => "Name of the institute.",
            "message_internet_required" => "Please check your internet connection and try again.",
            "message_invalid_contact_number" => "Please enter a valid Mobile Number.",
            "message_invalid_email" => "Please enter a valid Email.",
            "message_invalid_otp" => "Please enter a valid otp",
            "message_job_application_created" => "message_job_application_created",
            "message_job_application_deleted" => "message_job_application_deleted",
            "message_job_application_list" => "message_job_application_list",
            "message_job_application_updated" => "message_job_application_updated",
            "message_label_created" => "Label Created Successfully.",
            "message_label_deleted" => "Label Deleted Successfully.",
            "message_label_group_created" => "Label Group Created Successfully.",
            "message_label_group_deleted" => "Label Group Deleted Successfully.",
            "message_label_group_list" => "Label-Groups Retrived Successfully.",
            "message_label_group_updated" => "Label Group Updated Successfully.",
            "message_label_list" => "Labels Retrived Successfully.",
            "message_label_updated" => "Label Updated Successfully.",
            "message_language_created" => "Language Created Successfully.",
            "message_language_deleted" => "Language Deleted Successfully.",
            "message_language_list" => "Languages Retrived Successfully.",
            "message_language_updated" => "Language Updated Successfully.",
            "message_last_name_required" => "Last Name Required",
            "message_module_type_created" => "Module Type Created Successfully.",
            "message_module_type_deleted" => "Module Type Deleted Successfully.",
            "message_module_type_list" => "Module Type Retrieved Successfully.",
            "message_module_type_updated" => "Module Type Updated Successfully.",
            "message_no" => "No",
            "message_ok" => "Ok",
            "message_old_password_error" => "message_old_password_error",
            "message_otp_sent" => "One time otp password has been sent to your entered contact number",
            "message_page_created" => "Page Created Successfully.",
            "message_page_deleted" => "Page Deleted Successfully.",
            "message_page_list" => "Page Retrieved Successfully.",
            "message_page_updated" => "Page Updated Successfully.",
            "message_password_length" => "Password length should be atleast 6 characters long.",
            "message_password_mismatch" => "Password and repeat password must be identical.",
            "message_password_updated" => "Password successfully updated",
            "message_reward_point_setting_created" => "Reward Point Setting Created Successfully.",
            "message_reward_point_setting_deleted" => "Reward Point Setting Deleted Successfully.",
            "message_reward_point_setting_list" => "Reward Point Setting Retrieved Successfully.",
            "message_reward_point_setting_updated" => "Reward Point Setting Updated Successfully.",
            "message_service_provider_type_created" => "Service Provider Type Created Successfully.",
            "message_service_provider_type_deleted" => "Service Provider Type Deleted Successfully.",
            "message_service_provider_type_list" => "Service Provider Type Retrieved Successfully.",
            "message_service_provider_type_updated" => "Service Provider Type Updated Successfully.",
            "message_something_went_wrong" => "Something went wrong, please try again.",
            "message_ssn_required" => "SSN Required",
            "message_student_card" => "Photo of provided Student/Enrollment ID card.",
            "message_student_number" => "Student/Enrollment number provided by institute.",
  	    "message_social_security_number" => "Social security number eg : YYYYMMDD-XXXX.",
            "message_success_title" => "Success",
            "message_updated" => "Updated Successfully.",
            "message_user_created" => "User Created Successfully.",
            "message_user_deleted" => "User Deleted Successfully.",
            "message_user_detail_saved" => "User detail saved Successfully",
            "message_user_list" => "Users Retrived Successfully",
            "message_user_login_credencial_error" => "Credencial not valid.",
            "message_user_login_success" => "User logged in successfully.",
            "message_user_not_exists" => "User does not exists.",
            "message_user_registered" => "User Registered Successfully",
            "message_user_type_created" => "User Type Created Successfully.",
            "message_user_type_deleted" => "User Type Deleted Successfully.",
            "message_user_type_list" => "User Type Retrieved Successfully.",
            "message_user_type_updated" => "User Type Updated Successfully.",
            "message_user_updated" => "User Updated Successfully.",
            "message_validation" => "Error While Validating Data",
            "message_work_experience_created" => "Work Experience Created Successfully.",
            "message_work_experience_deleted" => "Work Experience Deleted Successfully.",
            "message_work_experience_list" => "Work Experience Retrieved Successfully.",
            "message_work_experience_updated" => "Work Experience Updated Successfully.",
            "message_years_of_study" => "Number of years you have studied in the institute.",
            "message_yes" => "Yes",
            "message_favourite_job_created" => "message_favourite_job_created",
            "message_favourite_job_deleted" => "message_favourite_job_deleted",
            "message_favourite_job_list" => "message_favourite_job_list",
            "message_job_promote" => "message_job_promote",
            "message_job_publish" => "message_job_publish",
            "message_job_tags_list" => "message_job_tags_list",
            "message_job_update-status" => "status",
            "message_label_group_info" => "message_label_group_info",
            "message_language_changed" => "message_language_changed",
            "message_language_list" => "message_language_list",
            "message_company_name_required" => "Company Name Required",
            "message_org_number_required" => "Organization Number Required",
            "message_vat_number_required" => "VAT Registration Number Required",
            "message_vat_file_required" => "VAT Registration File Required",
            "message_reg_type_required" => "Registration Type Required",
            "message_sp_type_required" => "Service Provider Type Required",
            "message_establishment_year_required" => "Year of Establishment Required",
            "message_completion_year_required" => "Year of Completion Required",
            "message_company_name" => "Registered Name of the company",
            "message_year_of_establishment" => "Year of establishment eg:2020",
            "message_completion_year" => "Year of Completion eg:2020, not less than 3 years from current year.",
            "message_vat_reg_file" => "VAT registration document in pdf format.",
            "message_vat_reg_number"=> "VAT registration number, eg:SE999999-9999-99",
            "message_org_number"=> "Organization number, eg:999999-9999",
            "message_invalid_year" => "Please enter a valid year.",
            "message_file_size_error" => "File size too large",
            "message_invalid_year_founded" => "Year of establishment can not exceed current year",
            "message_contact_number_required" => "Mobile number required",
            "message_address_required" => "Address required",
            "message_invalid_org_number" => "Please enter a valid organization number",
            "message_invalid_vat_number" => "Please enter a valid VAT Registration number",
            "message_same_number" => "Same as company number",
            "message_same_email" => "Same as company email",
            "message_about_company_required" => "About company required",
            "message_website_url_required" => "Website url required",
            "message_website_url_valid" => "Please Enter Valid Website url",
            "message_logo_required" => "Company logo image required",
            "message_download_success" => "Downloaded Successfully",
            "message_unable_to_downlaod" => "Unable to download, try again",
            "message_want_to_delete" => "Do you want to delete this ?",
            "message_job_posted" => "Job Posted Successfully",
            "message_job_updated" => "Job Updated Successfully",
            "message_job_publish" => "Publish job to make it visible to applicants, but published job can not be edited, so you can publish it later as well",
            "message_job_hour_required" => "Job hours required",
            "message_duration_required" => "Duration required",
            "message_nice_to_have_skills_required" => "Nice to have skills required",
            "message_select_start_date" => "Please select start date",
            "message_select_end_date" => "Please select end date",
            "message_added_to_fav" => "Added to favourites",
            "message_removed_from_fav" => "Removed from favourites",
            "message_not_an_student" => "You can not be considered as student",
            "message_invalid_student_number" => "Please enter a valid student number",
            "message_select_category" => "Please select category",
            "message_select_subcategory" => "Please select subcategory",
            "message_select_brand" => "Please select brand",
            "message_product_title_required" => "Please enter product name",
            "message_gtin_required" => "Please enter GTIN",
            "message_sku_required" => "Please enter SKU",
            "message_sku_info" => "Stock Keeping Unit Details",
            "message_quantity_required" => "Please enter valid quantity",
            "message_price_required" => "Please enter valid price",
            "message_reward_points_required" => "Please enter valid reward points",
            "message_discount_percent_required" => "Please enter valid discount percent",
            "message_product_location_required" => "Please select product location",
            "message_delivery_type_required" => "Please select delivery type",
            "message_product_description_required" => "Please enter description",
            "message_delivery_date_required" => "Please select delivery date",
            "message_image_required" => "Please add atleast one image",
            "message_max_image_selected" => "Maximum 6 images can be selected",
            "message_max_return_image_selected" => "Maximum 4 images can be selected",
            "message_quality_required" => "Please enter valid quality",
            "message_service_title_required" => "Please enter Service name",
            "message_duration_required" => "Please enter valid duration",
            "message_languages_required" => "Please select atleast one language",
            "message_service_type_required" => "Please select service type",
            "message_service_location_required" => "Please select service location",
            "message_book_title_required" => "Please enter book name",
            "message_isbn_required" => "Please enter ISBN",
            "message_invalid_isbn" => "Please enter a valid ISBN",
            "message_author_required" => "Please enter author",
            "message_published_year_required" => "Please enter published year",
            "message_publisher_required" => "Please enter publisher",
            "message_language_required" => "Please select a language",
            "message_pages_required" => "Please enter valig number of pages",
            "message_suitable_age_required" => "Please select suitable age",
            "message_book_cover_required" => "Please select book cover",
            "message_dimensions_required" => "Please enter valid dimensions",
            "message_weight_required" => "Please enter valid weight",
            "message_book_location_required" => "Please select book location",
            "message_deposit_required" => "Please enter valid deposit",
            "message_cover_image_required" => "Please choose a cover image",
            "message_promoted_successfully" => "Promotion added Successfully",
            "message_published_successfully" => "Published Successfully",
            "message_unpromoted_successfully" => "Promotion Removed Successfully",
            "message_unpublished_successfully" => "Unpublished Successfully",
            "message_package_updated_successfully" => "Packages Upgraded Successfully",
            "message_item_successfully_added_to_cart" => "Item successfully added to cart",
            "message_increase_quantity_to_item" => "Increase quantity to add item",
            "message_quantity_updated" => "Successfully updated quantity",
            "message_deletion_confirmation" => "Do you want to delete this ?",
            "message_empty_cart_success" => "Cart Cleared Successfully",
            "message_order_placed_successfully" => "Your order has been placed successfully",
            "message_please_accept_terms_condition" => "Please first accept terms and condition to place order.",
            "message_add_max_images" => "You can only add up to max four images",
            "message_please_choose_reason" => "Choose reason for the return",
            "message_please_choose_tracking_number" => "Enter a valid tracking number",
            "message_please_choose_shipping_company" => "Choose shipping company first",
            "message_select_atleast_one_image" => "Select atleast one image",
            "message_expected_return_date" => "Select expected return date first",
            "message_mobile_number_verified" => "Mobile number verified",
            "message_mobile_number_not_verified" => "Mobile number not verified",
            "message_valid_card_number" => "Enter a valid card number",
            "message_payment_method_required" => "Please select/add Payment Method",
            "message_card_successfully_added" => "Card has been added successfully",
            "message_card_successfully_updated" => "Card successfully updated",
            "message_address_successfully_added" => "Address successfully added",
            "message_address_successfully_updated" => "Address successfully updated",
            "message_please_choose_cover_image" => "Please choose cover image",
            "message_please_enter_title" => "Please enter title",
            "message_success_contest_posted" => "Contest has been posted successfully",
            "message_success_event_posted" => "Event has been posted successfully",
            "message_select_contest_type" => "Please select contest type",
            "message_enter_description" => "Please enter description",
            "message_enter_sponsers" => "Please enter sponsers",
            "message_select_events_starting_date" => "Please select event starting date",
            "message_select_events_starting_time" => "Please select event starting time",
            "message_select_events_ending_date" => "Please select event ending date",
            "message_select_events_ending_time" => "Please select event ending time",
            "message_enter_meeting_link" => "Please enter meeting link",
            "message_select_application_starting_date" => "Please select application starting date",
            "message_select_application_ending_date" => "Please select application ending date",
            "message_select_education_level" => "Please select education level",
            "message_select_education_institute" => "Please select education institute",
            "message_enter_min_age" => "Please enter min age",
            "message_enter_max_age" => "Please enter max age",
            "message_enter_others" => "Please enter others",
            "message_enter_file_title" => "Please enter file title",
            "message_enter_the_jury" => "Please enter the jury",
            "message_enter_subscription_fee" => "Please enter subscription fee",
            "message_enter_discount_percentage" => "Please enter discount percentage",
            "message_enter_all_cancellation_hours_and_their_deducted_amount" => "Please enter cancellation hours and their deducted amount",
            "message_enter_first_winner_prize" => "Please enter first winner prize",
            "message_enter_second_winner_prize" => "Please enter second winner prize",
            "message_enter_third_winner_prize" => "Please enter third winner prize",
            "message_select_event_type" => "Please select event type",
            "message_success_contest_updated" => "Contest has been updated successfully",
            "message_success_event_updated" => "Event has been updated successfully",
            "message_success_participated" => "Successfully participated",
            "message_enter_max_participant_number" => "Please enter participant max number",
            "message_select_user_type" => "Please select user type",
            "message_select_target_country" => "Please select target country",
            "message_select_target_city" => "Please select target city",
            "message_select_service_provider_type" => "Please select service provider type",
            "message_select_registration_type" => "Please select registration type",
            "message_enter_min_participant_number" => "Please enter min participant number",
            "message_min_participants_can_not_be_greater" => "Min participants can not be greater max participants",
            "message_max_participants_can_not_be_greater_than" => "Max participants can not be greater than",
            "message_enter_correct_url_for_meeting_link" => "Please enter correct url for meeting link",
            "message_you_have_be_registered_as_student_to_participate" => "You have to be registered as student to participate",
            "message_you_have_be_registered_as_company_to_participate" => "You have to be registered as company to participate",
            "message_you_have_be_registered_as_normal_user_to_participate" => "You have to be registered as normal user to participate",
            "message_new_password_does_not_match" => "New password does not match",
            "message_password_length" => "Password length should be atleast six",
            "message_old_password_does_not_match" => "Old password does not match",
            "message_add_max_three_images" => "You can only add up to max three images",
            "message_enter_valid_email" => "Please enter a valid email",
            "message_enter_message_title" => "Please enter message title",
            "message_enter_message" => "Please enter message",
            "message_enter_email" => "Please enter email",
            "please_select_all_winners_for_this_contest" => "Please select all winners for this contest",
            "message_number_of_location_validation" => "The maximum number of locations that you can select is",
            "message_please_complete_your_CV_before_presenting" => "Please complete your CV before presenting",
            "message_you_have_already_added_this_data" => "You have already added this data",
            "message_please_first_select_your_starting_date" => "Please first select your starting date",
            "message_updated_successfully" => "Updated Successfully",
            "message_added_successfully" => "Added Successfully",
            "message_please_enter_valid_cancellation_hours" => "Please enter valid cancellation hours",
            "message_email_already_registered" => "This email is already registered",
            "message_invalid_image_selected" => "Invalid image selected",
            "message_empty_cart_confirmation" => "Do you want to empty cart ?",
            "message_unable_to_detect_your_location" => "Unable to detect your location",
            "message_enter_phone_number" => 'Enter Phone Number',
            "message_enter_password" => "Enter Password",
            "message_invalid_OTP" => "Please enter a valid otp",
            "password_changed_successfully" => "Password Changed Successfully",
            "message_by_presenting_your_CV_your_profile_will_be_visible_to_recruiters" => "By presenting your CV, your profile will be visible to recruiters.",
            "message_deleted_successfully" => 'Deleted Successfully',
            "message_unable_to_contact" => 'Unable to contact because contact number not found',
  	    "message_unable_to_contact_via_email" => "Unable to contact because email not found",
	    "message_successfully_reported" => "Reported successfully",
	    "message_invalid_social_security_number" => "Please enter a valid social security number",
        ],
        "my_cv" => [
            "address" => "Address",
            "complete_cv" => "Complete Your CV",
            "create_cv" => "Complete Your CV",
            "download_cv" => "Download CV",
            "download_resume" => "Download Resume",
            "education_and_training" => "Education and Training",
            "email" => "Email",
            "female" => "Female",
            "first_name" => "First Name",
            "gender" => "Gender",
            "job_env" => "Job Environment",
            "language" => "Languages",
            "last_name" => "Last Name",
            "male" => "Male",
            "mobile_number" => "Mobile Number",
            "others" => "Others",
            "personal_info" => "Personal Information",
            "present" => "Present",
            "skills" => "Skills",
            "birth_date" => "Birth Date",
            "title" => "My CV",
            "update_cv" => "Update Your CV",
            "upload_resume" => "Upload Resume",
            "basic_details" => "Basic CV Details",
            "work_exp" => "Work Experience",
            "years" => "Years",
            "no_data_found" => "Data Not Found.",
            "accept" => "Accept",
            "reject" => "Reject",
            "accepted" => "Accepted",
            "rejected" => "Rejected",
            "application_rejected" => "Application Rejected",
            "application_accepted" => "Application Accepted"
        ],
        "my_info" => [
            "change_password" => "Change Password",
            "contact_support" => "Contact Support",
            "edit_info" => "Edit Your Information",
            "my_addresses" => "My Addresses",
            "my_cards" => "My Payment Cards",
            "sa_title" => "My Information",
            "save" => "Save Changes",
            "sp_title" => "Store Information",
        ],
        "my_profile" => [
            "active_account" => "Your account is Approved",
            "inactive_account" => "Your account is Pending Verification",
            "my_info" => "My Information",
            "my_marketplace" => "My Market Place",
            "my_orders" => "My Orders",
            "points" => "Points",
            "printing_and_packaging" => "Printing And Packaging",
            "you_have" => "You have",
            "request" => "Requests",
            "management" => "Management",
        ],
        "register" => [
            "address" => "Address",
            "agree_text" => "I agree to all",
            "back" => "Back",
            "birth_date" => "Date of Birth",
            "change_number" => "Change Phone Number",
            "company_name" => "Company Name",
            "easy_text" => "Easy & Quick",
            "educational_institute" => "Educational Institute",
            "educational_level" => "Educational Level",
            "email" => "Email",
            "enter_code" => "Enter The Code",
            "enter_phone_number" => "Please Enter Your Phone Number",
            "female" => "Female",
            "fill_education_info" => "Fill Your Education Information",
            "fill_work_info" => "Fill Your Work Information",
            "final_step" => "Final Step",
            "finish" => "Finish",
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "male" => "Male",
            "mobile_number" => "Mobile Number",
            "name_of_institute" => "Name of the Institute",
            "next" => "Next",
            "no_of_years_of_study" => "Number of years of Study",
            "password" => "Password",
            "personal_info" => "Personal Information",
            "policy_text" => "Terms of Use and Privacy Policy",
            "registration_number" => "Commercial Registration No.",
            "registration_type" => "Registration Type",
            "repeat_password" => "Repeat Password",
            "resend_code" => "Resend Code",
            "sent_code" => "We Have Sent A Code To",
            "service_provider_type" => "Service Provider Type",
            "set_password" => "Set Your Password",
            "student_card" => "Student Card",
            "student_number" => "Student Number",
            "terms_and_policy" => "Terms of Use, Privacy Policy",
            "title_service_provider" => "Register New Service Provider",
            "title_student" => "Register New Student",
            "vat_registration" => "VAT Registration Number",
            "verify" => "Verify",
            "year_founded" => "Year of Establishment",
            "register" => "Register",
            "register_new" => "Register New",
            "new_student" => "New Student",
            "new_buyer" => "New Buyer",
            "service_provider" => "Service Provider",
            "social_security_number" => "Social Security Number",
            "completion_year" => "Year of Completion",
            "g_first_name" => "Guardian First Name",
            "g_last_name" => "Guardian Last Name",
            "g_email" => "Guardian Email",
            "g_contact_number" => "Guardian Contact Number",
            "email_otp" => "Enter Email OTP",
            "mobile_otp" => "Enter Mobile OTP",
            "change_email_and_phone" => "Change Email and Phone Number",
            "about_service_provider" => "About Service Provider",
            "contact_person_info" => "Contact Person Details",
            "service_provider_info" => "Service Provider Details",
            "edu_info" => "Education Information",
            "vat_registration_file" => "Vat Registration.pdf",
            "organization_number" => "Organization Number",
            "subscription_package_info" => "Subscription Packages",
            "about_service_provider" => "About Service Provider",
            "about_company" => "About Company",
            "website_url" => "Website URL",
            "logo_image" => "Logo Image",
            "download" => "Download",
            "short_intro_placeholder" => "Write something about your self",
            "short_intro" => "Short Intro",
            "gender" => "Gender",
            "view" => "View",
            "years" => "Years",
	    "bank_name" => "Bank Name",
            "bank_identifier_code" => "Bank Identification Code",
            "bank_account_number" => "Bank Account Number",
            "bank_account_type" => "Bank Account Type",
	    "bank_details" => "Bank Details",
        ],
        "packages" => [
            "job_packages" => "Job Packages",
            "product_packages" => "Product Packages",
            "service_packages" => "Service Packages",
            "contest_packages" => "Contest Packages",
            "book_packages" => "Book Packages",
            "packages_type_of_package" => "Type of package",
            "packages_job_ads" => "Job ads",
            "packages_publications_day" => "Publications day",
            "packages_duration" => "Duration",
            "packages_cvs_view" => "CV’s view",
            "packages_boost" => "Boost",
            "packages_employees_per_job_ad" => "Employees per job ad",
            "packages_no_of_boost" => "No. of boost",
            "packages_boost_no_of_days" => "Boost no. of days",
            "packages_price" => "Price",
            "packages_start_up_fee" => "Start up Fee",
            "packages_subscription" => "Subscription",
            "packages_no_of_product_services" => "No of product / Service",
            "packages_commission" => "Commission",
            "packages_commission_per_sale" => "Commission per sale",
            "packages_sponsar_cost" => "Sponsor cost",
            "packages_number_of_product" => "No. of Product",
            "packages_number_of_service" => "No. of Service",
            "packages_notice_month" => "Notice Period",
            "packages_locations" => "Location",
            "packages_range_of_age" => "Range of Age",
            "packages_range_of_age_from" => "Range of Age From",
            "packages_range_of_age_to" => "Range of Age To",
            "packages_organization" => "Organization",
            "packages_attendees" => "Attendees",
            "packages_cost_for_each_attendee" => "Cost for Each attendee",
            "packages_top_up_fee" => "Top up fee",
            "packages_free" => "Free",
            "packages_basic" => "Basic",
            "packages_standard" => "Standard",
            "packages_premium" => "Premium",
            "subscription_packages" => "Subscription Packages",
            "package_detail" => "Package Detail",
            "package_valid_till" => "Valid Till",
            "register" => "Register",
            "job" => "Job",
            "products" => "Products",
            "books" => "Books",
            "contest" => "Contest",
            "service" => "Service",
            "upgrade_subscription" => "Upgrade Packages",
            "months" => "Months",
            "days" => "Days",
            "module" => "Module",
            "duration_of_the_package" => "Duration of the package",
            "number_of_job_adds_you_can_post" => "Number of job adds you can post",
            "start_up_fee_of_the_package" => "Start up fee of the package",
            "job_adds_you_can_post_per_employee" => "Job adds you can post per employee",
            "total_cv_views" => "Total cv views",
            "promotion" => "Promotion",
            "number_of_jobs_you_can_promote" => "Number of jobs you can promote",
            "number_of_days_you_can_promote_jobs" => "Number of days you can promote jobs",
            "promotion_no_of_days" => "Promotion no. of days",
            "number_of_products_you_can_promote" => "Number of products you can promote",
            "number_of_days_you_can_promote_product" => "Number of days you can promote product",
            "notice_period_of_package" => "Notice period of package",
            "total_commission_per_sale" => "Total commission per sale",
            "total_number_of_products" => "Total number of products",
            "number_of_books_you_can_promote" => "Number of books you can promote",
            "number_of_days_you_can_promote_books" => "Number of days you can promote books",
            "total_cost_of_each_attendee" => "Total cost of each attendee",
            "organization" => "Organization",
            "total_top_up_fee" => "Total top up fee",
            "total_attendee" => "Total attendee",
            "location" => "Location",
            "unlimited" => "Unlimited",
            "buy_now" => "Buy now",
            "validity" => "Valid till",
            "jobs_adds" => "Jobs adds",
            "cv_views" => "CV views",
            "promotion_count" => "Promotion",
            "most_popular" => "Most popular",
            "top_selling" => "Top selling",
            "total_number_of_services" => "Total number of services",
            "stripe" => "Stripe",
            "klarna" => "Klarna",
            "swish" => "Swish",
            "new_card" => "New payment card",
        ],

        "image_picker_texts" => [
            "select_photo" => "Select Photo",
            "launch_camera" => "Launch Camera",
            "load_from_gallery" => "Load from Gallery",
            "cancel" => "Cancel"
        ],
        "registration_type" => [
            "type_1" => "Registration Type 1",
            "type_2" => "Registration Type 2",
            "type_3" => "Registration Type 3",
            "type_4" => "Registration Type 4"
        ],
        "service_provider_type" => [
            "type_1" => "Service Provider 1",
            "type_2" => "Service Provider 2",
            "type_3" => "Service Provider 3",
            "type_4" => "Service Provider 4"
        ],
        "side_menu" => [
            "parent" => "Parent",
            "contact_us" => "Contact Us",
            "copyright_text" => "Copyright 2021 @ Student Store",
            "logout" => "Logout",
            "messages" => "Messages",
            "my_favourite" => "My Favourite",
            "my_marketplace" => "My Market Place",
            "my_orders" => "My Orders",
            "my_profile" => "My Profile",
            "notifications" => "Notifications",
            "packages" => "Packages",
            "reward_points" => "Reward Points",
            "printing_and_packagin" => "Printing And Packaging",
            "seller" => "Seller",
            "buyer" => "Buyer",
            "guest" => "Guest",
	    "statistics" => "Statistics",
        ],
        "work_experience" => [
            "activities_and_responsibilities" => "Main Activities and Responsibilities",
            "activities_placeholder" => "Describe your tasks and responsibilities",
            "add" => "Add",
            "city" => "City",
            "country" => "Country",
            "employer" => "Employer",
            "from" => "From",
            "from_sweden" => "I'm From Sweden",
            "new_work_exp" => "New Work Experience",
            "save" => "Save",
            "state" => "State",
            "till" => "Till",
            "title" => "Title",
            "work_exp" => "Work Experience",
            "working" => "Working"
        ],
        "notifications" => [
            "title" => "Notifications",
            "no_data_found" => "Data Not Found."
        ],
        "delivery_type" => [
            "deliver_to_location" => "To Address",
            "pickup_from_location" => "Take Away"
        ],
        "duration_type" => [
            "hours" => "Hours",
            "days" => "Days",
            "weeks" => "Weeks",
            "months" => "Months",
        ],
        "book_cover" => [
            "title" => "Book Cover"
        ],
        "service_type" => [
            "online" => "Online",
            //"offline" => "At Location",

            "at_center" => "At Center",
            "at_customer" => "At Customer Location"
        ],
        "sell_type" => [
            "free" => "Free",
            "for_rent" => "For Rent",
            "for_sale" => "For Sale",
        ],
        "suitable_age" => [
            "5-9" => "5 to 9 years",
            "9-13" => "9 to 13 years",
            "13-18" => "13 to 18 years",
            "18+" => "18 years and above",
            "all" => "All"
        ],
        "add_product_service_book" => [
            "product" => "Products",
            "service" => "Services",
            "book" => "Books",
            "categories" => "Categories",
            "subCategories" => "Sub Categories",
            "select" => "Select",
            "brand" => "Brand",
            "product_title" => "Product Name",
            "sku" => "SKU",
            "gtin" => "GTIN",
            "service_title" => "Service Name",
            "book_title" => "Book Name",
            "quantity" => "Quantity",
            "price" => "Original Price",
            "apply_discount" => "Apply Discount",
            "discount_value" => "Discount In Percent",
            "discount_placeholder" => "eg:10",
            "discounted_price" => "Price after discount",
            "apply_reward_points" => "Add Reward Points",
            "reward_points" => "Reward Points",
            "reward_points_text" => "The maximum number of reward points for this item is : ",
            "product_location" => "Product Location",
            "service_location" => "Service Location",
            "service_type" => "Service Type",
            "book_location" => "Book Location",
            "delivery_type" => "Delivery Type",
            "description" => "Description",
            "delivery_date" => "Delivery Date",
            "images" => "Images",
            "tap_to_make_cover" => "Tap on image to set as cover image",
            "product_tags" => "Tags",
            "boost" => "Boost product",
            "promote" => "Promote",
            "next" => "Next",
            "back" => "Back",
            "cancel" => "Cancel",
            "submit" => "Submit",
            "add_more" => "Add More",
            "go_to_my_products" => "Go To My Products",
            "quality" => "Quality (%)",
            "duration" => "Duration",
            "dimension" => "Dimensions",
            "width" => "Width",
            "height" => "Height",
            "length" => "Length",
            "weight" => "Weight",
            "in_cm" => "in cm",
            "in_gram" => "in grams",
            "book_rent_price" => "Rent Price Per Day",
            "free" => "Free",
            "for_rent" => "For Rent",
            "for_sale" => "For Sale",
            "languages" => "Languages",
            "isbn" => "ISBN",
            "author" => "Author",
            "published_year" => "Published Year",
            "publisher" => "Publisher",
            "pages" => "Pages",
            "suitable_age" => "Suitable Age",
            "language" => "Language",
            "book_cover" => "Book Cover",
            "deposit" => "Deposit",
            "publish" => "Publish",
            "sell_type" => "Sell Type",
            "service_online_link" => "Service Online Link",
            "used_product" => "Used Product",
            "price_range" => "Price Range",
            "min_price" => "Min Price",
            "max_price" => "Max Price",
            "city" => "City",
            "cities" => "Cities",
            "apply_filter" => "Apply",
            "deposit_amount" => "Deposit Amount",
 	    "seo_title" =>"SEO Title",
            "seo_keywords"=>"SEO Keywords",
            "seo_description" =>"SEO Description"

        ],
        "product_detail_screen" => [
	   "report_abuse"=> "Report Abuse",
            "already_reported"=> "Already Reported",
            "points" => "Points",
            "quantity" => "Quantity",
            "shipping" => "Shipping",
            "location" => "Location",
            "product_description" => "Product Description",
            "customer_reviews" => "Customer Reviews",
            "similar_products" => "Similar Products",
            "view_all" => "View all",
            "store" => "Store",
            "ask" => "Ask",
            "add_to_cart" => "Add To Cart",
            "buy_now" => "Buy Now",
            "view_on_map" => "View On Map",
            "zoom" => "Zoom",
            "seller" => "Seller",
            "edit" => "Edit",
            "boost" => "Boost",
            "boost_text" => "Please use your boost wisely, if you remove your boostings before the time, your boost will be lost.",
            "boost_details" => "Boost Details",
            "upgrade_package" => "Upgrade Package",
            "promotion" => "Promotion",
            "top_selling" => "Top Selling",
            "most_popular" => "Most Popular",
            "end_on" => "Will End on",
            "view_in_cart" => "View In Cart",
            "similar_services" => "Similar Services",
            "similar_books" => "Similar Books",
            "no_data_found" => "Data Not Found.",
            "view_more" => "View More",
            "share" => "Share",
            "share_this" => "Share this",
            "mark_as_sold" => "Mark As Sold",
            "status" => "Status : "
        ],
        "my_market_place" => [
            "my_market_place" => "My Market Place",
            "add_a_new_item" => "Add A New Item",
            "use_gtin_or_isbn" => "Use GTIN Or ISBN",
            "you_can_choose_to_add_it_manually" => "you can choose to add it manually",
            "product_for_sale" => "Product For Sale",
            "Services_to_others" => "Service to others",
            "books" => "Books"
        ],
        "product_card" => [
            "online" => "Online",
            "save" => "Save"
        ],
        "product_view_all" => [
            "all" => "All",
            "promotions" => "Promotions",
            "seller" => "Seller",
            "free" => "Free",
            "off" => "Off",
            "search_result" => "Search Results",
            "no_data_found" => "Data Not Found."
        ],
        "add_to_cart" => [
            "delivery_type" => "Delivery Type :",
            "to_address" => "To Address",
            "take_away" => "Take Away",
            "add_new_address" => "Add New Address",
            "next" => "Next",
            "note" => "Note :",
            "back" => "Back",
            "add_to_cart" => "Add To Cart",
            "successfully_added" => "Successfully Added",
            "seller" => "Seller :",
            "total" => "Total :",
            "points" => "Points",
            "go_to_cart" => "Go To Cart",
            "continue_shopping" => "Continue Shopping",
            "default" => "default",
            "delivering_to" => "Delivering to :",
        ],
        "review_and_rating" => [
            "welcome_text" => "Your opinion matters to us",
            "product" => "Product",
            "service" => "Service",
            "book" => "Book",
            "submit" => "Submit",
            "seller" => "Seller",
            "review_placeholder" => "Write Something"
        ],
        "promotion_modal" => [
            "delivery_type" => "Delivery Type :",
            "to_address" => "To Address",
            "take_away" => "Take Away",
            "add_new_address" => "Add New Address",
            "next" => "Next",
            "note" => "Note",
            "tracking_number" => "Tracking Number",
            "reason_placeholder" => "Write Something",
            "reason" => "Reason",
            "expected_delivery_date" => "Expected Delivery Date",
            "tracking_number_placeholder" => "Tracking number of the item",
            "shipment_company_name" => "Shipping Company",
            "shipment_placeholder" => "Shipping Company Name",
            "back" => "Back",
            "cancel" => "Cancel",
            "done" => "Done",
            "add_to_cart" => "Add To Cart",
            "successfully_added" => "Successfully Added",
            "seller" => "Seller",
            "total" => "Total",
            "points" => "Points",
            "go_to_cart" => "Go To Cart",
            "continue_shopping" => "Continue Shopping",
            "default" => "default",
            "delivering_to" => "Delivering to",
            "top_selling" => "Top Selling",
            "most_popular" => "Most Popular",
            "promotion" => "Promotion",
            "images" => "Images",
            "verification_code" => "Verification Code",
            "verification_code_placeholder" => "Enter Verification Code"
        ],
        "product_place_order_screen" => [
            "stripe" => "Stripe",
            "klarna" => "Klarna",
            "swish" => "Swish",
            "seller" => "Seller",
            "free" => "Free",
            "off" => "Off",
            "add_new_address" => "Add New Address",
            "default" => "default",
            "take_away" => "Take Away",
            "to_address" => "To Address",
            "delivery_type" => "Delivery Type :",
            "next" => "Next",
            "sub_total" => "Sub-total :",
            "shipping" => "Shipping :",
            "tax" => "Tax :",
            "total" => "Total :",
            "payment_method" => "Payment method",
            "delivery_address" => "Delivery Address",
            "use_my_reward_points" => "Use my reward points",
            "available_reward_points" => "Available reward points : ",
            "new_card" => "New payment card",
            "reward_points" => "Reward Points",
            "apply" => "Apply",
            "reward_points_text" => "Maximum reward points can be used : ",
            "you_can_add_notes_to_your_order" => "you can add notes to your order",
            "i_agree_to_all" => "By clicking checkout, you agree to our",
            "terms_of_use_and_privacy_policy" => "Terms of Use and Privacy Policy",
            "check_out" => "Check Out",
            "payment_done_by" => "Payment done by",
            "cancel" => "Cancel",
            "my_address" => "My Address",
            "pick_up_from_location" => "Pickup from location :",
            "deliver_to_location" => "Deliver to location",
            "online" => "Online",
            "vat" => "VAT",
            "my_payment_card" => "My payment cards"
        ],
        "product_order_successfully_placed_screen" => [
            "well_done" => "Well Done",
            "your_order_no" => "Your Order No. ",
            "was_submitted_successfully" => " Was Submitted Successfully",
            "seller" => "Seller :",
            "total" => "Total :",
            "points" => "Points",
            "delivering_to" => "Delivering to :",
            "continue_shopping" => "Continue Shopping",
            "go_to_my_orders" => "Go To My Orders",
        ],
        "used_product_screen" => [
            "recently_added_products" => "Recently added",
            "view_all" => "View all",
            "random_products" => "Random",
            "promotions" => "Promotions",
            "inside_all_text" => "It's all inside... search",
            "products" => "Products",
            "services" => "Services",
            "best_selling_product" => "Best selling",
            "data_not_found" => "Data Not Found.",
            "most_popular_services" => "Most popular",
            "top_rated" => "Top rated"
        ],
        "my_market_place_bar_code_scanner" => [
            "add_a_new_item" => "Add A New Item",
            "scan_or_enter" => "Scan Or Enter",
            "gtin_or_isbn_barcode" => "GTIN Or ISBN Barcode",
            "scan" => "Scan",
            "gtin_description" => "The Global Trade Item Number is an identifier for trade items, developed by GS1. Such identifiers are used to look up product ... information in a database which may belong to a retailer, manufacturer, collector, researcher, or other entity.",
            "i_dont_have_barcode" => "I don't have barcode"
        ],
        "product_my_orders" => [
            "ask_for_cancel" => "Ask For Cancellation",
            "asked_for_cancel" => "Asked For Cancellation",
            "type" => "Type",
            "my_orders" => "My Orders",
            "product" => "Products",
            "search_bar_text_" => "Search....",
            "service" => "Services",
            "book" => "Books",
            "hide_completed_orders" => "Hide Completed Orders",
            "order_no" => "Order No. ",
            "delivering_to" => "Delivering to :",
            "total" => "Total :",
            "used" => "Used : ",
            "points" => "Points",
            "rate" => "Rate",
            "go_and_pickup" => "Go and pickup",
            "completed" => "Completed",
            "under_process" => "Under Process",
            "canceled" => "Canceled",
            "shipped" => "Shipped",
            "quantity" => "Quantity",
            "return" => "Return",
            "no_data_found" => "Data Not Found.",
            "tracking_number" => "Tracking No.",
            "expected_delivery_date" => "Exp. Del. Date",
            "expected_return_date" => "Exp. Ret. Date",
            "expected_replacement_date" => "Exp Rep. Date",
            "delivery_completed_date" => "Del. Date",
            "return_date" => "Ret. Date",
            "replacement_date" => "Rep. Date",
            "seller" => "Seller : "
        ],
        "product_my_cart" => [
            "ask_parent" => "Ask Your Parent to Checkout",
            "stripe" => "Stripe",
            "klarna" => "Klarna",
            "swish" => "Swish",
            "seller" => "Seller :",
            "delivering_to" => "Delivering to :",
            "sub_total" => "Sub-total :",
            "shipping" => "Shipping :",
            "tax" => "Tax :",
            "total" => "Total :",
            "payment_method" => "Payment method :",
            "use_my_reward_points" => "Use my reward points",
            "available_reward_points" => "Available reward points : ",
            "new_card" => "New payment card",
            "reward_points" => "Reward Points",
            "apply" => "Apply",
            "new_address" => "New Address",
            "you_can_add_notes_to_your_order" => "you can add notes to your order",
            "i_agree_to_all" => "By clicking checkout, you agree to our",
            "terms_of_use_and_privacy_policy" => "Terms of Use and Privacy Policy",
            "check_out" => "Check Out",
            "cancel" => "Cancel",
            "continue_shopping" => "Continue Shopping",
            "empty_cart" => "Empty Cart",
            "select_address" => "Select Address",
            "vat" => "VAT",
            "delivery_address" => "Delivery address :",
            "my_address" => "My Addresses",
            "my_payment_card" => "My payment cards",
            "default" => "default"
        ],
        "product_myorders_detail" => [
            "order_number" => "Order Number",
            "seller" => "Seller",
            "buyer" => "Buyer",
            "delivery_type" => "Delivery type : ",
            "service_type" => "Service Type : ",
            "service_online_link" => "Service Online Link : ",
            "confirm_receipt" => "Confirm Delivery",
            "contact_the_seller" => "Contact Seller",
            "contact_the_buyer" => "Contact Buyer",
            "contact_support" => "Contact Support",
            "dispute" => "Dispute",
            "decline" => "Decline",
            "accept" => "Accept",
            "cancel_item" => "Cancel Item",
            "total" => "Total",
            "points" => "Points",
            "used" => "Used : ",
            "quantity" => "Quantity",
            "payment_card" => "Payment Card",
            "rate" => "Rate",
            "reason" => "Reason",
            "ask_for_cancel" => "Ask For Cancellation",
            "asked_for_cancel" => "Asked For Cancellation",
            "go_and_pickup" => "Go and pickup",
            "tracking_number" => "Tracking No.",
            "return_code" => "Return Code",
            "replacement_code" => "Replacement Code",
            "return_item" => "Return Item",
            "return_text" => "The last date to return this item is : ",
            "return_policy_text" => "Before returning, please read our ",
            "return_policy" => "Return & Replacement Policy",
            "expected_delivery_date" => "Expected Delivery Date",
            "expected_return_date" => "Expected. Return Date",
            "expected_replacement_date" => "Expected Replacement Date",
            "delivery_completed_date" => "Delivery Date",
            "return_date" => "Return Date",
            "replacement_date" => "Replacement Date",
            "date_of_resolution" => "Date of Resolution",
            "admin" => "Admin",
            "resolve_to_customer_message" => "Your order item is canceled and the amount will be credited in your account within 14 days from the date of resolution.",
            "resolve_to_seller_message" => "Your order item is marked as completed because dispute is resolved in favour of seller.",
            "seller_with_colon" => "Seller : ",
            "buyer_with_colon" => "Buyer : ",
            "delivery_details" => "Delivery Details",
            "return_details" => "Return Details",
            "replacement_details" => "Replacement Details",
            "dispute_details" => "Dispute Details",
            "via" => "Via",
            "note_to_seller" => "Note To Seller : ",
        ],
        "manage_my_market_place" => [
            "manage_my_market_place" => "Manage market place",
            "well_done" => "Well Done",
            "your_order_no" => "Your Order No. ",
            "was_submitted_successfully" => " Was Submitted Successfully",
            "seller" => "Seller :",
            "total" => "Total :",
            "free" => "Free",
            "off" => "Off",
            "points" => "Points",
            "delivering_to" => "Delivering to :",
            "continue_shopping" => "Continue Shopping",
            "go_to_my_orders" => "Go To My Orders",
            "product_location" => "Product location",
            "scan" => "Scan",
            "product" => "Products",
            "service" => "Services",
            "book" => "Books",
            "search_bar_text_" => "Search....GTIN-SKU-ISBN-Word",
            "scan" => "Scan",
            "no_data_found" => "Data Not Found.",
            "price" => "Price",
            "pending_for_approval" => "Pending for approval",
            "approved_but_not_published" => "Approved but not published",
            "approved_and_published" => "Approved and published",
            "rejected" => "Rejected",
            "data_not_found" => "Data Not Found.",
            "my_listings" => "My Listings",
            "my_orders" => "My Orders",
            "statistics" => "Statistics",
            "my" => "My",
            "favourite" => "Favourite",
        ],

        "order_status" => [
            "all" => "All",
            //"pending" => "Pending",
            "processing" => "Processing",
            "shipped" => "Shipped",
            "delivered" => "Delivered",
            "completed" => "Completed",
            "canceled" => "Canceled",
            "return_initiated" => "Return Initiated",
            "returned" => "Returned",
            "replacement_initiated" => "Replacement Initiated",
            "replaced" => "Replaced",
            "declined" => "Declined",
            "dispute_initiated" => "Dispute",
            "reviewed_by_seller" => "Review"
            //"resolved" =>""
        ],

        "dispute_status" => [
            "declined" => "Declined",
            "dispute_initiated" => "Dispute Initiated",
            "resolved_to_customer" => "Resolved to Customer",
            "resolved_to_seller" => "Resolved to Seller",
            "reviewed_by_seller" => "Review",
            "review_accepted" => "Review Accepted"
        ],

        "return_type" => [
            "shipment" => "Shipment",
            "by_hand" => "By Hand"
        ],

        "my_market_place_requests" => [
            "tracking_number" => "Tracking No.",
            "quantity" => "Quantity",
            "expected_delivery_date" => "Exp. Del. Date",
            "expected_return_date" => "Exp. Ret. Date",
            "expected_replacement_date" => "Exp Rep. Date",
            "delivery_completed_date" => "Del. Date",
            "return_date" => "Ret. Date",
            "replacement_date" => "Rep. Date",
            "market_place_requests" => "Market place Requests",
            "my_orders" => "Orders",
            "product" => "Products",
            "service" => "Services",
            "book" => "Books",
            "hide_completed_orders" => "Hide Completed Orders",
            "order_no" => "Order No. ",
            "delivering_to" => "Delivering to : ",
            "delivery_type" => "Delivery Type : ",
            "service_type" => "Service Type : ",
            "total" => "Total :",
            "points" => "Points",
            "rate" => "Rate",
            "go_and_pickup" => "Go and pickup",
            "completed" => "Completed",
            "under_process" => "Processing",
            "canceled" => "Canceled",
            "pending" => "Pending",
            "shipped" => "Shipped",
            "ship" => "Ship",
            "process" => "Process",
            "deliver" => "Deliver",
            "cancel" => "Cancel",
            "ask_for_cancel" => "Ask For Cancellation",
            "asked_for_cancel" => "Asked For Cancellation",
            "contact_support" => "Contact Support",
            "dispute" => "Dispute",
            "return" => "Return",
            "accept_return" => "Accept Return",
            "replace" => "Replace",
            "decline" => "Decline",
            "reason" => "Reason",
            "resolve" => "Resolve",
            "refund" => "Refund",
            "contact_buyer" => "Contact Buyer",
            "resolve_message" => "The dispute is resolved by the seller",
            "no_data_found" => "Data Not Found."
        ],
        "store_screen" => [
            "business_license" => "Business License",
            "store" => "store",
            "about" => "About",
            "products_and_services" => "Products and services",
            "view_all" => "View all",
            "customer_reviews" => "Customer Reviews",
            "view_more" => "View More",
            "data_not_found" => "Data Not Found.",
            "products" => "Products",
            "books" => "Books",
            "services" => "Services",

        ],
        "spFilter" => [
            "all" => "All"
        ],
        "statistic" => [
            "my_statistics" => "My Statistics",
 	    "data_not_found" => "Data Not Found.",
            "top_selling_products" => "Top Selling Products",
            "top_selling_services" => "Top Selling Services",
            "top_selling_books" => "Top Selling Books",
            "most_popular_contests" => "Most Popular Contests",
            "most_popular_events" => "Most Popular Events",
            "order_completed" => "Orders completed",
            "order_delivered" => "Orders delivered",
                        "order_under_process" => "Orders processing",
                        "total_amount_refunded" => "Amount refunded",
                        "total_books" => "Books",
                        "total_companies" => "companies",
                        "total_contests" => 'contests',
                        "total_earnings" => "earnings",
                        "total_events" => "events",
                        "total_jobs" => "jobs",
                        "total_normal_users" => "Guests",
                        "total_orders" => "orders",
                        "total_products" => "products",
                        "total_returned_items" => "returned items",
                        "total_services" => "services",
                        "total_students" => "students"
        ],
        "return_item_model" => [
            "return" => "Return",
            "attention" => "Attention",
            "return_text" => "Make sure the returned item is delivered to the shipping company and you have a",
            "trackingNumber" => "tracking number",
            "address" => "Address : ",
            "name" => 'Name : ',
            "yes_i_have_tracking_number" => "Yes i have tracking number",
            "deliver_by_hand" => "Deliver by hand",
            "item" => "Item :",
            "quantity" => "Quantity :",
            "return_to_address" => "Return to address :",
            "shipping_company" => "Shipping Company",
            "shipping_company_:" => "Shipping Company :",
            "reason" => "Reason",
            "reason_:" => "Reason :",
            "please_choose" => "Please Choose.. ",
            "tracking_number" => "Tracking Number :",
            "images" => 'Images :',
            "tap_to_make_cover" => "Tap to make cover",
            "submit" => "Submit",
            "delivery_address" => "Delivery address :",
            "finish" => "Finish",
            "success" => "Success",
            "your_return_code" => "Your return code :",
            "use_this_number_with_the" => "Use this number with the",
            "store" => "Store",
            "to_complete_return" => "to complete return",
            "replacement" => "Replacement",
            "expected_return_date" => "Expected return date :"
        ],
        "card_list" => [
            "my_cards" => "My payment cards",
            "default" => "default",
            "your_cards" => "Your Cards",
            "add" => "Add",
        ],
        "card_form" => [
            "please_enter_your_card_information" => "Please enter your card information",
            "card_holder_name" => 'Card Holder Name',
            "card_number" => "Card Number",
            "cvv" => 'CVV',
            "valid_thru_date" => "MM / YYYY",
            "mobile_number" => "Mobile Number",
            "please_enter_your_parents_information" => "Please enter your parent's information",
            "full_name" => 'Full Name',
            "terms_and_policy" => "Terms of Use, Privacy Policy",
            "set_as_default" => "Set as default",
            "i_agree_to_all" => "I agree to all",
            "next" => "Next",
            "save" => "Save",
        ],
        "address_list" => [
            "my_address" => "My Address",
            "default" => "default",
        ],
        "book_home_page" => [
            "book" => "Book",
            "inside_all_text" => "It's all inside ... search",
            "promotions" => "Promotions",
            "view_all" => "View all",
            "products" => "Products",
            "ervices" => "Services",
            "best_selling_products" => "Best selling",
            "most_popular_services" => "Most popular services",
            "top_rated_service_providers" => "Top rated service providers",
            "new_service_providers" => "New service providers",
            "latest_used_products" => "Latest used products",
            "student_popular_services" => "Student popular services",
            "company_listing" => "Company Listing",
            "student_listing" => "Student Listing",
            "data_not_found" => "Data Not Found.",
            "product" => "Product",
            "service" => "Service",
            "most_popular_products" => "Most popular",
            "company_top_rated_products" => "Top rated",
            "company_recently_added_products" => "Recently added",
            "company_random_products" => "Random",
            "for_sale" => "For Sale",
            "free" => "Free",
        ],
        "book_student_listing" => [
            "book" => "Book",
            "inside_all_text" => "It's all inside ... search",
            "promotions" => "Promotions",
            "view_all" => "View all",
            "recently_added" => "Recently added",
            "best_selling" => "Best selling",
            "most_popular" => "Most popular",
            "top_rated" => "Top rated",
            "data_not_found" => "Data Not Found.",
            "for_sale" => "For Sale",
            "free" => "Free",
            "for_rent" => "For Rent",
            "random" => "Random",
        ],
        "reward_point_screen" => [
            "reward_points" => "Reward Points",
            "current" => "Current",
            "used" => "Used",
            "earned" => "Earned",
            "current_reward_points" => "Current reward points : ",
            "pending_reward_points" => "Pending reward points : ",
            "reward_point_value_title" => "1 Reward point is eqivalent to : ",
            "order_number" => "Order No. : ",
            "order_date" => "Date : ",
            "reward_point_used" => "Reward point used : ",
            "reward_point_earned" => "Reward point earned : ",
            "price" => "Price : ",
            "status_of_reward_points" => "Status of reward points : ",
            "points" => "Points",
            "quantity" => "Quantity : ",
	    "share_reward_points" => "Share Reward Points",
	    "shared" => 'Shared',
 	    "you_have_received" => "You have received",
            "reward_points_from" => "reward points from",
            "you_have_sent" =>"You have sent",
            "reward_points_to" =>'reward points to'
        ],
        "reward_point_status" => [
            "credited" => "Credited",
            "pending" => "Pending",
            "canceled" => "Canceled",
            "used" => "Used",
            "refunded" => "Refunded"
        ],
        "all_customer_reviews_screen" => [
            "customer_reviews" => "Customer reviews",
        ],
        "messages_screen" => [
            "date" => "Date : ",
            "unread_messages" => "Unread messages : ",
            "only_show_unread_messages" => "Only Show unread messages",
            "no_data_found" => "Data Not Found.",
            "outgoing" => "Outgoing",
            "incoming" => "Incoming",
        ],
        "header_comp" => [
            "type_to_search" => "Type to search",
        ],
        "chat_screen" => [
            "type_a_message" => "Type a message",
            "failed_tap_to_retry" => "Failed, Tap to retry",
        ],
        "ask_chat_modal" => [
            "ask_the_seller_about" => "Ask The Seller About",
            "message" => "Message",
            "message_title" => "Message Title",
            "your_message" => "Your Message",
            "send" => "Send",
            "done" => "Done",
            "go_to_my_messages" => "Go To My Messages",
            "success_message" => "Your message was sent successfully",
            "ask_the_contest_creator_about" => "Ask The Contest Creator About",
            "ask_the_event_creator_about" => "Ask The Event Creator About",
        ],
        "contest_home_page" => [

            "contest" => "Contest",
            "inside_all_text" => "It's all inside ... search",
            "promotions" => "Promotions",
            "view_all" => "View all",
            "company_listing" => "Company Listing",
            "student_listing" => "Student Listing",
            "data_not_found" => "Data Not Found.",
            "closing_soon" => "Closing Soon",
            "most_popular_products" => "Most popular",
            "company_top_rated_products" => "Top rated",
            "company_recently_added_products" => "Recently added",
            "company_random_products" => "Random",
            "contest" => "Contest",
            "event" => "Event",
            "newest" => "Newest",
            "create_new_contest" => "Create New Contest"
        ],
        "contest_student_listing" => [
            "inside_all_text" => "It's all inside ... search",
            "promotions" => "Promotions",
            "view_all" => "View all",
            "newest" => "Newest",
            "best_selling" => "Best selling",
            "most_popular" => "Most popular",
            "closing_soon" => "Closing Soon",
            "top_rated" => "Top rated",
            "data_not_found" => "Data Not Found.",
            "for_sale" => "For Sale",
            "free" => "Free",
            "for_rent" => "For Rent",
            "random" => "Random",
            "contest" => "Contest",
            "event" => "Event"
        ],
        "ContestCarouselCard" => [
            "winner" => "Winner",
            "free" => "Free",
            "participated" => "Participated",
            "off" => "OFF"
        ],
        "create_new_contest" => [
            "new_contest" => "New Contest",
            "contest_info" => "Contest Info : ",
            "contest_description" => "Contest Description",
            "contest_cover_image" => "Contest Cover Image",
            "event_cover_image" => "Event Cover Image",
            "title" => "Title",
            "contest_type" => "Contest Type...",
            "event_type" => "Event Type...",
            "sponsers" => "Sponsers : ",
            "next" => "Next",
            "event_description" => "Event Description : ",
            "event_info" => "Event info : ",
            "contest_description" => "Contest Description : ",
            "starting_date" => "Starting Date : ",
            "starting_and_ending_time" => "Starting And Ending Time : ",
            "end_date_and_time" => "Ending Date & Time : ",
            "application_start_date" => "Application Start Date : ",
            "application_end_date" => "Application End Date : ",
            "participant_max_number" => "Participant Max Number : ",
            "when_adding_a_" => "When adding a paid competition, the competition fee depends mainly on the number of participants. The fee for one participant is 50 kr",
            "to_address" => "To Address : ",
            "location" => "Location",
            "add_new_address" => "Add new address",
            "at_location" => "At Location",
            "online" => "Online",
            "meeting_link" => "Meeting Link : ",
            "empty_is_unlimited" => "( empty is unlimited )",
            "default" => "default",
            "back" => 'Back',
            "next" => "Next",
            "condition_of_joining" => "Conditions of joining (if any) :",
            "education_level" => "Education Level",
            "education_institution" => 'Educational Institution',
            "age_restriction" => "Age restriction",
            "min" => "Min",
            "max" => "Max",
            "others" => "Others : ",
            "required_upload_file" => "Required upload file",
            "file_title" => 'File title',
            "the_jury" => "The Jury : ",
            "add_new_contest" => "Add New Contest",
            "add_new_form" => "Add New Form",
            "subscription_fees" => "Subscription Fees : ",
            "entry_cost_for_attendee" => "Entry cost for attendee : ",
            "free_subscription" => "Free Subscription",
            "use_cancellation_policy" => "Use Cancellation Policy",
            "hour_s" => "Hour/s",
            "from" => "From",
            "to" => "To",
            "deduct" => "Deduct",
            "number_of_winners" => "Total number of winners",
            "st_winner_prize" => "1st Winner Prize",
            "nd_winner_prize" => "2nd Winner Prize",
            "rd_winner_prize" => "3rd Winner Prize",
            "publish_contest" => "Publish Contest",
            "publish_event" => "Publish Event",
            "submit" => "Submit",
            "new_event" => "New Event",
            "my_address" => "My Address",
            "categories" => "Categories",
            "subCategories" => "Sub Categories",
            "select" => "Select",
            "contest" => "Contest",
            "apply_discount" => "Apply Discount",
            "discount_in_percentage" => "Discount In Percentage",
            "discount_placeholder" => "eg:10",
            "discounted_price" => "Price after discount",
            "apply_reward_points" => "Add Reward Points",
            "reward_points" => "Reward Points",
            "reward_points_text" => "The maximum number of reward points for this item is : ",
            "set_condition_of_joining" => "Set condition of joining",
            "user_type" => "User type",
            "all" => "All",
            "student" => "Student",
            "edit_contest" => "Edit Contest",
            "edit_event" => "Edit Event",
            "target_location" => 'Target Location',
            "city" => "City",
            "country" => "Country",
            "the_amount_you_will_get_after_subtracting_commission_will_be" => "The amount you will get after subtracting commission will be ",
            "registration_type" => "Registration Type",
            "service_provider_type" => "Service Provider Type",
            "max_participants_will_be" => "Max participants will be",
            "set_min_paticipants" => "Set Min Participants",
            "participant_min_number" => "Participant Min Number : ",
	    "seo_title" =>"SEO Title",
            "seo_keywords"=>"SEO Keywords",
            "seo_description" =>"SEO Description"
        ],
        "add_new_contest_or_event" => [
            "add_new_contest" => "Add New Contest",
            "add_new_event" => "Add New Event",
            "please_upgrade_package_to_add_contest" => "Please upgrade package to add contest",
            "please_upgrade_package_to_add_event" => "Please upgrade package to add event",
        ],
        "contest_detail" => [
	    "report_abuse"=> "Report Abuse",
            "already_reported"=> "Already Reported",
            "reason" => "Reason",
            "reupload" => "Reupload Document",
            "description" => "Description : ",
            "cancellation_policy" => "Cancellation policy",
            "created_by" => "Created by : ",
            "deduct" => "Deduct",
            "winner" => 'Winner : ',
            "winner_" => "Winner",
            "participate_now" => 'Participate now',
            "publish_contest" => 'Publish Contest',
            "start" => "Start",
            "participants" => "Participants",
            "end_contest" => "End Contest",
            "cancel_contest" => "Cancel Contest",
            "select_a_winner" => "Select a winner",
            "subscription_fees" => "Subscription fees",
            "free" => "Free",
            "sponser" => "Sponser",
            "type" => "Type",
            "application_start" => "Application start",
            "application_end" => "Application end",
            "contest_start_date" => "Contest start date",
            "contest_start_and_end_time" => "Contest start & end time",
            "event_location" => "Event location",
            "event_start_date" => "Event start date",
            "event_start_and_end_time" => "Event start & end time",
            "already_participated" => "Already participated",
            "select" => "Select",
            "upload_document" => "Upload Document",
            "upload_image" => "Upload Image",
            "cancel" => "Cancel",
            "hold_text" => "The contest is put on hold because minimum participants condition is not fullfilled till the application end date, so we suggest you to edit and extend the dates of the contest or cancel and refund the participation amount of existing particpants if any.",
            "if_you_cancel_text" => "If you cancel now, you will get",
            "as" => "as",
            "will_be_dedcuted_text" => "will be deducted according to the cancellation policy.",
            "prize" => "Prize",
            "meeting_link" => "Meeting Link",
            "ask" => "Ask",
            "upload" => "Upload",
            "rejected" => "Rejected",
            "application_status" => "Aplication Status",
            "sub_category" => "Sub Category",
            "category" => "Category",
            "file_required_to_participate" => "File Required To Participate",
            "only_for_students" => "Only for students",
            "only_for_companies" => "Only for companies",
            "only_for_normal_user" => "Only for normal user",
            "third_prize" => "Third Prize",
            "second_prize" => "Second Prize",
            "view_on_map" => "View On Map",
            "off" => "OFF",
            "status" => "Status : "
        ],
        "my_contests" => [
            "my_contest" => 'My contest',
            "created_by_me" => "Created by me",
            "participants" => "participants",
            "free" => "Free",
            "date" => 'Application Date : ',
            "start_date" => "Start Date : ",
            "contest_type" => 'Contest Type',
            "data_not_found" => "Data Not Found.",
            "off" => "OFF"
        ],
        "target_audience" => [
            // "all" => "All",
            "students" => "Students",
            "companies" => "Companies",
            "normal_users" => "Normal Users"
        ],
        "manage_participants" => [
            "applied_on" => 'Applied On : ',
            "data_not_found" => "Data Not Found.",
            "reason" => "Reason",
            "approve" => "Approve",
            "reject" => "Reject",
            "cancel" => "Cancel",
            "refund" => "Refund",
            "select_as_winner" => "Select as winner",
            "first_place" => "First Place",
            "second_place" => "Second Place",
            "third_place" => "Third Place",
            "done" => "Done",
        ],
        "application_status" => [
            "pending" => "Pending",
            "joined" => "Joined",
            "rejected" => "Rejected",
            "canceled" => "Canceled",
            "completed" => "Completed",
            "document_updated" => "Document Updated (Verification Pending)",
            "hold" => "On Hold",
            "verified" => "Verified",
        ],

        "contest_filter" => [
            "user_type" => "Available For",
            "all" => "All",
            "student" => "Student",
            "contest" => "Contest",
            "categories" => "Categories",
            "subCategories" => "Sub Categories",
            "free_subscription_only" => "Free Subscription Only",
            "free_cancellation" => "Free Cancellation",
            "choose" => 'Choose',
            "price_type" => "Price Type",
            "date" => "Date",
            "contest_type" => "Contest type",
            "from" => "From",
            "to" => "To",
            "at_location" => "At Location",
            "online" => "Online",
            "event_type" => "Mode",
            "country" => "Country",
            "city" => "City",
            "distance" => "Distance",
            "apply_filter" => "Apply filter"
        ],
        "buy_contest_screen" => [
            "sub_total" => "Sub-total :",
            "shipping" => "Shipping :",
            "tax" => "Tax :",
            "total" => "Total :",
            "vat" => "Vat",
            "stripe" => "Stripe",
            "klarna" => "Klarna",
            "swish" => "Swish",
            "reward_points" => "Reward Points",
            "apply" => "Apply",
            "available_reward_points" => "Available Reward Points",
            "participants" => "participants",
            "date" => 'Start Date : ',
            "contest_type" => 'Contest Type',
            "use_my_reward_points" => "Use my reward points",
            "new_card" => "New payment card",
            "default" => 'default',
            "i_agree_to_all" => "By clicking pay now, you agree to our",
            "terms_of_use_and_privacy_policy" => "Terms of Use and Privacy Policy",
            "pay_now" => "Pay Now",
            "your_subscription_was_submitted_successfully" => "Your Subscription Was Submitted Successfully",
            "back_to_browsing" => "Back to browsing",
            "event_type" => "Event Type",
            "select" => "Select",
            "upload_document" => "Upload Document",
            "upload_image" => "Upload Image",
            "application_date" => "Application Date : "
        ],
        "contest_view_all" => [
            "all" => "All",
            "search_results" => "Search Results",
            "participants" => "Participants",
            "date" => 'Application Date : ',
            "free" => "Free",
            "start_date" => "Start Date : ",
            "contest_type" => "Contest Type",
            "data_not_found" => "Data Not Found.",
        ],
        "education_level" => [
            "pre_primary" => "Pre Primary",
            "primary" => "Primary",
            "high_school" => "High School",
            "higher_secondary" => "Higher Secondary",
            "graduation" => "Graduation",
            "post_graduation" => "Post Graduation",
            "phd" => "PHD",
        ],
        "contest_participating_in_it" => [
            "my_contest" => 'My contest',
            "participating_in_it" => "Participating in it",
            "participants" => "participants",
            "date" => 'Application Date : ',
            "start_date" => "Start Date : ",
            "contest_type" => 'Contest Type',
            "data_not_found" => "Data Not Found.",
            "free" => "Free",
            "status" => "Status : ",
            "off" => "OFF"
        ],
        "work_experience_edit_page" => [
            "new_work_experience" => "New Work Experience",
            "from" => "From",
            "till" => "Till",
            "ongoing" => "Ongoing",
            "main_activities_and_responsibilities" => "Main Activities And Responsibilities",
            "describe_your_tasks_and_responsibilities" => "Describe your tasks and responsibilities",
        ],
        "education_and_training_edit_page" => [
            "new_education_and_training_experience" => "New Education and Training Experience",
            "from" => "From",
            "till" => "Till",
            "ongoing" => "Ongoing",
            "main_activities" => "Main Activities",
            "describe_main_activities" => "Describe main activities",

        ],
        "request_camera_permission" => [
            "app_camera_permission" => "App Camera Permission",
            "app_needs_access_to_your_camera" => "App needs access to your camera",
            "ask_me_later" => "Ask Me Later",
            "cancel" => "Cancel",
            "oK" => "OK",
            "select_photo" => "Select Photo",
            "launch_camera" => "Launch camera",
            "load_from_gallery" => "Load from gallery",
        ],
        "contact_us" => [
            "contact_us" => "Contact Us",
            "message" => "Message",
            "send" => "Send",
            "your_email" => "Your Email",
            "message_title" => "Message Title",
            "your_message" => "Your Message",
            "upload_documents" => "Upload Documents",
            "images" => "Images",
        ],
        "change_password" => [
            "change_password" => "Change Password",
            "change" => "Change",
            "password" => "Password",
            "old_password" => "Old Password",
            "new_password" => "New Password",
            "repeat_new_password" => "Repeat New Password",
            "set_new_password" => "Set New Password",
        ],
        "action_sheet_comp" => [
            "search_add_new" => "Search/Add New",
            "search" => "Search"
        ],
        "mark_as_sold_modal" => [
            "reason" => "Reason",
            "where_did_you_sold_this_item" => "Q 1) Where did you sold this ?",
            "student_store" => "Student Store",
            "outside" => "Outside",
            "in_how_much_time_did_you_able_to_sold_this" => "Q 2) In how many days were you able to sold this ?",
            "five_to_seven" => "5 - 7",
            "three_to_five" => "3 - 5",
            "one_to_three" => "1 - 3",
            "more" => "More than 7",
            "done" => "Done"
        ],
        "notification_navigation_alert" => [
            "sign_in_as_student_to_view_this" => "Sign in as student to view this",
            "sign_in_as_service_provider_to_view_this" => "Sign in as service provider to view this",
            "switch_to_buyer_to_view_this" => "Switch to buyer to view this",
            "switch_to_seller_to_view_this" => "Switch to seller to view this",
            "sign_in_to_view_this" => "Sign in to view this",
        ],
        "assign_winner_modal" => [
            "select_winner" => "Select Winner",
            "first_place" => "First Place",
            "second_place" => "Second Place",
            "third_place" => "Third Place",
        ],
        "winner_places" => [
            "first" => "First Place",
            "second" => "Second Place",
            "third" => "Third Place",
            "first_winner" => "First Place",
            "second_winner" => "Second Place",
            "third_winner" => "Third Place",
        ],
        "product_status" => [
            "0" => "Pending",
            "2" => "Verified",
            "3" => "Rejected",
        ],
        "success_screen" => [
            "go_back" => "Go back",
            "congratulations" => "Congratulations !!"
        ],
        "address_lan_skills_edit_page" => [
            "basic_details" => "Basic Details",
            "skills" => "Skills",
            "languages" => "Languages",
            "job_environment" => "Job Environment",
            "years_of_experience" => "Years Of Experience",
            "address" => "Address",
            "others" => "Others",
            "present_my_CV" => "Present My CV",
            "save" => "Save",
            "select" => "Select",
            "choose" => "Choose",
            "online" => "Online",
            "at_office" => "At Office",
        ],
        "normal_user_congratulations" => [
            "congratulations" => "Congratulations",
            "your_request_has_been_submitted_successfully" => " Your Request Has Been Submitted Successfully.",
            "start_exploring_now" => "Start exploring now",
        ],
        "provider_address" => [
            "save" => "Save",
            "set_as_default_address" => "Set as default address.",
            "search" => "Search",
        ],
        "student_congratulations" => [
            "congratulations" => "Congratulations",
            "your_request_has_been_submitted_successfully" => "Your Request Has Been Submitted Successfully.",
            "start_exploring_now" => "Start exploring now",
        ],
        "OTP_verification" => [
            "we_have_sent_a_code_to" => "We Have sent a code to",
            "change_email" => "Change Email",
            "or" => "Or",
            "resend_code" => "Resend Code",
            "change_phone_number" => "Change Phone Number",
            "resend_SMS" => "Resend SMS",
            "terms_of_use_privacy_policy" => "Terms of Use, Privacy Policy",
        ],
        "student_product_flat_list_comp" => [
            "online" => "Online",
            "off" => "OFF"
        ],
        "product_home" => [
            "inside_all_text" => "It's all inside ... search",
            "promotions" => "Promotions",
            "view_all" => "View all",
            "products" => "Products",
            "services" => "Services",
            "best_selling_products" => "Best selling",
            "most_popular_services" => "Most popular services",
            "top_rated_service_providers" => "Top rated service providers",
            "new_service_providers" => "New service providers",
            "latest_used_products" => "Latest used products",
            "student_popular_services" => "Student popular services",
            "company_listing" => "Company Listing",
            "student_listing" => "Student Listing",
            "data_not_found" => "Data Not Found.",
            "product" => "Product",
            "service" => "Service",
            "most_popular_products" => "Most popular",
            "company_top_rated_products" => "Top rated",
            "company_recently_added_products" => "Recently added",
            "company_random_products" => "Random",
        ],
        "location_permission_messages" => [
            "we_need_to_access_your_location" => "We need to access your location",
            "we_use_your_location_to_show_where_you_are_on_the_map" => "We use your location to show where you are on the map",
            "ok" => "OK",
            "cancel" => "Cancel",
            "please_provide_location_permission_to_continue" => 'Please provide location permission to continue',
        ],
        "shipping_crieteria" => [
            "shipping_crieteria" => "Shipping Crieteria",
            "done" => 'Done',
            "deduct" => "Deduct",
            "min_price" => "Min Price",
            "max_price" => "Max Price",
            "message_enter_all_shipping_crieteria_and_their_deducted_amount" => "Please enter shipping crieteria ranges and their deducted amount",
            "message_please_enter_valid_shipping_crieteria_ranges" => "Please enter valid shipping crieteria ranges",
            "message_enter_different_crieteria_ranges" => "Please enter different crieteria ranges",
        ],
	"reward_point_share_screen" => [
            "share_reward" => "Share Your Reward Points",
            "mobile" => 'Mobile number',
            "reward" => "Reward ponits",
            "max_reward_message" => "Maximum reward points that you can share are : ",
            "send" => "Send",
            "zero_reward_message" => "Presently you have zero reward point",
        ],
 	"module_names" => [
            "Product" => "Product",
            "Service" => "Service",
            "Book" => "Book",
            "Job" => "Job",
            "Contest" => "Contest",
            "Events" => "Events",
        ],
    ];
		

    foreach ($labels as $key => $label) {
            if(LabelGroup::where('name',$key)->count() > 0)
            {
                $labelGroup = LabelGroup::where('name',$key)->first();
            }
            else
            {
                $labelGroup = new LabelGroup;
                $labelGroup->name                = $key;
                $labelGroup->status              = 1;
                $labelGroup->save();
            }

            foreach ($label as $key1 => $value) {
                if(Label::where('label_group_id',$labelGroup->id)->where('label_name',$key1)->where('language_id',1)->count() == 0)
                {
                    $label = new Label;
                    $label->label_group_id         = $labelGroup->id;
                    $label->language_id            = 1;
                    $label->label_name             = $key1;
                    $label->label_value            = $value;
                    $label->status                 = 1;
                    $label->save(); 
                }
            }
        }
		$userTypes = UserType::where('id','!=', 1)->get();
		return response()->json(prepareResult(false, $userTypes, getLangByLabelGroups('messages','message_user_type_list')), config('http_response.success'));		
	}

	public function getServiceProviderType(Request $request)
	{
		$serviceProviderTypes = ServiceProviderTypeDetail::select('service_provider_type_details.*')
		->join('service_provider_types', function ($join) {
			$join->on('service_provider_type_details.service_provider_type_id', '=', 'service_provider_types.id');
		})
		->where('service_provider_types.registration_type_id',$request->registration_type_id)
		->where('language_id',$request->language_id)
		->get();
		return response()->json(prepareResult(false, $serviceProviderTypes, "Service-Provider-Types retrieved successfully! "), config('http_response.success'));
	}

	public function getRegistrationType(Request $request)
	{
		$registrationTypes = RegistrationTypeDetail::select('registration_type_details.*')
		->join('registration_types', function ($join) {
			$join->on('registration_type_details.registration_type_id', '=', 'registration_types.id');
		})
		->where('language_id',$request->language_id)
		->get();
		return response()->json(prepareResult(false, $registrationTypes, "Registration-Types retrieved successfully! "), config('http_response.success'));
	}

	public function userQr($qr_code)
	{
		$userInfo = User::where('qr_code_number', $qr_code)->with('userType','studentDetail')->first();
		if($userInfo)
		{
			return response()->json(prepareResult(false, $userInfo, "Registration-Types retrieved successfully! "), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function labelByGroupName(Request $request)
	{
		$getLang = $request->language_id;
		$labelGroups = LabelGroup::select('id','name')
		->where('name', $request->group_name)
		->with(['labels' => function($q) use ($getLang) {
			$q->select('id','label_group_id','language_id','label_name','label_value')
			->where('language_id', $getLang);
		}])
		->first();
		if($labelGroups)
		{
			return response()->json(prepareResult(false, $labelGroups, getLangByLabelGroups('messages', 'messages_label_group_info')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function getInfoByGroupAndLabelName(Request $request)
	{
		$group_name = $request->group_name;
		$getLang 	= $request->language_id;
		$label_name = $request->label_name;
		$getLabelGroup = LabelGroup::select('id')
		->with(['returnLabelNames' => function($q) use ($getLang, $label_name) {
			$q->select('id','label_group_id', 'label_name','label_value')
			->where('language_id', $getLang)
			->where('label_name', $label_name);
		}])
		->where('name', $group_name)
		->first();

		if($getLabelGroup)
		{
			return response()->json(prepareResult(false, $getLabelGroup, getLangByLabelGroups('messages', 'messages_label_group_info')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function updateUserLanguage(Request $request)
	{
		$getLang = env('APP_DEFAULT_LANGUAGE', '1');
		if(Auth::check())
		{
			$getLang = Auth::user()->language_id;
			if(empty($getLang))
			{
				$getLang = env('APP_DEFAULT_LANGUAGE', '1');
			}
			$userLangUpdate = User::find(Auth::id());
			$userLangUpdate->language_id = $request->language_id;
			$userLangUpdate->save();
		}

		if($getLang)
		{
			return response()->json(prepareResult(false, $getLang, getLangByLabelGroups('messages', 'messages_language_changed')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}

	public function getLanguages()
	{
		$languages = Language::where('status', 1)->get();
		if($languages)
		{
			return response()->json(prepareResult(false, $languages, getLangByLabelGroups('messages', 'messages_language_list')), config('http_response.success'));
		}
		return response()->json(prepareResult(true, [], getLangByLabelGroups('messages', 'message_error')), config('http_response.internal_server_error'));
	}


	

	public function appSettings()
	{
		try
		{
			$appSetting = AppSetting::first();
			return response(prepareResult(false, $appSetting, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getRewardPointCurrencyValue()
	{
		try
		{
			$customer_rewards_pt_value = AppSetting::first(['single_rewards_pt_value','customer_rewards_pt_value','vat']);
			return response(prepareResult(false, $customer_rewards_pt_value, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function page( Request $request,$slug)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}
			$page = Page::where('slug',$slug)->where('language_id',$language_id)->first();
			return response(prepareResult(false, $page, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getFaqs(Request $request)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}

			$faq = FAQ::where('language_id',$language_id)->get();
			return response(prepareResult(false, $faq, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getSliders(Request $request)
	{
		try
		{
			if(!empty($request->language_id))
			{
				$language_id = $request->language_id;
			}
			else
			{
				$language_id = 1;
			}

			$faq = Slider::where('language_id',$language_id)->get();
			return response(prepareResult(false, $faq, getLangByLabelGroups('messages','message_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	

	public function getLabels(Request $request)
	{
		try
		{
			$getLang = $request->language_id;
			$labelGroups = LabelGroup::select('id','name')
			->with(['labels' => function($q) use ($getLang) {
				$q->select('id','label_group_id','language_id','label_name','label_value')
				->where('language_id', $getLang);
			}])->orderBy('auto_id','ASC')->get();
			return response(prepareResult(false, $labelGroups, getLangByLabelGroups('messages','message_label_group_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}

	public function getLanguageListForDDL()
	{
		$languages = LangForDDL::orderBy('name', 'ASC')->get();
		return response()->json(prepareResult(false, $languages, getLangByLabelGroups('messages','message_user_type_list')), config('http_response.success'));
	}

	public function gtinIsbnSearch(Request $request)
	{
		$products = ProductsServicesBook::where('gtin_isbn','like', '%'.$request->gtin_isbn.'%')->orderBy('created_at','desc')->get();
		return response()->json(prepareResult(false, $products, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}

	public function getEducationInstitutes(Request $request) {
		$educationInstitutes = StudentDetail::groupBy('institute_name')->get(['institute_name']);
		return response()->json(prepareResult(false, $educationInstitutes, getLangByLabelGroups('messages','message_products_services_book_list')), config('http_response.success'));
	}


	public function getJobTags()
	{
		try
		{
			$jobTags = JobTag::where('title', '!=', null)->select('title')->groupBy('title')->get();
			return response(prepareResult(false, $jobTags, getLangByLabelGroups('messages','messages_job_tags_list')), config('http_response.success'));
		}
		catch (\Throwable $exception) 
		{
			return response()->json(prepareResult(true, $exception->getMessage(), getLangByLabelGroups('messages','message_error')), config('http_response.internal_server_error'));
		}
	}
}