<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Elementor extends Utils {
	/**
	 * Ensure a CSS selector string includes the Elementor wrapper placeholder.
	 *
	 * If '{{WRAPPER}}' is not already in the string, it prepends it.
	 *
	 * @param string $string The CSS selector.
	 *
	 * @return string Selector including '{{WRAPPER}}'.
	 */
	public static function get_wrapper_selector( $string ) {
		return strpos( $string, '{{WRAPPER}}' ) !== false ? $string : "{{WRAPPER}} {$string}";
	}

	public static function date_types() {
		return apply_filters( 'mj\whitebox\utils\elementor\date_types', [
			'anytime'	=> esc_html__( 'All', 'mj-whitebox' ),
			'today'		=> esc_html__( 'Past Day', 'mj-whitebox' ),
			'week'		=> esc_html__( 'Past Week', 'mj-whitebox' ),
			'month'		=> esc_html__( 'Past Month', 'mj-whitebox' ),
			'quarter'	=> esc_html__( 'Past Quarter', 'mj-whitebox' ),
			'year'		=> esc_html__( 'Past Year', 'mj-whitebox' ),
			'exact'		=> esc_html__( 'Custom', 'mj-whitebox' ),
		] );
	}

	public static function orderby( $wc = false, $excludes = [] ) {
		if( !$wc ) {
			$orderby = [
				'post_date'		=> esc_html__( 'Date', 'mj-whitebox' ),
				'post_title'	=> esc_html__( 'Title', 'mj-whitebox' ),
				'modified'		=> esc_html__( 'Last Modified', 'mj-whitebox' ),
				'comment_count'	=> esc_html__( 'Comment Count', 'mj-whitebox' ),
				'rand'			=> esc_html__( 'Random', 'mj-whitebox' ),
			];
		} else {
			$orderby = [
				'ID'			=> esc_html__( 'ID', 'mj-whitebox' ),
				'name'			=> esc_html__( 'Product name', 'mj-whitebox' ),
				'type'			=> esc_html__( 'Product type', 'mj-whitebox' ),
				'post_date'		=> esc_html__( 'Date', 'mj-whitebox' ),
				'modified'		=> esc_html__( 'Last Modified', 'mj-whitebox' ),
				'price'			=> esc_html__( 'Price', 'mj-whitebox' ),
				'popularity'	=> esc_html__( 'Popularity', 'mj-whitebox' ),
				'rating'		=> esc_html__( 'Rating', 'mj-whitebox' ),
				'sales'			=> esc_html__( 'Sales', 'mj-whitebox' ),
				'rand'			=> esc_html__( 'Random', 'mj-whitebox' ),
			];
		}
		if( !empty( $excludes ) ) {
			$orderby = parent::unset( $orderby, $excludes );
		}

		$orderby = apply_filters( 'mj\whitebox\utils\elementor\orderby', $orderby );

		return $orderby;
	}
}