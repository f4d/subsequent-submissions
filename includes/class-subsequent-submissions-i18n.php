<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://rezon8.net/
 * @since      1.0.0
 *
 * @package    Subsequent_Submissions
 * @subpackage Subsequent_Submissions/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Subsequent_Submissions
 * @subpackage Subsequent_Submissions/includes
 * @author     David Powers <cyborgk@gmail.com>
 */
class Subsequent_Submissions_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'subsequent-submissions',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
