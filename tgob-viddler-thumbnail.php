<?php
/*
Plugin Name: Two Guys On Beer - Viddler Extensions
Plugin URI: http://twoguysonbeer.com
Description: Extensions to the Viddler API for the Two Guys On Beer Website
Author: Dave Martorana
Version: 0.0.2
Author URI: http://davemartorana.com
*/

//include_once('../wp-content/plugins/the-viddler-wordpress-plugin/phpviddler/phpviddler.php');

$VIDDLER_API_KEY = '<YOUR KEY>';

/*
Get a one paragraph excerpt
*/
function tgob_excerpt()
{
    echo get_tgob_excerpt();
}

function get_tgob_excerpt()
{
    $content = _get_tgob_post_without_player_before_processing();

    // Go in a single paragraph
    $pos = strpos($content, "\n");
    $content = substr($content, 0, $pos);

    // Pass through regular content filters
    // From http://codex.wordpress.org/Template_Tags/the_content#Alternative_Usage
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);    

    return $content;
}

/*
Get the the video thumbnail
*/
function tgob_viddler_thumbnail($width=0, $height=0, $class='', $default_image='') 
{
    echo get_tgob_viddler_thumbnail($width, $height, $class, $default_image);
}

function get_tgob_viddler_thumbnail($width=0, $height=0, $class='', $default_image='') 
{
    global $VIDDLER_API_KEY;
    include_once('phpviddler/phpviddler.php');
    $tgob_viddler = new Phpviddler($VIDDLER_API_KEY);

    $thumbnail_link = null;
    $video_id = _get_plugin_id_from_content();

    // If we have an ID, hit up the viddler API
    if ($video_id != null) {
        $details = $tgob_viddler->video_details($video_id);
        $thumbnail_link = $details['video']['thumbnail_url'];
    }
    
    if ($thumbnail_link == '') {
        $thumbnail_link = $default_image;
    }
    
    if ($thumbnail_link != '') {
        $link = "<img src=\"$thumbnail_link\" class=\"$class\"";
        if ($width > 0) {
            $link .= " width=\"$width\"";
        }
        if ($height > 0) {
            $link .= " height=\"$height\"";
        }
        
        $link .= ">";
        
        return $link;
    }
    else {
        return '';
    }
}

/*
Get the the video player using oEmbed
*/
function tgob_viddler_player_embed($max_width=437, $type='player') 
{
    echo get_tgob_viddler_player_embed($max_width, $type);
}

function get_tgob_viddler_player_embed($max_width=437, $type='player') 
{
    global $VIDDLER_API_KEY;
    include_once('phpviddler/phpviddler.php');
    $tgob_viddler = new Phpviddler($VIDDLER_API_KEY);

    $embed_code = null;
    $video_id = _get_plugin_id_from_content();

    // If we have an ID, hit up the viddler API
    if ($video_id != null) {
        $embed_code = _get_video_new_embed($video_id, $max_width, $type);

/*
        $details = $tgob_viddler->video_details($video_id);
        $video_url = $details['video']['url'];
        $embed_code = _get_video_oEmbed($video_url, $max_width, $type);
*/

        
    }
    if ($embed_code != null) {
        return $embed_code;
    }
    else {
        return '';
    }
}

/* 
Get the post WITHOUT the video embed
*/

function tgob_post_without_player()
{
    echo get_tgob_post_without_player();
}

function get_tgob_post_without_player()
{
    $content = _get_tgob_post_without_player_before_processing();

    // Pass through regular content filters
    // From http://codex.wordpress.org/Template_Tags/the_content#Alternative_Usage
    $content = apply_filters('the_content', $content);
    $content = str_replace(']]>', ']]&gt;', $content);

    // Print
    return $content;
}

/*
Private: Get the ID from the viddler plugin code
*/
function _get_plugin_id_from_content() 
{
    $video_id = null;

    $pos = -1;
    $endpos = -1;
    
    // Get the raw post
    $raw_content = get_the_content();
    
    // Look for anything that looks like this in the content:
    // [viddler id-770aa2b9 h-343 w-535]
    $pos = strpos($raw_content, '[viddler id-');
    if ($pos !== FALSE) {
        $pos = $pos + 12;
        $endpos = strpos($raw_content, ' ', $pos);
    }
        
    if ($pos !== FALSE && $endpos !== FALSE && $endpos > $pos) {
        $video_id = substr($raw_content, $pos, $endpos - $pos);
    }

    return $video_id;
}


function _get_tgob_post_without_player_before_processing()
{
    $content = get_the_content();
    
    // Look for and remove any embed
    $pos = strpos($content, '[viddler');
    $endpos = FALSE;
    
    if ($pos !== FALSE) {
        // Find the end position
        $endpos = strpos($content, ']', $pos);

        if ($endpos !== FALSE && $endpos > $pos) {
            $content = substr($content, 0, $pos) . substr($content, $endpos + 1);
        }
    }
    
    return $content;
}


/* 
    Stolen from (and modified):
    
	#  Viddler API / PHP Wrapper
	#  By: Colin Devroe | cdevroe@viddler.com
	
	With permission under MIT license.
    
    Private: Get the oEmbed code from Viddler Labs
*/
function _get_video_oEmbed($videourl,$maxwidth,$type='simple',$format='html') {
	$reqURL = 'http://developers.viddler.com/services/oembed/?format='.$format.'&url='.$videourl.'&maxwidth='.$maxwidth.'&type='.$type;

	$curl_handle = curl_init();
	curl_setopt ($curl_handle, CURLOPT_URL, $reqURL);
	curl_setopt ($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($curl_handle, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt ($curl_handle, CURLOPT_HEADER, 0);
	curl_setopt ($curl_handle, CURLOPT_TIMEOUT, 0);
	$embedcode = curl_exec($curl_handle);
	
	if (!$response)	$response = curl_error($curl_handle);
	curl_close($curl_handle);
	
	return $embedcode;
}

function _get_video_new_embed($video_id,$maxwidth,$type='simple',$format='html')
{
    $height = ceil($maxwidth * (9/16));
    $player_height = 0;
 
    $movie_url = '';
    if ($type == 'simple') {
        $movie_url = 'http://www.viddler.com/simple/'.$video_id.'/';

        // Height of simple player is 20 px.
        $player_height = ceil($maxwidth * (9/16)) + 20;        
    }
    else {
        $movie_url = 'http://www.viddler.com/player/'.$video_id.'/';
        
        // Height of regular player is 42 px.
        $player_height = ceil($maxwidth * (9/16)) + 42;
    }
    
    $iplayer_url = 'http://www.viddler.com/iplayer/'.$video_id.'/';

    $embed_code = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
     width="'.$maxwidth.'" height="'.$player_height.'" id="viddlerplayer-'.$video_id.'" wmode="transparent" quality="high">
     <param name="wmode" value="transparent" />
     <param name="movie" value="'.$movie_url.'" />
     <param name="quality" value="high" />
     <param name="allowScriptAccess" value="always" />
     <param name="allowFullScreen" value="true" />
     <param name="name" value="viddlerplayer-18f30dfd" />
     <param name="flashvars" value="autoplay=f&disablebranding=f">
     <!--[if !IE]><!-->
     <object type="application/x-shockwave-flash" 
     data="'.$movie_url.'" width="'.$maxwidth.'" height="'.$player_height.'">
     <object>
     <video src="'.$iplayer_url.'" 
     type="video/mp4" width="'.$maxwidth.'" height="'.$height.'"
     poster="'.$iplayer_url.'thumb/" controls="controls"></video>
     </object></object><!--<![endif]--></object>';
     
     return $embed_code;
}

?>
