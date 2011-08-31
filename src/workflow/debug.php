<?php
# $Revision: 2305 $
include_once("include/functions_misc.php");

session_start();

if ( $_GET['section'] == 'my_queue')
    readfile('debug_my_queue10.html');
else if ( $_GET['section'] == 'roi_list')
    readfile('debug_roi_list.html');
else if ( $_GET['section'] == 'smallmap')
    readfile('debug_openlayers_map1.html');
else if ( $_GET['section'] == 'item_browser')
    readfile('debug_item_browser1.html');
else if ( $_GET['section'] == 'parse_text2')
    readfile('parse_text2.html');
else if ( $_GET['section'] == 'parse_text3')
    readfile('parse_text3.html');
else if ( $_GET['section'] == 'parse_content_0')
    readfile('parse_form_content0a.html');
else if ( $_GET['section'] == 'parse_content_1')
    readfile('parse_form_content1.html');
else if ( $_GET['section'] == 'parse_content_2')
    readfile('parse_form_content2.html');
else if ( $_GET['section'] == 'parse_content_3')
    readfile('parse_form_content3.html');
else if ( $_GET['section'] == 'contextmenu')
    readfile('debug_jeegoo_menu.html');
else if ( $_GET['section'] == 'transcribetext')
    readfile('debug_transcribe_text.txt');
else if ( $_GET['section'] == 'parse_dropdown')
    readfile('debug_parse_dropdown.txt');
else if ( $_GET['section'] == 'roi' )
{
		$url = "http://dev.apiaryproject.org/drupal/apiary/ajaxrequest/getImageROIList/ap-image:Image-37/0";
/*		$send = curl_init();
		curl_setopt($send, CURLOPT_URL, $url);
		curl_setopt($send, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($send, CURLINFO_HEADER_OUT, 1);
		$output = curl_exec($send);
		if(curl_errno($send))
		{
		    echo "error getting html";
			return false;
		}
		else{
			$info = curl_getinfo($send);
			if($info['http_code']==200){
				echo $output;
			}
			else
			{
			    print_r($info);
			return false;
		    }
		}*/
	$options = array(
		CURLOPT_RETURNTRANSFER => true,     // return web page
		CURLOPT_HEADER         => false,    // don't return headers
		CURLOPT_FOLLOWLOCATION => true,     // follow redirects
		CURLOPT_ENCODING       => "",       // handle compressed
		CURLOPT_USERAGENT      => "spider", // who am i
		CURLOPT_AUTOREFERER    => true,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
		CURLOPT_TIMEOUT        => 120,      // timeout on response
		CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
        CURLOPT_COOKIEFILE     => dirname(__FILE__). '/cookie.txt',
        CURLOPT_COOKIEJAR      => dirname(__FILE__). '/cookie.txt'
	);

	$ch      = curl_init( $url );
	curl_setopt_array( $ch, $options );
	$content = curl_exec( $ch );
	$err     = curl_errno( $ch );
	$errmsg  = curl_error( $ch );
	$header  = curl_getinfo( $ch );
	curl_close( $ch );

	$header['errno']   = $err;
	$header['errmsg']  = $errmsg;
	$header['content'] = $content;
	print_r($header);
	print_r($_SESSION);

}
else if ( $_GET['section'] == 'metadata' )
{
    echo "{\"h\":\"10212\",\"w\":\"7212\",\"URL\":\"http:\/\/webroot.local\/practice\/openlayers\/23639.jp2\",\"rft_id\":\"apiary:jpeg2000\/23639\",\"sourceURL\":\"http:\/\/webroot.local\/practice\/openlayers\/23639.jp2\"}";
    die();
}



?>