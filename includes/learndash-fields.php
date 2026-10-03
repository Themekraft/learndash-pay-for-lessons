<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'learndash_lesson_meta_box' );
function learndash_lesson_meta_box() {
	if( dpflww_fs()->is_paying() ){
		add_meta_box(
			'learndash_lesson',
			__( 'WooCommerce Lesson Settings', 'learndash-pfl' ),
			'learndash_lesson_meta_box_callback',
			"sfwd-lessons"
		);
	}
}

function learndash_lesson_meta_box_callback( $post ) {
	wp_nonce_field( 'learndash_lesson_nonce', 'learndash_lesson_nonce' );

	$active      = '1' === (string) get_post_meta( $post->ID, '_ld_lesson_active', true );
	$question_ic = esc_url( plugins_url( 'lesson_buy/images/question.png' ) );

	?>
	<div id="_ld_lesson_price" class="sfwd_input sfwd_input_type_checkbox">
		<span class="sfwd_option_label" style="padding:10px 0px">
			<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php esc_attr_e( 'It will be marked as paid once it is added to a WooCommerce product, but you can leave it free if you prefer.', 'learndash-pfl' ); ?>">
				<img alt="" src="<?php echo esc_url( $question_ic ); ?>">
				<label for="_ld_lesson_price" class="sfwd_label">
					<?php esc_html_e( 'Mark as Paid', 'learndash-pfl' ); ?>
				</label>
			</a>
		</span>
		<span class="sfwd_option_input">
			<div class="sfwd_option_div">
				<fieldset>
					<legend class="screen-reader-text">
						<span><?php esc_html_e( 'Settings', 'learndash-pfl' ); ?></span>
					</legend>
					<p class="learndash-section-field-checkbox-p">
						<input type="checkbox" id="ld_lesson_active-yes" name="ld_lesson_active" value="1" <?php checked( $active ); ?> class="learndash-section-field learndash-section-field-checkbox ld-checkbox-input">
						<label class="ld-checkbox-input__label" for="ld_lesson_active-yes"><span><?php esc_html_e( 'Yes', 'learndash-pfl' ); ?></span></label>
					</p>
				</fieldset>
			</div>
		</span>
		<p class="ld-clear"></p>
	</div>

	<div id="sfwd_option_label_2" class="sfwd_input sfwd_input_type_text">
		<span class="sfwd_option_label">
			<a class="sfwd_help_text_link" style="cursor:pointer;" title="Click for Help!">
				<img alt="" src="<?php echo esc_url( $question_ic ); ?>">
				<label for="sfwd_option_label_2" class="sfwd_label">
					<?php esc_html_e( 'Product(s) Attached', 'learndash-pfl' ); ?>
				</label>
			</a>
		</span>
		<span class="sfwd_option_input">
			<div class="sfwd_option_div">
				<?php
				$product_ids = unserialize( get_post_meta( get_the_ID(), 'product_ids', true ) );
				if ( ! is_array( $product_ids ) || empty( $product_ids ) ) {
					echo esc_html__( 'No Product Attached', 'learndash-pfl' );
				} else {
					foreach ( $product_ids as $attached_product ) {
						$pid       = (int) $attached_product;
						$permalink = get_permalink( $pid );
						printf(
							' <a href="%s" target="_blank">%s</a> <br>',
							esc_url( $permalink ),
							esc_html( get_the_title( $pid ) )
						);
					}
				}
				?>
			</div>
		</span>
		<p class="ld-clear"></p>
	</div>

	<div id="sfwd_option_label_1" class="sfwd_input sfwd_input_type_text">
		<span class="sfwd_option_label">
			<a class="sfwd_help_text_link" style="cursor:pointer;" title="<?php esc_attr_e( 'Click for Help!', 'learndash-pfl' ); ?>">
				<img alt="" src="<?php echo esc_url( $question_ic ); ?>">
				<label for="sfwd_option_label_1" class="sfwd_label">
					<?php esc_html_e( 'Allowed Users', 'learndash-pfl' ); ?>
				</label>
			</a>
		</span>
		<span class="sfwd_option_input">
			<div class="sfwd_option_div">
				<?php
				$users       = get_users( array( 'fields' => 'all' ) );
				$access_user = unserialize( get_post_meta( get_the_ID(), 'access_user_id', true ) );
				if ( ! is_array( $access_user ) ) {
					$access_user = array();
				}
				?>
				<select multiple="multiple" class="select2" id="subscription_toggle_ids" name="access_user_id[]" data-placeholder="<?php esc_attr_e( 'Select User', 'learndash-pfl' ); ?>">
					<?php
					foreach ( $users as $user ) {
						$user_id    = (int) $user->data->ID;
						$user_login = $user->data->user_login;
						$first_name = get_user_meta( $user_id, 'first_name', true );
						$last_name  = get_user_meta( $user_id, 'last_name', true );
						$label      = trim( $first_name . ' ' . $last_name ) . ' (' . $user_login . ')';
						printf(
							'<option value="%1$s"%2$s>%3$s</option>',
							esc_attr( $user_id ),
							in_array( $user_id, $access_user, true ) ? ' selected' : '',
							esc_html( $label )
						);
					}
					?>
				</select>
			</div>
		</span>
		<p class="ld-clear"></p>
	</div>
	<?php
}

add_action( 'save_post', 'save_learndash_lesson_meta_box_data' );
function save_learndash_lesson_meta_box_data( $post_id ) {

	if ( ! isset( $_POST['learndash_lesson_nonce'] ) ) {
		return;
	}

	$nonce = sanitize_text_field( wp_unslash( $_POST['learndash_lesson_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'learndash_lesson_nonce' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : '';
	if ( 'page' === $post_type ) {
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$active = ( isset( $_POST['ld_lesson_active'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['ld_lesson_active'] ) ) ) ? '1' : '0';
	update_post_meta( $post_id, '_ld_lesson_active', $active );

	if ( isset( $_POST['access_user_id'] ) ) {
		$access_user_ids = array_map( 'absint', (array) wp_unslash( $_POST['access_user_id'] ) );
		$access_user_ids = array_values( array_filter( $access_user_ids ) );
		update_post_meta( $post_id, 'access_user_id', maybe_serialize( $access_user_ids ) );
	}
}

?>