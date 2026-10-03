=== LearnDash Pay for Lesson ===
Contributors: svenl77, themekraft, buddyforms
Tags: learndash, woocommerce, lessons, pay for lesson, student
Requires at least: 5.9
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.4-beta.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

User a Custom Login and define the Login Redirect and Registration Link

== Description ==

This plugin enables you to sell LearnDash Lessons using the Woocommerce platform as payment intermediary.

---

> #### Follow Us
> [Blog](https://themekraft.com/blog/)

---

> **Powered with ❤ by [ThemeKraft](https://themekraft.com)**

---

== Installation ==

Upload the entire plugin folder to the /wp-content/plugins/ directory or install the plugin through the WordPress plugins screen directly.
Activate the plugin through the 'Plugins' menu in WordPress.

== Changelog ==
= 1.0.4 - 04 May 2026 =
* Plugin Check: added the License plugin-header field and stripped hidden macOS metadata from the release.
* Plugin Check: renamed the text domain from `learndash_pfl` to `learndash-pfl` (WP requires lowercase + hyphens) and propagated the new domain across every translation call.
* Plugin Check: removed the non-existent Domain Path header.
* Plugin Check: normalised line endings to LF (was mixed CRLF/LF).
* Plugin Check: added direct file access protection (`if ( ! defined( 'ABSPATH' ) ) exit;`) to every PHP file in the plugin.
* Plugin Check: hardened the `get_course_lessons` AJAX handler — added a capability check, nonce verification, sanitised the `courses` and `productID` request parameters with `absint`/`wp_unslash`, escaped the rendered `<option>` markup with `wp_kses`, replaced the variable-text `__()` call on lesson titles with `esc_html( get_the_title() )`, and removed the public (`nopriv`) AJAX hook so anonymous visitors can't hit the endpoint.
* Plugin Check: escaped the WooCommerce-not-active admin notice with `esc_html__`.
* Plugin Check: moved the select2 inline initialiser into `admin_print_footer_scripts` and marked the select2 enqueue as `in_footer`, plus passed a localized nonce + ajaxUrl object for the lesson-fetch flow.
* Cleaned up the `lesson-type.php` template: dropped the redundant `_e( esc_html( ... ) )` and emit the cart-text via `esc_html()`.
* Plugin Check: hardened the WooCommerce product save handler (`save_lesson_type_options_field`) — added a dedicated nonce + capability check, sanitised every `$_POST` access with `wp_unslash` + `sanitize_text_field`, and validated `_course_id` / `_lesson_id` arrays as positive integers.
* Plugin Check: hardened the LearnDash lesson save handler (`save_learndash_lesson_meta_box_data`) — sanitised + unslashed the existing nonce before verification, sanitised the `post_type` field with `sanitize_key`, and cast `access_user_id` array members through `absint`.
* Plugin Check: rewrote the lesson-content alert renderer (`lesson__add_to_content`) so every dynamic value is run through `esc_html`, `esc_url`, or `wp_json_encode` (for the JS redirect) — previously the rendered HTML interpolated raw `get_permalink()` URLs and translated post titles via the disallowed variable-string `__()` form.
* Plugin Check: rewrote the LearnDash lesson meta-box callback (`learndash_lesson_meta_box_callback`) and the WooCommerce multi-select helper (`woocommerce_wp_select_multiple`) to escape every output, including the user-name dropdown labels and the option list under "Allowed Users".
* Plugin Check: wired the existing `lpflajax` script-localised object to carry the new `get_course_lessons` nonce and an `i18n` bag; updated `assets/js/admin.js` to send the nonce as `_wpnonce` and replaced a long-standing bug where line 17 embedded a literal `<?php _e() ?>` tag inside the JS file (the alert would have rendered the raw template string).
* Plugin Check: dropped the external select2 CDN enqueue and now reuse WooCommerce's bundled select2 (`select2` script handle) and admin styles, since this enqueue only runs on the WC product edit screen.
* Plugin Check: annotated the intentional `meta_key` / `meta_query` lookup in `get_course_lessons` with `phpcs:ignore` (the lookup is required to find lessons by `course_id` and the AJAX endpoint is gated on `edit_products` + nonce).
* Cleaned up the user-facing English copy: meta-box label "Woocommece Lesson Setting" → "WooCommerce Lesson Settings", checkbox "Make As Paid" → "Mark as Paid", "Buy lesson plugin required woocommerce plugin to activate" → "LearnDash Pay for Lessons requires the WooCommerce plugin to be active.", "Plz buy previous lessons first..." → "Please buy the previous lessons first...", and several other rewritten strings for clarity.
* Plugin Check: cleaned up second-pass scan residuals — `esc_url($question_ic)` at the three echo points in `learndash-fields.php`, `sanitize_text_field` on `$_POST['ld_lesson_active']` and on the `$_REQUEST['courses']` map, repositioned the `slow_db_query` `phpcs:disable`/`phpcs:enable` block around the multi-line `$args` array (was previously a single-line ignore that didn't cover the array body), annotated the intentional WooCommerce-textdomain reuse on the "Add to cart" string, and annotated the read-only `$_REQUEST['post']` lookup in the admin script enqueue.
* Updated Freemius SDK to 2.13.1.
* Tested up to WordPress 6.9.
* Requires WordPress 5.9 or later and PHP 7.4 or later.
* Tested up to WordPress 7.1.

= 1.0.3 - 07 Feb 2023 =
* Fixed issue with ajax call on lesson product page.
* Fixed jQuery error related with select2 library.
* Enabled bundle license auto activation.
* Updated trial version.

= 1.0.2 - 26 Jan 2023 =
* Fixed issue with course access after having made the purchase.
* Fixed issue in lessons prior to plugin activation.
* Fixed issue with Add to cart text on archive pages.
* Tested up to WordPress 6.1.1 

= 1.0.1 - 01 Aug 2022 =
* Fixed issue with add to cart button.
* Removed the use of PHP session variables.
* Improved AJAX call for lesson lists.
* Added default css to buy button to adapt it to Woocommerce styles.
* Tested up to WordPress 6.0.1

= 1.0.0 - 06 Jul 2022 =
* First version release.