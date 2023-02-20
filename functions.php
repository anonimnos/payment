<?php
/**
 * payment functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package payment
 */

define( 'PAYMENT_VERSION', '1.0.2' );
define( 'PAYMENT_REAL_PATH', realpath( dirname( __FILE__ ) ) );
define( 'PAYMENT_ORDER_HANDLER', get_stylesheet_directory_uri() . '/inc/handle-order.php' );
define( 'PAYMENT_PROJECT_ID', 82637 );
define( 'PAYMENT_SIGN_PASSWORD', '65d6558f37fbc7abe1e2c1ed1e585299' ); 

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function payment_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on payment, use a find and replace
		* to change 'payment' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'payment', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'payment' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);
}
add_action( 'after_setup_theme', 'payment_setup' );

/**
 * 
 * Enqueue scripts and styles.
 * 
 */
function payment_scripts() {
	wp_enqueue_style( 'payment-style', get_stylesheet_uri(), array(), PAYMENT_VERSION );

	if ( is_product() ) {
		wp_enqueue_script( 'add-to-cart-ajax', get_stylesheet_directory_uri() . '/assets/js/add-to-cart.js', array( 'jquery' ), PAYMENT_VERSION, true );
		wp_enqueue_script( 'payment-handle', get_stylesheet_directory_uri() . '/assets/js/payment-handle.js', array( 'jquery' ), PAYMENT_VERSION, true );

		wp_localize_script( 'payment-handle', 'Order', array(
			'url' => PAYMENT_ORDER_HANDLER,
		) );
		
	}
}
add_action( 'wp_enqueue_scripts', 'payment_scripts' );

/**
 * 
 * Load WooCommerce file.
 * 
 */
if ( class_exists( 'WooCommerce' ) ) {
	require PAYMENT_REAL_PATH . '/inc/woocommerce.php';
	require PAYMENT_REAL_PATH . '/inc/custom-field.php';
}