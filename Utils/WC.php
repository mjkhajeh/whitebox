<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class WC extends Utils {
	/**
	 * Retrieve the current WooCommerce account endpoint slug.
	 *
	 * This method determines the active endpoint in a WooCommerce "My Account" page.
	 * It checks the current query variables against WooCommerce account menu items
	 * (or a custom list of items if provided).
	 *
	 * The result is cached statically for performance, so subsequent calls will
	 * return the same value without reprocessing.
	 *
	 * @since 1.0.0
	 *
	 * @param array $items Optional. Custom menu items to check. Defaults to WooCommerce account menu items.
	 *                     The array keys should be endpoint slugs.
	 *
	 * @return string The detected account endpoint slug. Defaults to 'dashboard' if no match is found.
	 */
	public static function get_account_endpoint( $items = [] ) {
		static $endpoint = '';
		if( $endpoint === '' && function_exists( 'wc_get_account_menu_items' ) ) {
			if( empty( $items ) ) {
				$items = array_keys( wc_get_account_menu_items() );
			} else {
				$items = array_keys( $items );
			}
			$items[] = 'view-order';
			global $wp;
			if( !empty( $wp->query_vars ) ) {
				$intersect = array_intersect( $items, array_keys( $wp->query_vars ) );
				if( $intersect ) {
					$endpoint = array_values( $intersect )[0];
				} else {
					$endpoint = 'dashboard';
				}
			}
		}
		return $endpoint;
	}

		/**
	 * Get the number of items in the WooCommerce cart.
	 *
	 * Returns 0 if WooCommerce or the cart is not available. The result
	 * is cached statically for performance.
	 *
	 * @return int Number of items in the cart.
	 */
	public static function get_cart_count() : int {
		if( !function_exists( 'WC' ) ) return 0;
		if( empty( WC()->cart ) ) return 0;
		static $count = null;
		if( $count === null ) {
			$count = WC()->cart->get_cart_contents_count();
		}
		return $count;
	}

	/**
	 * Get all active WooCommerce coupons available for the current user.
	 *
	 * Considers coupon expiry dates, email restrictions, and usage limits.
	 * Returns an array of WC_Coupon objects.
	 *
	 * @return array[WC_Coupon] Array of active WC_Coupon objects for the current user.
	 */
	public static function get_active_coupons_for_user() {
		$user = wp_get_current_user();
	
		if( !$user->ID ) {
			return []; // Return empty if no user is logged in
		}
	
		// Get the current date
		$current_date = date( 'Y-m-d H:i:s' );
	
		// Query WooCommerce coupons
		$args = [
			'post_type'			=> 'shop_coupon',
			'posts_per_page'	=> -1,
			'post_status'		=> 'publish',
			'meta_query'		=> [
				'relation' => 'OR',
				[
					'key'     => 'expiry_date',
					'value'   => $current_date,
					'compare' => '>=', // Coupon has not expired
					'type'    => 'DATETIME',
				],
				[
					'key'     => 'expiry_date',
					'compare' => 'NOT EXISTS', // Coupons with no expiry date
				],
			],
			'fields'			=> 'ids', // Only retrieve IDs for efficiency
			'no_found_rows'		=> true,
		];
	
		$coupons = get_posts( $args );
		$active_coupons = [];
	
		// Filter coupons based on usage and user restrictions
		foreach( $coupons as $coupon_id ) {
			$coupon = new \WC_Coupon( $coupon_id );
	
			// Check if the coupon has usage restrictions
			$allowed_users = $coupon->get_email_restrictions();
	
			// If there are specific allowed users, check if the current user is allowed
			if( !empty( $allowed_users ) && !in_array( $user->user_email, $allowed_users, true ) ) {
				continue; // Skip coupons not allowed for this user
			}
	
			// Check if usage limit has been reached
			if( $coupon->get_usage_limit() && $coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
				continue; // Skip coupons that have reached their usage limit
			}
	
			$active_coupons[] = $coupon;
		}
	
		return $active_coupons;
	}

	/**
	 * Returns an array of Iranian currencies for convert to rial.
	 *
	 * @return array An associative array where the keys represent the currency codes and the values represent the equalization rates to the Iranian rial.
	 */
	public static function ir_currencies() {
		return [ // Array of Iranian currencies for convert to rial
			'IRR'	=> 1, // Rial
			'IRT'	=> 0.1, // Toman
			'IRHR'	=> 0.001, // 1000 Rial
			'IRHT'	=> 0.0001, // 1000 Toman
		];
	}

	public static function ir_currencies_label() {
		return [
			'IRR'	=> __( 'Rial', 'mj-whitebox' ),
			'IRT'	=> __( 'Toman', 'mj-whitebox' ),
			'IRHR'	=> __( 'Thousand rials', 'mj-whitebox' ),
			'IRHT'	=> __( 'Thousand Tomans', 'mj-whitebox' ),
		];
	}
}