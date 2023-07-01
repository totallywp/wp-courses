<?php
// Check if WooCommerce is active
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	// Add WooCommerce product panel to the course edit page
	function wp_courses_woocommerce_product_meta_box($post) {
		$products = get_post_meta($post->ID, 'wp_courses_woocommerce_products', true);
	
		// Check if $products is an array, if not initialize it as an empty array
		if (!is_array($products)) {
			$products = array();
		}
	
		$all_products = get_posts(array(
			'post_type' => 'product',
			'numberposts' => -1,
			'post_status' => 'publish',
		));
	
		echo '<p>Select the products required to unlock this course:</p>';
		echo '<select name="wp_courses_woocommerce_products[]" multiple="multiple">';
		foreach ($all_products as $product) {
			$selected = in_array($product->ID, $products) ? 'selected="selected"' : '';
			echo '<option value="' . $product->ID . '" ' . $selected . '>' . get_the_title($product->ID) . '</option>';
		}
		echo '</select>';
	}
	
	// Save WooCommerce product meta box data
	function wp_courses_save_woocommerce_product_meta_box($post_id) {
		if (isset($_POST['wp_courses_woocommerce_products'])) {
			$products = array_map('intval', $_POST['wp_courses_woocommerce_products']);
			update_post_meta($post_id, 'wp_courses_woocommerce_products', $products);
		}
	}
	add_action('save_post_course', 'wp_courses_save_woocommerce_product_meta_box');
	
	// Redirect non-logged in users to the login page
	function wp_courses_redirect_non_logged_in_users() {
		if (!is_user_logged_in()) {
			wp_redirect(wp_login_url());
			exit;
		}
	}
	add_action('template_redirect', 'wp_courses_redirect_non_logged_in_users');
	
	// Redirect logged in users who have not purchased the course
	function wp_courses_redirect_non_purchasers() {
		global $post;
	
		if (is_singular('course') && is_user_logged_in()) {
			$course_id = $post->ID;
			$products = get_post_meta($course_id, 'wp_courses_woocommerce_products', true);
	
			if (!empty($products)) {
				$purchased = false;
	
				foreach ($products as $product_id) {
					$current_user = wp_get_current_user(); // Get the WP_User object
					if (wc_customer_bought_product($current_user->ID, $current_user->user_email, $product_id)) { // Use $current_user->user_email to get the email
						$purchased = true;
						break;
					}
				}
	
				if (!$purchased) {
					wp_redirect(get_permalink($products[0]));
					exit;
				}
			}
		}
	}
	add_action('template_redirect', 'wp_courses_redirect_non_purchasers');
	
	// Add WooCommerce product panel to the course edit page
	function wp_courses_add_woocommerce_product_meta_box() {
		add_meta_box(
			'wp_courses_woocommerce_product_meta_box',
			'WooCommerce Products',
			'wp_courses_woocommerce_product_meta_box',
			'course',
			'side'
		);
	}
	add_action('add_meta_boxes_course', 'wp_courses_add_woocommerce_product_meta_box');
	
	// Add column to courses index admin page
	function wp_courses_add_custom_columns($columns) {
		$columns['required_products'] = 'Required Products';
		return $columns;
	}
	add_filter('manage_course_posts_columns', 'wp_courses_add_custom_columns');
	
	// Populate custom column with required products
	function wp_courses_populate_custom_column($column, $post_id) {
		if ($column === 'required_products') {
			$products = get_post_meta($post_id, 'wp_courses_woocommerce_products', true);
	
			if (!empty($products)) {
				$product_names = array();
				foreach ($products as $product_id) {
					$product = wc_get_product($product_id);
					if ($product) {
						$product_names[] = $product->get_name();
					}
				}
				echo implode(', ', $product_names);
			} else {
				echo '-';
			}
		}
	}
	add_action('manage_course_posts_custom_column', 'wp_courses_populate_custom_column', 10, 2);

}