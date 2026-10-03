<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function woocommerce_wp_select_multiple( $field ) {
	global $thepostid, $post;

	$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
	$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
	$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
	$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];

	if ( ! isset( $field['value'] ) ) {
		$stored        = get_post_meta( $thepostid, $field['id'], true );
		$field['value'] = $stored ? unserialize( $stored ) : array();
	}
	if ( ! is_array( $field['value'] ) ) {
		$field['value'] = array();
	}

	printf(
		'<p class="form-field %1$s_field %2$s"><label for="%1$s">%3$s</label><select id="%1$s" name="%4$s" class="%5$s" multiple="multiple">',
		esc_attr( $field['id'] ),
		esc_attr( $field['wrapper_class'] ),
		wp_kses_post( $field['label'] ),
		esc_attr( $field['name'] ),
		esc_attr( $field['class'] )
	);

	foreach ( $field['options'] as $key => $value ) {
		printf(
			'<option value="%1$s"%2$s>%3$s</option>',
			esc_attr( $key ),
			in_array( $key, $field['value'] ) ? ' selected="selected"' : '',
			esc_html( $value )
		);
	}

	echo '</select> ';

	if ( ! empty( $field['description'] ) ) {
		if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
			printf(
				'<img class="help_tip" data-tip="%1$s" src="%2$s/assets/images/help.png" height="16" width="16" />',
				esc_attr( $field['description'] ),
				esc_url( WC()->plugin_url() )
			);
		} else {
			echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
		}
	}

	echo '</p>';
}

add_action( 'plugins_loaded', 'wcpt_register_lesson_type' );
function wcpt_register_lesson_type () {

	class WC_Product_Lesson_Type extends WC_Product {

		public function __construct( $product ) {
			$this->product_type = 'lesson_type'; 
			parent::__construct( $product );
		}
    }
}

add_filter( 'product_type_selector', 'wcpt_add_lesson_type_type' );
function wcpt_add_lesson_type_type ( $type ) {
	$type[ 'lesson_type' ] = __( 'Lesson', 'learndash-pfl' );
	return $type;
}

add_filter( 'woocommerce_product_data_tabs', 'lesson_type_tab' );
function lesson_type_tab( $tabs ) {
	$tabs['lesson_type'] = array(
		'label'	   => __( 'Lesson', 'learndash-pfl' ),
		'target'   => 'lesson_type_options',
		'class'    => ('show_if_lesson_type'),
		'priority' => 10,
	);
	return $tabs;
}

add_action( 'woocommerce_product_data_panels', 'wcpt_lesson_type_options_product_tab_content' );
function wcpt_lesson_type_options_product_tab_content() {
	global $post;

	wp_nonce_field( 'lesson_type_options', 'lesson_type_options_nonce' );

	include_once 'product-data-ajax.php';

	$regular_price = (string) get_post_meta( $post->ID, '_regular_price', true );
	$sale_price    = (string) get_post_meta( $post->ID, '_sale_price', true );

	?>
	<div id="lesson_type_options" class="panel woocommerce_options_panel">
		<div class="options_group">
			<?php
			woocommerce_wp_checkbox( array(
				'id'    => '_enable_lesson_type',
				'label' => __( 'Enable as Lesson Product', 'learndash-pfl' ),
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_regular_price_lesson',
				'label'       => __( 'Regular price (€)', 'learndash-pfl' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter the regular price for this lesson.', 'learndash-pfl' ),
				'value'       => $regular_price,
			) );

			woocommerce_wp_text_input( array(
				'id'          => '_sale_price_lesson',
				'label'       => __( 'Sale price (€)', 'learndash-pfl' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter the sale price for this lesson.', 'learndash-pfl' ),
				'value'       => $sale_price,
			) );

			woocommerce_wp_checkbox( array(
				'id'    => '_all_lessons',
				'label' => __( 'All Lessons', 'learndash-pfl' ),
			) );

			$options       = array( '' => __( 'Select Course', 'learndash-pfl' ) );
			$courses_query = new WP_Query(
				array(
					'post_type' => 'sfwd-courses',
					'order'     => 'ASC',
				)
			);
			if ( $courses_query->have_posts() ) {
				while ( $courses_query->have_posts() ) {
					$courses_query->the_post();
					$options[ get_the_ID() ] = get_the_title();
				}
				wp_reset_postdata();
			}
			?>
			<div class="options_group">
				<?php
				woocommerce_wp_select_multiple( array(
					'id'          => '_course_id',
					'label'       => __( 'Courses', 'learndash-pfl' ),
					'description' => __( 'Attach Courses.', 'learndash-pfl' ),
					'name'        => '_course_id[]',
					'class'       => 'lesson_form_select',
					'options'     => $options,
				) );
				?>
			</div>
			<div class="options_group">
				<?php
				woocommerce_wp_select_multiple( array(
					'id'          => '_lesson_id',
					'label'       => __( 'Lesson', 'learndash-pfl' ),
					'description' => __( 'Attach Lesson.', 'learndash-pfl' ),
					'name'        => '_lesson_id[]',
					'class'       => 'lesson_form_select',
					'options'     => array( __( 'Select Course First', 'learndash-pfl' ) ),
				) );
				?>
			</div>
		</div>
	</div>
	<?php
}

function set_product_id( $lesson_id, $post_id ) {
    $product_ids   = get_post_meta(
                    $lesson_id,
                    "product_ids",
                    true);

    $product_ids   = unserialize($product_ids);   

    if(!is_array($product_ids))
    {
        $product_ids = array();    
    }
    $product_ids[] =   $post_id;

    $product_ids   =   array_unique(array_merge($product_ids, $product_ids) );

    update_post_meta(
                    $lesson_id,"product_ids",
                    maybe_serialize($product_ids)
                    );
}

add_action( 'woocommerce_process_product_meta', 'save_lesson_type_options_field', 9999 );
function save_lesson_type_options_field( $post_id ) {

	$nonce = isset( $_POST['lesson_type_options_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['lesson_type_options_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'lesson_type_options' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$enable_lesson_type = ! empty( $_POST['_enable_lesson_type'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_enable_lesson_type', $enable_lesson_type );

	$regular_price = isset( $_POST['_regular_price_lesson'] )
		? sanitize_text_field( wp_unslash( $_POST['_regular_price_lesson'] ) )
		: '';
	if ( '' !== $regular_price ) {
		update_post_meta( $post_id, '_regular_price', $regular_price );
		if ( empty( $_POST['_sale_price_lesson'] ) ) {
			update_post_meta( $post_id, '_price', $regular_price );
		}
	}

	$sale_price = isset( $_POST['_sale_price_lesson'] )
		? sanitize_text_field( wp_unslash( $_POST['_sale_price_lesson'] ) )
		: '';
	if ( '' !== $sale_price ) {
		update_post_meta( $post_id, '_sale_price', $sale_price );
		update_post_meta( $post_id, '_price', $sale_price );
	}

	if ( isset( $_POST['_course_id'] ) ) {
		$course_ids = array_map( 'absint', (array) wp_unslash( $_POST['_course_id'] ) );
		$course_ids = array_values( array_filter( $course_ids ) );
		update_post_meta( $post_id, '_course_id', maybe_serialize( $course_ids ) );
	}

	if ( isset( $_POST['_lesson_id'] ) ) {
		$lesson_ids = array_map( 'absint', (array) wp_unslash( $_POST['_lesson_id'] ) );
		$lesson_ids = array_values( array_filter( $lesson_ids ) );
		update_post_meta( $post_id, '_lesson_id', maybe_serialize( $lesson_ids ) );

		foreach ( $lesson_ids as $lesson_id ) {
			set_product_id( $lesson_id, $post_id );
			update_post_meta( $lesson_id, '_ld_lesson_active', '1' );
		}
	}
}

function first_lesson_fn( $lesson_id ) {
	$first_lesson = false;
	$courses = get_post_meta($lesson_id,'course_id');
	if (!empty($courses)) 
	{
    	if(is_array($courses) && count($courses) > 0)
		{
			$course_id = $courses[0];
			$course_meta = get_post_meta($course_id,'ld_course_steps',true);
			$lesson_ar = (isset($course_meta['steps']['h']['sfwd-lessons']) ? $course_meta['steps']['h']['sfwd-lessons'] : array());
			if (isset($lesson_ar) && is_array($lesson_ar) && count($lesson_ar) > 0 ) 
			{
				if (array_key_first($lesson_ar) == $lesson_id) {
					$first_lesson = true;
				}
			}
		}
	}
	return $first_lesson;
}

/**
 * Render the standard ld-alert wrapper around a pre-escaped HTML body.
 *
 * @param string $body_html HTML for the message body. Must already be escaped.
 *
 * @return string
 */
function learndash_pfl_render_lesson_alert( $body_html ) {
	return '<div class="ld-alert ld-alert-warning">'
		. '<div class="ld-alert-content">'
		. '<div class="ld-alert-icon ld-icon ld-icon-alert"></div>'
		. '<div class="ld-alert-messages">' . $body_html . '</div>'
		. '</div></div>';
}

function lesson__add_to_content( $content ) {
	global $post;

	if ( ! is_single() || ! isset( $post->post_type ) || 'sfwd-lessons' !== $post->post_type ) {
		return $content;
	}

	$item_id          = get_the_ID();
	$ld_lesson_active = get_post_meta( $item_id, '_ld_lesson_active', true );
	if ( '1' !== (string) $ld_lesson_active ) {
		return $content;
	}

	$product_ids = unserialize( get_post_meta( $item_id, 'product_ids', true ) );
	if ( ! is_array( $product_ids ) ) {
		$product_ids = array();
	}

	$paid_msg = esc_html__( 'This is paid content. Please contact the administrator about purchasing this product.', 'learndash-pfl' );

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();

		$access_meta = unserialize( get_post_meta( $item_id, 'access_user_id', true ) );
		if ( $access_meta ) {
			set_lesson_access( array( $item_id ), $user_id );
		}

		if ( empty( $product_ids ) ) {
			return learndash_pfl_render_lesson_alert( $paid_msg );
		}

		$access_meta = is_array( $access_meta ) ? $access_meta : array();
		if ( ! in_array( $user_id, $access_meta, true ) ) {
			if ( count( $product_ids ) === 1 ) {
				$permalink = get_permalink( (int) $product_ids[0] );
				return learndash_pfl_render_lesson_alert(
					esc_html__( 'Please buy this lesson', 'learndash-pfl' )
					. ' <a href="' . esc_url( $permalink ) . '" target="_blank">'
					. esc_html__( 'Buy Now', 'learndash-pfl' )
					. '</a>'
				);
			}

			$products_html = '';
			foreach ( $product_ids as $product_lesson_id ) {
				$pid       = (int) $product_lesson_id;
				$permalink = get_permalink( $pid );
				$products_html .= ' <a href="' . esc_url( $permalink ) . '" target="_blank">' . esc_html( get_the_title( $pid ) ) . '</a> &nbsp;';
			}
			return learndash_pfl_render_lesson_alert(
				esc_html__( 'To buy this lesson, purchase any of the following products.', 'learndash-pfl' )
				. ' &nbsp;' . $products_html
			);
		}

		// User has access — check whether earlier lessons in the course remain unpurchased.
		$courses = get_post_meta( $item_id, 'course_id' );
		if ( empty( $courses ) || ! is_array( $courses ) ) {
			return $content;
		}
		$course_id   = (int) $courses[0];
		$course_meta = get_post_meta( $course_id, 'ld_course_steps', true );
		$lesson_ar   = isset( $course_meta['steps']['h']['sfwd-lessons'] ) ? $course_meta['steps']['h']['sfwd-lessons'] : array();
		if ( ! is_array( $lesson_ar ) || count( $lesson_ar ) === 0 ) {
			return $content;
		}

		$page_data = array();
		foreach ( $lesson_ar as $key => $value ) {
			$posts_meta = unserialize( get_post_meta( $key, 'access_user_id', true ) );
			if ( ! is_array( $posts_meta ) || ! in_array( $user_id, $posts_meta, true ) ) {
				if ( '1' === (string) get_post_meta( $key, '_ld_lesson_active', true ) ) {
					$page_data[] = $key;
				}
			} elseif ( (int) $key === (int) $item_id ) {
				break;
			}
		}

		if ( count( $page_data ) > 0 ) {
			$permalink = esc_url( get_permalink( $page_data[0] ) );
			$body      = esc_html__( 'Please buy the previous lessons first. You are being redirected to', 'learndash-pfl' )
				. ' "' . esc_html( get_the_title( $page_data[0] ) ) . '" page.';
			$alert     = learndash_pfl_render_lesson_alert( $body );

			$script  = '<script type="text/javascript">'
				. 'setTimeout(function(){window.location.href=' . wp_json_encode( $permalink ) . ';}, 3000);'
				. '</script>';
			return $alert . $script;
		}

		return $content;
	}

	// Anonymous visitor.
	if ( first_lesson_fn( $item_id ) ) {
		if ( empty( $product_ids ) ) {
			return learndash_pfl_render_lesson_alert( $paid_msg );
		}
		$permalink = get_permalink( (int) $product_ids[0] );
		return learndash_pfl_render_lesson_alert(
			esc_html__( 'Please buy this lesson', 'learndash-pfl' )
			. ' <a href="' . esc_url( $permalink ) . '" target="_blank">'
			. esc_html__( 'Buy Now', 'learndash-pfl' )
			. '</a>'
		);
	}

	$return_url = esc_url( home_url( '/login/' ) );
	return learndash_pfl_render_lesson_alert(
		esc_html__( 'Please', 'learndash-pfl' ) . ' '
		. '<a href="' . $return_url . '" target="_blank">'
		. esc_html__( 'login', 'learndash-pfl' )
		. '</a> '
		. esc_html__( 'first to access this content.', 'learndash-pfl' )
	);
}
add_filter( 'the_content', 'lesson__add_to_content' );

add_filter('woocommerce_product_add_to_cart_text', 'pay_lesson_read_more_text');
function pay_lesson_read_more_text( $value ){
	global $product;
	if( isset( $product ) && is_object( $product )){
		$product_type = $product->get_type();
		if( 'lesson_type' == $product_type ){
			// phpcs:ignore WordPress.WP.I18n.TextDomainMismatch -- intentionally reusing WooCommerce's existing translation for "Add to cart" so the button label localizes consistently with the rest of the cart UI.
			$value = __( 'Add to cart', 'woocommerce' );
		}
	}
	return $value;
}

?>