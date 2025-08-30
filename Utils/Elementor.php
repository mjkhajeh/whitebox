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

	public static function button_types( $args = [] ) {
		$types = [
			'primary'	=> esc_html_x( 'Primary', 'Button type', 'mj-whitebox' ),
			'secondary'	=> esc_html_x( 'Secondary', 'Button type', 'mj-whitebox' ),
			'gray'		=> esc_html_x( 'Gray', 'Button type', 'mj-whitebox' ),
			'white'		=> esc_html_x( 'White', 'Button type', 'mj-whitebox' ),
			'action'	=> esc_html_x( 'Action', 'Button type', 'mj-whitebox' ),
			'bordered'	=> esc_html_x( 'Bordered', 'Button type', 'mj-whitebox' ),
		];
		$types = apply_filters( 'mj\whitebox\elementor_controls\button\types', $types, $args );
		return $types;
	}

	public static function button_styles( $args = [] ) {
		$styles = [
			'normal'	=> esc_html_x( 'Normal', 'Button style', 'mj-whitebox' ),
			'rounded'	=> esc_html_x( 'Rounded', 'Button style', 'mj-whitebox' ),
			'circle'	=> esc_html_x( 'Circle', 'Button style', 'mj-whitebox' ),
		];
		$styles = apply_filters( 'mj\whitebox\elementor_controls\button\styles', $styles, $args );
		return $styles;
	}

	public static function date_types( $args = [] ) {
		return apply_filters( 'mj\whitebox\utils\elementor\date_types', [
			'anytime'	=> esc_html__( 'All', 'mj-whitebox' ),
			'today'		=> esc_html__( 'Past Day', 'mj-whitebox' ),
			'week'		=> esc_html__( 'Past Week', 'mj-whitebox' ),
			'month'		=> esc_html__( 'Past Month', 'mj-whitebox' ),
			'quarter'	=> esc_html__( 'Past Quarter', 'mj-whitebox' ),
			'year'		=> esc_html__( 'Past Year', 'mj-whitebox' ),
			'exact'		=> esc_html__( 'Custom', 'mj-whitebox' ),
		], $args );
	}

	public static function orderby( $wc = false, $excludes = [], $args = [] ) {
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

		$orderby = apply_filters( 'mj\whitebox\utils\elementor\orderby', $orderby, $wc, $excludes, $args );

		return $orderby;
	}

	/**
	 * Generate link HTML attributes from Elementor link settings
	 *
	 * @param array|string $link Elementor link settings
	 * @return array HTML attributes of the link
	 */
	public static function get_link_attributes( $link = [] ) {
		$url_attrs = [];
		$rel_string = '';

		if( !empty( $link ) && !is_array( $link ) ) {
			$link = [
				'url'	=> $link
			];
		}

		if ( ! empty( $link['url'] ) ) {
			$url_attrs['href'] = esc_url( $link['url'] );
		}

		if ( ! empty( $link['is_external'] ) ) {
			$url_attrs['target'] = '_blank';
			$rel_string .= 'noopener ';
		}

		if ( ! empty( $link['nofollow'] ) ) {
			$rel_string .= 'nofollow ';
		}

		if ( ! empty( $rel_string ) ) {
			$url_attrs['rel'] = $rel_string;
		}

		if( class_exists( "\Elementor\Utils" ) ) {
			$url_combined_attrs = array_merge(
				$url_attrs,
				\Elementor\Utils::parse_custom_attributes( $link['custom_attributes'] ?? '' ),
			);
			return $url_combined_attrs;
		} else {
			$result = [];
			// Copied from \Elementor\Utils::parse_custom_attributes
			foreach ( $url_attrs as $attribute ) {
				$attr_key_value = explode( '|', $attribute );

				$attr_key = mb_strtolower( $attr_key_value[0] );

				// Remove any not allowed characters.
				preg_match( '/[-_a-z0-9]+/', $attr_key, $attr_key_matches );

				if ( empty( $attr_key_matches[0] ) ) {
					continue;
				}

				$attr_key = $attr_key_matches[0];

				// Avoid Javascript events and unescaped href.
				if ( 'href' === $attr_key || 'on' === substr( $attr_key, 0, 2 ) ) {
					continue;
				}

				if ( isset( $attr_key_value[1] ) ) {
					$attr_value = trim( $attr_key_value[1] );
				} else {
					$attr_value = '';
				}

				$result[ $attr_key ] = $attr_value;
			}
			return $result;
		}
	}

	/**
	 *	'desktop_slider'		=> false,
	 *	'desktop_slides_type'	=> 'auto', // auto | count
	 *	'desktop_slides'		=> $desktop_columns,
	 *	'desktop_slides_space'	=> 0,
	 *	'desktop_cols'			=> $desktop_columns,
	 *	'desktop_row_gap'		=> 16,
	 *	'desktop_column_gap'	=> 16,
	 *	
	 *	'tablet_slider'			=> false,
	 *	'tablet_slides_type'	=> 'auto',
	 *	'tablet_slides'			=> 4,
	 *	'tablet_slides_space'	=> 0,
	 *	'tablet_cols'			=> 2,
	 *	'tablet_row_gap'		=> 16,
	 *	'tablet_column_gap'		=> 16,
	 * 
	 *	'mobile_slider'			=> false,
	 *	'mobile_slides_type'	=> 'auto',
	 *	'mobile_slides'			=> 4,
	 *	'mobile_slides_space'	=> 0,
	 *	'mobile_cols'			=> 1,
	 *	'mobile_row_gap'		=> 16,
	 *	'mobile_column_gap'		=> 16,
	 */
	/**
	 * Get display attributes for slider and columns in themes and plugins
	 *
	 * @param array $settings [
	 * 	'desktop_slider'		=> false,
	 *	'desktop_slides_type'	=> 'auto', // auto | count
	 *	'desktop_slides'		=> $desktop_columns,
	 *	'desktop_slides_space'	=> 0,
	 *	'desktop_cols'			=> $desktop_columns,
	 *	'desktop_row_gap'		=> 16,
	 *	'desktop_column_gap'	=> 16,
	 *
	 *	'tablet_slider'			=> false,
	 *	'tablet_slides_type'	=> 'auto',
	 *	'tablet_slides'			=> 4,
	 *	'tablet_slides_space'	=> 0,
	 *	'tablet_cols'			=> 2,
	 *	'tablet_row_gap'		=> 16,
	 *	'tablet_column_gap'		=> 16,
	 *
	 *	'mobile_slider'			=> false,
	 *	'mobile_slides_type'	=> 'auto',
	 *	'mobile_slides'			=> 4,
	 *	'mobile_slides_space'	=> 0,
	 *	'mobile_cols'			=> 1,
	 *	'mobile_row_gap'		=> 16,
	 *	'mobile_column_gap'		=> 16,
	 * ]
	 * @param boolean $slider_mode Force activate slider for all devices
	 * @param array $other_slider_attrs More args for slider. It will be applied for all devices. SwiperJS options.
	 * @return array [
	 * 	'wrap_classes' This is for element that has 'swiper' class
	 * 	'classes' This is for element that has 'swiper-wrapper' class
	 * 	'args' The slider and other settings. This is for element that has 'swiper' class.
	 * 	'style' Some styles. This is for element that has 'swiper' class
	 * ]
	 */
	public static function get_display_attributes( array $settings, $slider_mode = false, $other_slider_attrs = [] ) {
		$wrap_classes = [];
		$classes = [];
		$args = [];
		$styles = [];
		$devices = ['desktop', 'tablet', 'mobile'];

		if( !empty( $settings ) ) {
			foreach( $devices as $device ) {
				if( $slider_mode ) {
					$settings["{$device}_slider"] = true;
				}

				$args[$device]["slider"]['enabled'] = parent::to_bool( $settings["{$device}_slider"] );
				if( $args[$device]["slider"]['enabled'] ) {
					$settings["{$device}_slides_space"] = isset( $settings["{$device}_slides_space"] ) ? floatval( $settings["{$device}_slides_space"] ) : 0;

					$wrap_classes[] = "{$device}-slider-wrap";
					$classes[] = "{$device}-slider";
					$args[$device]["slider"]["slidesPerView"] = $settings["{$device}_slides_type"] == 'count' ? $settings["{$device}_slides"] : 'auto';
					$args[$device]["slider"]["spaceBetween"] = $settings["{$device}_slides_space"];

					if( $settings["{$device}_slides_type"] == 'auto' ) {
						$classes[] = "{$device}-slider-auto";
					}
					$styles["--{$device}-space"] = "{$settings["{$device}_slides_space"]}px";

					$args[$device]["slider"] = array_merge( $args[$device]["slider"], $other_slider_attrs );
				} else {
					$wrap_classes[] = "{$device}-columns-wrap";
					$classes[] = "{$device}-columns";
					$classes[] = "{$device}-columns-{$settings["{$device}_cols"]}";
					$display = 'grid';
					if( !empty( $settings["{$device}_display"] ) ) {
						$display = $settings["{$device}_display"];
					}
					$classes[] = "{$device}-display-{$display}";
					if( $display === 'grid' ) {
						$args[$device]["columns"] = $settings["{$device}_cols"];
						$styles["--{$device}-cols"] = $settings["{$device}_cols"];
					}

					if( isset( $settings["{$device}_gap"] ) ) {
						$styles["--{$device}-gap"] = $settings["{$device}_gap"] . "px";
					} else {
						if( isset( $settings["{$device}_row_gap"] ) && isset( $settings["{$device}_column_gap"] ) ) {
							$styles["--{$device}-gap"] = "{$settings["{$device}_row_gap"]}px {$settings["{$device}_column_gap"]}px";
						}
						if( isset( $settings["{$device}_row_gap"] ) ) {
							$styles["--{$device}-row-gap"] = $settings["{$device}_row_gap"] . "px";
						}
						if( isset( $settings["{$device}_column_gap"] ) ) {
							$styles["--{$device}-column-gap"] = $settings["{$device}_column_gap"] . "px";
						}
					}
				}
			}
		}

		return [
			'wrap_classes'	=> $wrap_classes,
			'classes'		=> $classes,
			'args'			=> $args,
			'style'			=> $styles
		];
	}

	public static function get_button_args( array $settings, string $prefix = 'button_' ) {
		$icon = '';
		if( isset( $settings['button_icon'] ) ) {
			$icon = $settings['button_icon']['value'];
			if( is_array( $icon ) && !empty( $icon['url'] ) ) {
				$icon = $icon['url'];
			}
		}

		$args = [];
		if( isset( $settings['button_type'] ) ) {
			$args["{$prefix}type"] = $settings['button_type'];
		}
		if( isset( $settings['button_transparent'] ) ) {
			$args["{$prefix}transparent"] = Utils::to_bool( $settings['button_transparent'] );
		}
		if( isset( $settings['button_small'] ) ) {
			$args["{$prefix}small"] = Utils::to_bool( $settings['button_small'] );
		}
		if( $icon ) {
			$args["{$prefix}icon"] = $icon;
		}
		if( isset( $settings['button_text'] ) ) {
			$args["{$prefix}text"] = $settings['button_text'];
		}
		if( isset( $settings['button_link'] ) ) {
			$args["{$prefix}link"] = $settings['button_link'];
		}
		if( isset( $settings['button_new_tab'] ) ) {
			$args["{$prefix}new_tab"] = Utils::to_bool( $settings['button_new_tab'] );
			if( $args["{$prefix}new_tab"] ) {
				$args["{$prefix}link"]['is_external'] = 'on';
			}
		}
		if( isset( $settings['button_icon_align'] ) ) {
			$args["{$prefix}icon_align"] = $settings['button_icon_align'];
		}
		if( isset( $settings['button_style'] ) ) {
			$args["{$prefix}style"] = $settings['button_style'];
		}
		if( isset( $settings['button_align'] ) ) {
			$args["{$prefix}align"] = $settings['button_align'];
		}
		
		return $args;
	}

	public static function check_button_defaults( array $args, string $prefix = 'button_' ) {
		$rtl = is_rtl();

		if( isset( $args["{$prefix}link"] ) && !is_array( $args["{$prefix}link"] ) ) {
			$args["{$prefix}link"] = [
				'url'				=> $args["{$prefix}link"],
				'is_external'		=> isset( $args["{$prefix}new_tab"] ) && Utils::to_bool( $args["{$prefix}new_tab"] ),
				'nofollow'			=> isset( $args["{$prefix}new_tab"] ) && Utils::to_bool( $args["{$prefix}new_tab"] ),
				'custom_attributes'	=> '',
			];
		}

		$args = Utils::check_default( $args, [
			"{$prefix}transparent"	=> false,
			"{$prefix}type"			=> 'primary',
			"{$prefix}small"		=> false,
			"{$prefix}icon"			=> '',
			"{$prefix}text"			=> '',
			"{$prefix}title"		=> '',
			"{$prefix}link"			=> [],
			"{$prefix}new_tab"		=> false,
			"{$prefix}fullwidth"	=> false,
			"{$prefix}icon_align"	=> $rtl ? 'right' : 'left',
			"{$prefix}style"		=> 'rounded',
			"{$prefix}align"		=> $rtl ? 'right' : 'left',
			"{$prefix}classes"		=> [],
			"{$prefix}id"			=> '',
			"{$prefix}disabled"		=> false,
			"{$prefix}loading"		=> false,
			"{$prefix}atts"			=> [],
		], ["{$prefix}icon"] );
		return $args;
	}
}