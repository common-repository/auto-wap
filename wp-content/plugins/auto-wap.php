<?php
/*
Plugin Name: auto-wap
Plugin URI: http://serge.matveenko.ru/auto-wap/
Description: .
Author: Serge Matveenko
Version: 0.8 "pre-alpha"
Author URI: http://serge.matveenko.ru/cv/
*/

$lig_wap_res = override_function ('load_template', '$file', 'return lig_wap_load_template($file)');

/**
 * Plugin function for filter "the_content"
 * Strips all tags except "<img>" and "<br>" to make wml page valid
 * @todo make option to disable this feature
 * 
 * @param String $output
 * @return String
 */
function lig_wap_strip_paragraphs($output) {
	if ( 'wml' == LIG_WAP_FOLDER ) {
		return strip_tags($output,'<img>,<br>');
	} else {
		return $output;
	}
}

/**
 * Plugin function for filter "wp_list_pages"
 * Strips all tags except "<a>" in the pages listing to make wml page valid
 *
 * @param String $output
 * @return String
 */
function lig_wap_strip_list_tags($output) {
	if ( 'wml' == LIG_WAP_FOLDER ) {
		return strip_tags($output,'<a>');
	} else {
		return $output;
	}
}

/**
 * replacement for standard wp-core-function load_template($file)
 *
 * @param String $file
 */
function lig_wap_load_template($file) {
	global $posts, $post, $wp_did_header, $wp_did_template_redirect, $wp_query,
		$wp_rewrite, $wpdb;

	if (defined('LIG_WAP_FOLDER')) {
		$file = lig_wap_replace_template ($file);
	}

	extract($wp_query->query_vars);

	require_once($file);
}

/**
 * replacement for standard wp-function get_query_template($type)
 *
 * @param String $type
 * @return file path
 */
function lig_wap_get_query_template($type) {
	add_filter("{$type}_template", 'lig_wap_replace_template');
	return get_query_template($type);
}

/**
 * replacement for standard wp-function get_404_template()
 *
 * @return file path
 */
function lig_wap_get_404_template() {
	return lig_wap_get_query_template('404',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_archive_template()
 *
 * @return file path
 */
function lig_wap_get_archive_template() {
	return lig_wap_get_query_template('archive',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_author_template()
 *
 * @return file path
 */
function lig_wap_get_author_template() {
	return lig_wap_get_query_template('author',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_category_template()
 *
 * @return file path
 */
function lig_wap_get_category_template() {
	$template = '';
	if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/category-" . get_query_var('cat') . '.php') )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/category-" . get_query_var('cat') . '.php';
	else if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/category.php") )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/category.php";

	return apply_filters('category_template', $template);
}

/**
 * replacement for standard wp-function get_date_template()
 *
 * @return file path
 */
function lig_wap_get_date_template() {
	return lig_wap_get_query_template('date',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_home_template()
 *
 * @return file path
 */
function lig_wap_get_home_template() {
	$template = '';

	if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/home.php") )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/home.php";
	else if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/index.php") )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/index.php";

	return apply_filters('home_template', $template);
}

/**
 * replacement for standard wp-function get_page_template()
 *
 * @return file path
 */
function lig_wap_get_page_template() {
	global $wp_query;

	$id = $wp_query->post->ID;
	$template = get_post_meta($id, '_wp_page_template', true);

	if ( 'default' == $template )
		$template = '';

	if ( ! empty($template) && file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/$template") )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/$template";
	else if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/page.php") )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/page.php";
	else
		$template = '';

	return apply_filters('page_template', $template);
}

/**
 * replacement for standard wp-function get_paged_template()
 *
 * @return file path
 */
function lig_wap_get_paged_template() {
	return lig_wap_get_query_template('paged',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_search_template()
 *
 * @return file path
 */
function lig_wap_get_search_template() {
	return lig_wap_get_query_template('search',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_single_template()
 *
 * @return file path
 */
function lig_wap_get_single_template() {
	return lig_wap_get_query_template('single');
}

/**
 * replacement for standard wp-function get_atachment_template()
 *
 * @return file path
 */
function lig_wap_get_attachment_template() {
	global $posts;
	$type = explode('/', $posts[0]->post_mime_type);
	if ( $template = lig_wap_get_query_template($type[0],LIG_WAP_FOLDER) )
		return $template;
	elseif ( $template = lig_wap_get_query_template($type[1],LIG_WAP_FOLDER) )
		return $template;
	elseif ( $template = lig_wap_get_query_template("$type[0]_$type[1]",LIG_WAP_FOLDER) )
		return $template;
	else
		return lig_wap_get_query_template('attachment',LIG_WAP_FOLDER);
}

/**
 * replacement for standard wp-function get_comments_popup_template()
 *
 * @return file path
 */
function lig_wap_get_comments_popup_template() {
	if ( file_exists( TEMPLATEPATH ."/".LIG_WAP_FOLDER. '/comments-popup.php') )
		$template = TEMPLATEPATH ."/".LIG_WAP_FOLDER. '/comments-popup.php';
	else
		$template = get_theme_root() . '/default/comments-popup.php';

	return apply_filters('comments_popup_template', $template);
}
/**
 * replacement for standard wp-function template_select()
 *
 */
function lig_wap_template_select (){
	if ( is_feed() || is_trackback() ) {
	} else if ( is_404() && $template = lig_wap_get_404_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_search() && $template = lig_wap_get_search_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_home() && $template = lig_wap_get_home_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_attachment() && $template = lig_wap_get_attachment_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_single() && $template = lig_wap_get_single_template(LIG_WAP_FOLDER) ) {
		if ( is_attachment() )
		add_filter('the_content', 'prepend_attachment');
		include($template);
		exit;
	} else if ( is_page() && $template = lig_wap_get_page_template(LIG_WAP_FOLDER) ) {
		if ( is_attachment() )
		add_filter('the_content', 'prepend_attachment');
		include($template);
		exit;
	} else if ( is_category() && $template = lig_wap_get_category_template(LIG_WAP_FOLDER)) {
		include($template);
		exit;
	} else if ( is_author() && $template = lig_wap_get_author_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_date() && $template = lig_wap_get_date_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_archive() && $template = lig_wap_get_archive_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_comments_popup() && $template = lig_wap_get_comments_popup_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( is_paged() && $template = lig_wap_get_paged_template(LIG_WAP_FOLDER) ) {
		include($template);
		exit;
	} else if ( file_exists(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/index.php") ) {
		if ( is_attachment() )
		add_filter('the_content', 'prepend_attachment');
		include(TEMPLATEPATH ."/".LIG_WAP_FOLDER. "/index.php");
		exit;
	}
}
/**
 * Main method for selecting target theme
 *
 * @return theme name
 */
function lig_wap_check () {
	$accept = $_SERVER['HTTP_ACCEPT'];
	$wmlHack = $_GET['tpl'];
	if (strpos ($accept, 'vnd.wap') || $wmlHack == 'wml' || $wmlHack == 'xhtml'){
		if (strpos ($accept, 'xhtml') && !($wmlHack == 'wml') ){
			return 'xhtml';
		}
		else{
			return 'wml';
		}
	}
	else{
		if (strpos ($accept, 'html')){
			return 'html';
		}
		else{
			return 'text';
		}
	}
}
/**
 * Plugin function for action "template_redirect"
 * Replaces standard HTTP header "Content-Type" to one needed for target theme
 *
 */
function lig_wap_redir () {
	define ('LIG_WAP_FOLDER', lig_wap_check (), true);
	switch (LIG_WAP_FOLDER){
		case 'html':
			break;
		case 'wml':
			header ('Content-Type: text/vnd.wap.wml;charset=UTF-8', true);
		case 'text':
			header ('Content-Type: text/plain;charset=UTF-8', true);
		default:
			lig_wap_template_select ();
	}
}

/**
 * Plugin function for filter "comments_template"
 * Rewrites file path to match target theme folder
 *
 * @param String $includePath
 * @return String
 */
function lig_wap_replace_template ($includePath) {
	return str_replace ( TEMPLATEPATH, TEMPLATEPATH.'/'.LIG_WAP_FOLDER, $includePath );
}

add_action('template_redirect', 'lig_wap_redir');
add_filter('comments_template', 'lig_wap_replace_template');

add_filter('wp_list_pages', 'lig_wap_strip_list_tags');

add_filter('the_content', 'lig_wap_strip_paragraphs');

?>
