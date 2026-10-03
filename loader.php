<?php
/*
* Plugin Name: LearnDash Pay for Lesson
* Plugin URI: https://themekraft.com
* Description: LearnDash Pay for Lesson enables you to sell LearnDash Lessons using Woocommerce.
* Author: Themekraft
* Version: 1.0.4
* Requires at least: 5.9
* Requires PHP: 7.4
* Text Domain: learndash-pfl
* Author URI: https://themekraft.com/
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! function_exists( 'dpflww_fs' ) ) {
    // Create a helper function for easy SDK access.
    function dpflww_fs() {
        global $dpflww_fs;

        if ( ! isset( $dpflww_fs ) ) {
            $dpflww_fs = fs_dynamic_init( array(
                'id'                             => '9380',
                'slug'                           => 'learn-dash-pay-for-lessons-with-woocommerce',
                'premium_slug'                   => 'Learn-dash-pay-for-lessons-with-wooCommerce-premium',
                'type'                           => 'plugin',
                'public_key'                     => 'pk_b9fecc8926d3242517fbefb590a9b',
                'is_premium'                     => true,
                'is_premium_only'                => true,
                'has_addons'                     => false,
                'has_paid_plans'                 => true,
                'is_org_compliant'               => true,
                'wp_org_gatekeeper'              => 'OA7#BoRiBNqdf52FvzEf!!074aRLPs8fspif$7K1#4u4Csys1fQlCecVcUTOs2mcpeVHi#C2j9d09fOTvbC0HloPT7fFee5WdS3G',
                'trial'                          => array(
                    'days'               => 7,
                    'is_require_payment' => true,
                ),
                'menu'                           => array(
                    'first-path' => 'plugins.php',
                    'support'    => false,
                ),
                'bundle_license_auto_activation' => true,
            ) );
        }

        return $dpflww_fs;
    }

    // Init Freemius.
    dpflww_fs();
    // Signal that SDK was initiated.
    do_action( 'dpflww_fs_loaded' );
}

if ( in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins') ) ) && dpflww_fs()->is_paying() ) {

	function set_lesson_access( $lesson_ids_ar, $user_id ) {
	    foreach ( $lesson_ids_ar as $lesson_id ) {
	        $user_ids = get_post_meta($lesson_id, "access_user_id", true);
	        $user_ids = unserialize( $user_ids );
	        if ( ! is_array( $user_ids ) ) {
	            $user_ids = [];
	        }
	        $user_ids[] = $user_id;
	        $user_ids = array_unique(array_merge($user_ids, $user_ids));
	        update_post_meta( $lesson_id, "access_user_id", maybe_serialize( $user_ids ) );
	    }
	}


	include_once 'includes/woocommerce-fields.php';	
	include_once 'includes/learndash-fields.php';
	include_once 'includes/product-integration.php';


	add_action( 'wp_ajax_get_course_lessons', 'get_course_lessons' );
	function get_course_lessons() {
		if ( ! current_user_can( 'edit_products' ) ) {
			wp_send_json_error( array( 'message' => 'forbidden' ), 403 );
		}

		$nonce = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'learndash_pfl_get_course_lessons' ) ) {
			wp_send_json_error( array( 'message' => 'bad nonce' ), 403 );
		}

		$courses_raw = array();
		if ( isset( $_REQUEST['courses'] ) ) {
			$courses_raw = wp_unslash( $_REQUEST['courses'] );
			if ( ! is_array( $courses_raw ) ) {
				$courses_raw = array_filter( array_map( 'trim', explode( ',', (string) $courses_raw ) ) );
			}
			$courses_raw = array_map( 'sanitize_text_field', $courses_raw );
		}
		$courses = array_map( 'absint', $courses_raw );

		$product_id = isset( $_REQUEST['productID'] ) ? absint( wp_unslash( $_REQUEST['productID'] ) ) : 0;

		// The meta_key + meta_query lookup against `course_id` is the only way to find lessons attached to a course; the endpoint is gated on `edit_products` + nonce, so the unbounded-query risk is bounded.
		// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		$args = array(
			'posts_per_page' => -1,
			'post_type'      => 'sfwd-lessons',
			'order'          => 'ASC',
			'meta_key'       => 'course_id',
			'meta_query'     => array(
				array(
					'key'     => 'course_id',
					'value'   => $courses,
					'compare' => 'IN',
				),
			),
		);
		// phpcs:enable WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.SlowDBQuery.slow_db_query_meta_query

		$lesson_idss = array();
		if ( $product_id ) {
			$lesson_idss = unserialize( get_post_meta( $product_id, '_lesson_id', true ) );
			if ( ! is_array( $lesson_idss ) ) {
				$lesson_idss = array();
			}
		}

		$options   = '<option value="">' . esc_html__( 'Select lesson', 'learndash-pfl' ) . '</option>';
		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) :
			while ( $the_query->have_posts() ) :
				$the_query->the_post();
				$id       = get_the_ID();
				$selected = ( count( $lesson_idss ) > 0 && in_array( $id, $lesson_idss, true ) ) ? ' selected' : '';
				$options .= '<option value="' . esc_attr( $id ) . '"' . $selected . '>' . esc_html( get_the_title() ) . '</option>';
			endwhile;
			wp_reset_postdata();
		endif;

		echo wp_kses(
			$options,
			array(
				'option' => array(
					'value'    => array(),
					'selected' => array(),
				),
			)
		);
		wp_die();
	}

	add_action( 'admin_enqueue_scripts', 'enqueue_select2_jquery' );
	function enqueue_select2_jquery() {
		global $post, $pagenow;
		if ( ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) && isset( $post->post_type ) && 'product' === $post->post_type ) {
			// Reuse WooCommerce's bundled select2 4.0.3 (registered as the `select2` legacy handle for `wc-select2`) and admin styles instead of pulling from a CDN.
			wp_enqueue_script( 'select2' );
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}
	}

	add_action( 'admin_print_footer_scripts', 'select2jquery_inline' );
	function select2jquery_inline() {
		global $post, $pagenow;
		if ( ( $pagenow === 'post-new.php' || $pagenow === 'post.php' ) && isset( $post->post_type ) && 'product' === $post->post_type ) {
			?>
			<script type="text/javascript">jQuery(function($){$('.lesson_form_select').select2();});</script>
			<?php
		}
	}
} else {
	
	function general_admin_notice() {
		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			esc_html__( 'LearnDash Pay for Lessons requires the WooCommerce plugin to be active.', 'learndash-pfl' )
		);
	}
	add_action( 'admin_notices', 'general_admin_notice' );
}

?>
