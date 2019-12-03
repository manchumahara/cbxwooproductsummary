<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              codeboxr.com
 * @since             1.0.0
 * @package           cbxwooproductsummary
 *
 * @wordpress-plugin
 * Plugin Name:       CBX Woo Product Summary
 * Plugin URI:        https://codeboxr.com/product/cbx-woo-product-summary/
 * Description:       WooCommerce Product Summary
 * Version:           1.0.1
 * Author:            Codeboxr Team
 * Author URI:        https://codeboxr.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cbxwooproductsummary
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//CBX Woo Free Product Quick Checkout

defined( 'CBXWOOPRODUCTSUMMARY_PLUGIN_NAME' ) or define( 'CBXWOOPRODUCTSUMMARY_PLUGIN_NAME', 'cbxwooproductsummary' );
defined( 'CBXWOOPRODUCTSUMMARY_PLUGIN_VERSION' ) or define( 'CBXWOOPRODUCTSUMMARY_PLUGIN_VERSION', '1.0.1' );
defined( 'CBXWOOPRODUCTSUMMARY_BASE_NAME' ) or define( 'CBXWOOPRODUCTSUMMARY_BASE_NAME', plugin_basename( __FILE__ ) );
defined( 'CBXWOOPRODUCTSUMMARY_ROOT_PATH' ) or define( 'CBXWOOPRODUCTSUMMARY_ROOT_PATH', plugin_dir_path( __FILE__ ) );
defined( 'CBXWOOPRODUCTSUMMARY_ROOT_URL' ) or define( 'CBXWOOPRODUCTSUMMARY_ROOT_URL', plugin_dir_url( __FILE__ ) );

class CBXWooProductSummary {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	public function __construct() {
		$this->plugin_name = CBXWOOPRODUCTSUMMARY_PLUGIN_NAME;
		$this->version     = CBXWOOPRODUCTSUMMARY_PLUGIN_VERSION;

		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'init', array( $this, 'init_shortcodes' ) );
		add_filter( 'wc_get_template', array( $this, 'wc_get_template_summary' ), 10, 5 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}//end of constructor

	/**
	 * Load translation text domain
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'cbxwooproductsummary', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}//end function load_plugin_textdomain

	/**
	 * init all shortcodes
	 */
	public function init_shortcodes() {
		add_shortcode( 'cbxwooproductsummary', array( $this, 'cbxwooproductsummary_shortcode' ) );

	}//end method init_shortcodes


	public function cbxwooproductsummary_shortcode( $atts = array() ) {
		// normalize attribute keys, lowercase
		$atts = array_change_key_case( (array) $atts, CASE_LOWER );

		if ( empty( $atts ) ) {
			return '';
		}

		// override default attributes with user attributes
		$atts = shortcode_atts( array(
			'id'               => '',
			'sku'              => '',
			'show_title'       => 1,
			'show_rating'      => 1,
			'show_price'       => 1,
			'show_excerpt'     => 1,
			'show_add_to_cart' => 1,
			'show_meta'        => 1,
			'show_sharing'     => 1,
        ), $atts,'cbxwooproductsummary' );

		if ( ! isset( $atts['id'] ) && ! isset( $atts['sku'] ) ) {
			return '';
		}

		wp_enqueue_style( 'cbxwooproductsummary' );

		$args = array(
			'posts_per_page'      => 1,
			'post_type'           => 'product',
			'post_status'         => ( ! empty( $atts['status'] ) ) ? $atts['status'] : 'publish',
			'ignore_sticky_posts' => 1,
			'no_found_rows'       => 1,
		);

		if ( isset( $atts['sku'] ) ) {
			$args['meta_query'][] = array(
				'key'     => '_sku',
				'value'   => sanitize_text_field( $atts['sku'] ),
				'compare' => '=',
			);

			$args['post_type'] = array( 'product', 'product_variation' );
		}

		if ( isset( $atts['id'] ) ) {
			$args['p'] = absint( $atts['id'] );
		}

		// Don't render titles if desired.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// Don't render rating if desired.
		if ( isset( $atts['show_rating'] ) && ! $atts['show_rating'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		}

		// Don't render price if desired.
		if ( isset( $atts['show_price'] ) && ! $atts['show_price'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		// Don't render excerpt if desired.
		if ( isset( $atts['show_excerpt'] ) && ! $atts['show_excerpt'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}

		// Don't render add to cart if desired.
		if ( isset( $atts['show_add_to_cart'] ) && ! $atts['show_add_to_cart'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}

		// Don't render meta if desired.
		if ( isset( $atts['show_meta'] ) && ! $atts['show_meta'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		}

		// Don't render meta if desired.
		if ( isset( $atts['show_sharing'] ) && ! $atts['show_sharing'] ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		}

		// Change form action to avoid redirect.
		add_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		$single_product = new WP_Query( $args );

		$preselected_id = '0';

		// Check if sku is a variation.
		if ( isset( $atts['sku'] ) && $single_product->have_posts() && 'product_variation' === $single_product->post->post_type ) {

			$variation  = new WC_Product_Variation( $single_product->post->ID );
			$attributes = $variation->get_attributes();

			// Set preselected id to be used by JS to provide context.
			$preselected_id = $single_product->post->ID;

			// Get the parent product object.
			$args = array(
				'posts_per_page'      => 1,
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'no_found_rows'       => 1,
				'p'                   => $single_product->post->post_parent,
			);

			$single_product = new WP_Query( $args );
			?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $variations_form = $('[data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>"]').find('form.variations_form');

					<?php foreach ( $attributes as $attr => $value ) { ?>
                    $variations_form.find('select[name="<?php echo esc_attr( $attr ); ?>"]').val('<?php echo esc_js( $value ); ?>');
					<?php } ?>
                });
            </script>
			<?php
		}

		// For "is_single" to always make load comments_template() for reviews.
		$single_product->is_single = true;

		ob_start();

		global $wp_query;

		// Backup query object so following loops think this is a product page.
		$previous_wp_query = $wp_query;
		// @codingStandardsIgnoreStart
		$wp_query = $single_product;
		// @codingStandardsIgnoreEnd

		wp_enqueue_script( 'wc-single-product' );

		while ( $single_product->have_posts() ) {
			$single_product->the_post()
			?>
            <div class="single-product cbxwooproductsummary-single-product" data-product-page-preselected-id="<?php echo esc_attr( $preselected_id ); ?>">
				<?php
				wc_get_template( 'content-single-product-summary.php' );

				?>
            </div>
			<?php
		}

		// Restore $previous_wp_query and reset post data.
		// @codingStandardsIgnoreStart
		$wp_query = $previous_wp_query;
		// @codingStandardsIgnoreEnd
		wp_reset_postdata();

		// Re-enable titles if they were removed.
		if ( isset( $atts['show_title'] ) && ! $atts['show_title'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		}

		// Re-enable rating if they were removed.
		if ( isset( $atts['show_rating'] ) && ! $atts['show_rating'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		}

		// Re-enable price if they were removed.
		if ( isset( $atts['show_price'] ) && ! $atts['show_price'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		}

		// Re-enable excerpt if they were removed.
		if ( isset( $atts['show_excerpt'] ) && ! $atts['show_excerpt'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}

		// Re-enable add to cart if they were removed.
		if ( isset( $atts['show_add_to_cart'] ) && ! $atts['show_add_to_cart'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}

		// Re-enable meta if they were removed.
		if ( isset( $atts['show_meta'] ) && ! $atts['show_meta'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		}

		// Re-enable sharing if they were removed.
		if ( isset( $atts['show_sharing'] ) && ! $atts['show_sharing'] ) {
			add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
		}

		remove_filter( 'woocommerce_add_to_cart_form_action', '__return_empty_string' );

		return '<div class="woocommerce">' . ob_get_clean() . '</div>';

	}//end method cbxwooproductsummary_shortcode


	/**
	 * Function to change template dir
	 *
	 * @param $located
	 * @param $template_name
	 * @param $args
	 * @param $template_path
	 * @param $default_path
	 *
	 * @return mixed|void
	 */
	public function wc_get_template_summary( $located, $template_name, $args, $template_path, $default_path ) {

		if ( $template_name == 'content-single-product-summary.php' ) {
			$located = cbxwooproductsummary_locate_template( 'content-single-product-summary.php' );
		}

		return $located;

	}//end method wc_get_template_summary

	/**
	 * Load css and js
	 */
	public function enqueue_scripts() {


		wp_register_style( 'cbxwooproductsummary', plugin_dir_url( __FILE__ ) . 'assets/cbxwooproductsummary.css', array(), $this->version, 'all' );
		//wp_enqueue_style( 'cbxwooproductsummary');
	}//end method enqueue_scripts
}//end class CBXWooProductSummary

new CBXWooProductSummary();


/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function cbxwooproductsummary_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		//$template_path = cbxmcratingreview_template_path();
		$template_path = WC()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = CBXWOOPRODUCTSUMMARY_ROOT_PATH . 'templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/.
	if ( ! $template ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'cbxwooproductsummary_locate_template', $template, $template_name, $template_path );
}//end function cbxwooproductsummary_locate_template