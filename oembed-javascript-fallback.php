<?php
/**
 * Plugin Name: oEmbed Javascript Fallback
 * Plugin URI: http://wordpress.org/extend/plugins/oembed-javascript-fallback/
 * Description: Fall back to oEmbed over Javascript if the embed fails
 * Version:     0.1
 * Author:      Automattic, Mo Jangda, Daniel Bachhuber
 * Author URI: http://automattic.com/
 */

class oEmbed_Javascript_Fallback {

	function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'action_enqueue_scripts' ) );
		add_action( 'wp_footer', array( $this, 'action_wp_footer' ) );
		add_filter( 'embed_maybe_make_link', array( $this, 'filter_embed_maybe_make_link' ), 10, 2 );
	}

	function action_enqueue_scripts() {
		wp_enqueue_script( 'jquery' );
	}

	function action_wp_footer() {
		?>
		<script>
		// handle failed tweets or tweets that haven't been fetched yet
		jQuery(function($) {
		
			var ofj_do_embeds = function ofj_do_embeds() {
				// Stolen from wpcom. We should support all of the native oembed providers
				$( 'span.oembed-javascript-fallback' ).each( function() {
					var $this = $(this),
					text = $this.text(),
					url = 'http://api.twitter.com/1/statuses/oembed.json?omit_script=true&callback=?&';

					// If we find an exact match, we want to fetch its content from the oembed endpoint and display it
					if ( text.match( /^http(s|):\/\/twitter\.com(\/\#\!\/|\/)([a-zA-Z0-9_]{1,20})\/status(es)*\/(\d+)$/ ) ) {
						url += 'url=' + encodeURIComponent( text );
					} else if ( text.match( /^(\d+)$/ ) ) {
						url += 'id=' + text;
					} else {
						return;
					}

					// Need to make a JSONP call to avoid CORS issues
					$.getJSON( url, function( data ) {
						if ( data.html ) {
							$this.html( data.html );
							$this.show();
						}
					} );
				});
				setTimeout( ofj_do_embeds, 10000 );
			}
			ofj_do_embeds();
		});
		</script>
		<?php
	}

	function filter_embed_maybe_make_link( $output, $url ) {

		if ( $output != esc_url( $output ) )
			return $output;

		$output = '<span class="oembed-javascript-fallback">' . $output . '</span>';
		return $output;
	}
}
global $oembed_javascript_fallback;
$oembed_javascript_fallback = new oEmbed_Javascript_Fallback;