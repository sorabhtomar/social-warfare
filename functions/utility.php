<?php
/**
 * General utility helper functions.
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2016, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     2.1.0
 */

/**
 * Get the current site's URL.
 *
 * @since  2.1.0
 * @return string The current site's URL.
 */
function swp_get_site_url() {

	$domain = get_option( 'siteurl' );

	if ( is_multisite() ) {
		$domain = network_site_url();
	}

	return $domain;
}

/**
 *  Round a number to the appropriate thousands.
 *
 * @since  unknown
 * @access public
 * @param  float $val The float to be rounded.
 * @return float A rounded number.
 */
function swp_kilomega( $val ) {
	global $swp_user_options;

	// Fetch the user assigned options
	$options = $swp_user_options;

	// Check if we even have a value to format
	if ( $val ) :

		// Check if the value is less than 1,000....
		if ( $val < 1000 ) :

			// If less than 1,000 just format and kick it back
			return number_format( $val );

			// Check if the value is greater than 1,000 and less than 1,000,000....
		elseif ( $val < 1000000 ) :

			// Start by deviding the value by 1,000
			$val = intval( $val ) / 1000;

			// If the decimal separator is a period
			if ( $options['swp_decimal_separator'] == 'period' ) :

				// Then format the number to the appropriate number of decimals
				return number_format( $val,$options['swDecimals'],'.',',' ) . 'K';

				// If the decimal separator is a comma
			else :

				// Then format the number to the appropriate number of decimals
				return number_format( $val,$options['swDecimals'],',','.' ) . 'K';

			endif;

			// Check if the value is greater than 1,000,000....
		else :

			// Start by deviding the value by 1,000,000
			$val = intval( $val ) / 1000000;

			// If the decimal separator is a period
			if ( $options['swp_decimal_separator'] == 'period' ) :

				// Then format the number to the appropriate number of decimals
				return number_format( $val,$options['swDecimals'],'.',',' ) . 'M';

				// If the decimal separator is a comma
			else :

				// Then format the number to the appropriate number of decimals
				return number_format( $val,$options['swDecimals'],',','.' ) . 'M';

			endif;

		endif;

	endif;

	return 0;
}

/**
 *  Process the excerpts for descriptions
 *
 * @since  unknown
 * @access public
 * @param  int $post_id The post ID to use when getting an exceprt.
 * @return string The excerpt.
 */
function swp_get_excerpt_by_id( $post_id ) {

	// Check if the post has an excerpt
	if ( has_excerpt() ) :
		$the_post = get_post( $post_id ); // Gets post ID
		$the_excerpt = $the_post->post_excerpt;

		// If not, let's create an excerpt
		else :
			$the_post = get_post( $post_id ); // Gets post ID
			$the_excerpt = $the_post->post_content; // Gets post_content to be used as a basis for the excerpt
		endif;

		$excerpt_length = 100; // Sets excerpt length by word count
		$the_excerpt = strip_tags( strip_shortcodes( $the_excerpt ) ); // Strips tags and images

		$the_excerpt = str_replace( ']]>', ']]&gt;', $the_excerpt );
		$the_excerpt = strip_tags( $the_excerpt );
		$excerpt_length = apply_filters( 'excerpt_length', 100 );
		$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[...]' );
		$words = preg_split( "/[\n\r\t ]+/", $the_excerpt, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) > $excerpt_length ) :
			array_pop( $words );
			// array_push($words, '…');
			$the_excerpt = implode( ' ', $words );
		endif;

		$the_excerpt = preg_replace( "/\r|\n/", '', $the_excerpt );

		return $the_excerpt;
}

if ( ! function_exists( 'swp_mobile_detection' ) ) {
	/**
	 * Check to see if the user is using a mobile device.
	 *
	 * @since  unknown
	 * @access public
	 * @todo   Replace this with a more reliable method, probably client-side.
	 * @return bool true if a mobile user agent.
	 */
	function swp_mobile_detection() {
		return preg_match( '/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $_SERVER['HTTP_USER_AGENT'] );
	}
}

/**
 * Check to see if debugging has been enabled.
 *
 * @since  2.1.0
 * @access private
 * @param  string $type The type of debugging to check for.
 * @return bool true if debugging is enabled.
 */
function _swp_is_debug( $type = 'all' ) {
	$debug = false;

	if ( ! empty( $_GET['swp_debug'] ) ) {
		$debug = sanitize_key( $_GET['swp_debug'] );
	}

	if ( ( $debug && 'all' === $type ) || $debug === $type ) {
		$debug = true;
	} else {
		$debug = false;
	}

	return (bool) apply_filters( 'swp_is_debug', $debug );
}

/**
 * A function to clean up the available buttons Array
 *
 * @since 2.1.4
 * @param Array $options The options Array
 * @return Array $options The modified options array
 */
function swp_buttons_cleanup( $options ) {
	if(isset($options['content']['active'])) {
		unset($options['content']['active']);
	}
	return $options;
}
add_filter( 'swp_button_options', 'swp_buttons_cleanup', 999 );

/**
 * A function to recursively search arrays
 *
 * @since  1.0.0
 * @access public
 * @param  string $needle   The needle
 * @param  string $haystack The haystack
 * @return string/bool Return the key if found or false if nothing is found
 */
function recursive_array_search( $needle, $haystack ) {
	foreach ( $haystack as $key => $value ) {
		$current_key = $key;
		if ( $needle === $value or (is_array( $value ) && recursive_array_search( $needle,$value ) !== false) ) {
			return $current_key;
		}
	}
	return false;
}

/**
 * A function to gethe current URL of a page
 *
 * @since  1.0.0
 * @return string The URL of the current page
 */
function swp_get_current_url() {
	$page_url = 'http';
	if ( $_SERVER['HTTPS'] == 'on' ) {$page_url .= 's';}
	$page_url .= '://';
	$page_url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	$page_url = strtok( $page_url, '?' );

	return $page_url;
}
/**
 * A function to disable the buttons on subtitles
 *
 * @return bool false
 */
function swp_disable_subs() {
	return false;
}

/**
 * Convert curly quotes to straight quotes
 *
 * @since  1.4.0
 * @param  string $content A string of text to be filtered
 * @return string $content The modified string of text
 */
function convert_smart_quotes( $content ) {
	$content = str_replace( '"', '\'', $content );
	$content = str_replace( '&#8220;', '\'', $content );
	$content = str_replace( '&#8221;', '\'', $content );
	$content = str_replace( '&#8216;', '\'', $content );
	$content = str_replace( '&#8217;', '\'', $content );
	return $content;
}

/**
 * A function to make removing hooks easier
 *
 * @since  1.4.0
 * @access public
 * @param  string  $hook_name   The name of the hook
 * @param  string  $method_name The name of the method
 * @param  integer $priority    The hook priority
 * @return boolean false
 */
function swp_remove_filter( $hook_name = '', $method_name = '', $priority = 0 ) {
	global $wp_filter;

	// Take only filters on right hook name and priority
	if ( ! isset( $wp_filter[ $hook_name ][ $priority ] ) || ! is_array( $wp_filter[ $hook_name ][ $priority ] ) ) {
		return false;
	}

	// Loop on filters registered
	foreach ( (array) $wp_filter[ $hook_name ][ $priority ] as $unique_id => $filter_array ) {
		// Test if filter is an array ! (always for class/method)
		if ( isset( $filter_array['function'] ) && is_array( $filter_array['function'] ) ) {
			// Test if object is a class and method is equal to param !
			if ( is_object( $filter_array['function'][0] ) && get_class( $filter_array['function'][0] ) && $filter_array['function'][1] == $method_name ) {
				unset( $wp_filter[ $hook_name ][ $priority ][ $unique_id ] );
			}
		}
	}

	return false;
}
