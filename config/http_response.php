<?php
return [
	'success'          			=> 200,
	'created'                	=> 201,
	'accepted'               	=> 202,
	'not_modified'           	=> 304,
	'bad_request'            	=> 400,
	'unauthorized'           	=> 401,
	'payment_required'       	=> 402,
	'forbidden'              	=> 403,
	'not_found'              	=> 404,
	'method_not_allowed'     	=> 405,
	'internal_server_error'  	=> 500,
	'unprocessable_entity'   	=> 422,
	'service_unavailable'    	=> 503,
	'no_content'             	=> 204,

	// 'message_success'			=> App\Models\Label::where('label_name','message_success')->first()->label_value,
	// 'message_failed'			=> App\Models\Label::where('label_name','message_failed')->first()->label_value,
];