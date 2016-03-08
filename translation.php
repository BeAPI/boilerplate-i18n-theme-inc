<?php class BEA_Translation {

	/**
	 * Set the theme textdomain
	 */
	const textdomain = 'custom_text_domain';

	function __construct() {
		// Load theme textdomain
		add_action( 'after_setup_theme', array( __CLASS__, 'load_theme_textdomain' ) );

		// Load export acf textdomain
		add_filter( 'acf/settings/export_textdomain', array( __CLASS__, 'load_acf_textdomain' ) );

		// Set Polylang current lang
		add_filter( 'acf/settings/current_language', array( __CLASS__, 'get_current_site_lang' ) );

		// Load default Polylang
		add_filter( 'acf/load_value', array( __CLASS__, 'set_default_value' ), 10, 3 );
	}

	/**
	 * Load the theme textdomain
	 *
	 * @author Maxime CULEA
	 */
	public static function load_theme_textdomain() {
		load_theme_textdomain( self::textdomain, get_template_directory() . '/languages' );
	}

	/**
	 * Load the acf textdomain for export
	 *
	 * @since ACF 5.3.3
	 *
	 * @author Maxime CULEA
	 * @return string
	 */
	public static function load_acf_textdomain() {
		return self::textdomain;
	}

	/**
	 * Get the current Polylang's locale or the wp's one
	 *
	 * @author Maxime CULEA
	 *
	 * @return bool|string
	 */
	public static function get_current_site_lang() {
		return function_exists( 'pll_current_language' ) ? pll_current_language( 'locale' ) : get_locale();
	}

	/**
	 * Load default value in front, if none found for an acf option
	 *
	 * @author Maxime CULEA
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return mixed|string|void
	 */
	public static function set_default_value( $value, $post_id, $field ) {
		if ( is_admin() || false === strpos( $post_id, 'options' ) || ! function_exists( 'pll_current_language' ) || ! empty( $value ) ) {
			return $value;
		}

		/**
		 * Take off filters for loading "default" Polylang saved value
		 * and for avoiding infinite looping on current filter
		 */
		remove_filter( 'acf/settings/current_language', array( __CLASS__, 'get_current_site_lang' ) );
		remove_filter( 'acf/load_value', array( __CLASS__, 'set_default_value' ) );

		$value = acf_get_metadata( 'options', $field['name'] );

		/**
		 * Re-add taken off filters
		 */
		add_filter( 'acf/settings/current_language', array( __CLASS__, 'get_current_site_lang' ) );
		add_filter( 'acf/load_value', array( __CLASS__, 'set_default_value' ), 10, 3 );

		return $value;
	}

}
new BEA_Translation();