<?php
/*
Plugin Name: Custom Post Type Code Generator
Plugin URI: http://fooplugins.com
Description: Generates Custom Posts Type code output from a Gravity Form
Version: 1.1
Author: Brad Vincent
Author URI: http://fooplugins.com
License: GPL2

------------------------------------------------------------------------
Copyright 2016 FooPlugins

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

// Include Gravity Forms
if ( ! class_exists( 'RGForms' ) ) {
	@include_once( WP_PLUGIN_DIR . '/gravityforms/gravityforms.php' );
}
if ( ! class_exists( 'RGFormsModel' ) ) {
	@include_once( WP_PLUGIN_DIR . '/gravityforms/forms_model.php' );
}
if ( ! class_exists( 'GFCommon' ) ) {
	@include_once( WP_PLUGIN_DIR . '/gravityforms/common.php' );
}

add_action( 'init', array( 'CPTCodeGen', 'init' ) );

class CPTCodeGen {

	public static function init() {
		add_shortcode( 'cptcodegenerator', array( 'CPTCodeGen', 'shortcode' ) );
	}

	public static function shortcode() {
		if ( !class_exists('RGFormsModel') ) return;

		$entry = RGFormsModel::get_lead( $_GET['id'] );

		/* general */
		$single      = $entry['1'];
		$plural      = $entry['2'];
		$cpt         = self::if_null( strtolower( $entry['4'] ), str_replace( ' ', '_', strtolower( $single ) ) );
		$description = $entry['53'];

		if ( ! empty( $description ) ) {
			$description_code = "'description' => " . self::surround_with_quotes( self::escape( $description ) ) . ",";
		}

		$hierarchical = $entry['18'];

		/* features */

		$features = self::if_not_null( $entry['3.1'], "'title', " );
		$features .= self::if_not_null( $entry['3.2'], "'editor', " );
		$features .= self::if_not_null( $entry['3.3'], "'excerpt', " );
		$features .= self::if_not_null( $entry['3.4'], "'author', " );
		$features .= self::if_not_null( $entry['3.5'], "'thumbnail', " );
		$features .= self::if_not_null( $entry['3.6'], "'trackbacks', " );
		$features .= self::if_not_null( $entry['3.7'], "'custom-fields', " );
		$features .= self::if_not_null( $entry['3.8'], "'comments', " );
		$features .= self::if_not_null( $entry['3.9'], "'revisions', " );
		$features .= self::if_not_null( $entry['3.11'], "'page-attributes', " );
		$features .= self::if_not_null( $entry['3.12'], "'post-formats', " );

		//cut off ending comma
		if ( self::ends_with( $features, ', ' ) ) {
			$features = substr( $features, 0, - 2 );
		}

		$tax = self::if_not_null( $entry['19.1'], "'" . $entry['19.1'] . "', " );
		$tax .= self::if_not_null( $entry['19.2'], "'" . $entry['19.2'] . "', " );
		$tax .= self::if_not_null( $entry['19.3'], "'" . $entry['19.3'] . "', " );

		if ( ! empty( $entry['20'] ) ) {
			if ( strpos( $entry['20'], ',' ) === false ) {
				$tax .= self::surround_with_quotes( $entry['20'] );
			} else {
				$custom_taxes = explode( ',', str_replace( ';', ',', $entry['20'] ) );
				foreach ( $custom_taxes as $custom_tax ) {
					$tax .= self::surround_with_quotes( str_replace( ' ', '_', strtolower( trim( $custom_tax ) ) ) ) . ', ';
				}
			}
		}

		//cut off ending comma
		if ( self::ends_with( $tax, ', ' ) ) {
			$tax = substr( $tax, 0, - 2 );
		}

		if ( ! empty( $tax ) ) {
			$tax_code = "'taxonomies' => array( {$tax} ),";
		}

		/* labels */

		$label_add_new            = self::escape( self::if_null( $entry['6'], 'Add New' ) );
		$label_add_new_item       = self::escape( self::if_null( $entry['7'], 'Add New ' . $single ) );
		$label_edit_item          = self::escape( self::if_null( $entry['8'], 'Edit ' . $single ) );
		$label_new_item           = self::escape( self::if_null( $entry['9'], 'New ' . $single ) );
		$label_view_item          = self::escape( self::if_null( $entry['10'], 'View ' . $single ) );
		$label_search_items       = self::escape( self::if_null( $entry['11'], 'Search ' . $plural ) );
		$label_not_found          = self::escape( self::if_null( $entry['12'], 'No ' . strtolower( $plural ) . ' found' ) );
		$label_not_found_in_trash = self::escape( self::if_null( $entry['13'], 'No ' . strtolower( $plural ) . ' found in Trash' ) );
		$label_parent_text        = self::escape( self::if_null( $entry['14'], 'Parent ' . $single . ':' ) );
		$label_menu_name          = self::escape( self::if_null( $entry['15'], $plural ) );

		/* visibility */

		$public               = $entry['30'];
		$show_ui              = $entry['21'];
		$show_in_menu         = $entry['22'];
		$show_in_menu_custom  = $entry['23'];
		$menu_position        = $entry['24'];
		$menu_position_custom = $entry['25'];
		$menu_icon            = $entry['26'];
		$show_in_nav_menus    = $entry['55'];

		if ( $show_ui == 'true' ) {

			if ( $menu_position == 'custom' ) {
				$menu_position = $menu_position_custom;
			}

			if ( $menu_position != 25 ) {
				$menu_position_code = "'menu_position' => " . $menu_position . ",";
			}

			if ( $show_in_menu == 'custom' ) {
				$show_in_menu = self::surround_with_quotes( $show_in_menu_custom );
			}

			if ( $show_in_menu != 'false' ) {
				$show_in_menu_code = "'show_in_menu' => " . $show_in_menu . ",";
			}

		}

		if ( ! empty( $menu_icon ) ) {
			$menu_icon_code = "'menu_icon' => '" . $menu_icon . "',";
		}

		/* Options */

		$publicly_querable   = $entry['28'];
		$exclude_from_search = $entry['29'];
		$has_archive         = $entry['31'];
		$has_archive_custom  = $entry['32'];

		if ( $has_archive == 'custom' ) {
			$has_archive = self::surround_with_quotes( $has_archive_custom );
		}

		$query_var        = $entry['33'];
		$query_var_custom = $entry['34'];

		if ( $query_var == 'custom' ) {
			$query_var = self::surround_with_quotes( $query_var_custom );
		}

		$can_export = $entry['54'];

		$rewrite            = $entry['35'];
		$rewrite_slug       = $entry['36'];
		$rewrite_with_front = $entry['38'];
		$rewrite_feeds      = $entry['56'];
		$rewrite_pages      = $entry['39'];

		if ( $rewrite == 'custom' ) {
			$rewrite_code = "'rewrite' => array(
			'slug' => " . self::surround_with_quotes( $rewrite_slug ) . ",
			'with_front' => {$rewrite_with_front},
			'feeds' => {$rewrite_feeds},
			'pages' => {$rewrite_pages}
		),";
		} else {
			$rewrite_code = "'rewrite' => {$rewrite},";
		}

		/* capabilities */

		$advanced_capabilities = $entry['45'];

		$capability_type        = $entry['42'];
		$custom_capability_type = $entry['43'];

		if ( $capability_type == 'custom' ) {
			$capability_type = $custom_capability_type;
		}

		$edit_post_capability          = $entry['47'];
		$edit_posts_capability         = $entry['46'];
		$edit_others_posts_capability  = $entry['48'];
		$publish_posts_capability      = $entry['49'];
		$read_post_capability          = $entry['50'];
		$read_private_posts_capability = $entry['51'];
		$delete_post_capability        = $entry['52'];

		if ( $advanced_capabilities == 'false' ) {
			$capabilities_code = "'capability_type' => " . self::surround_with_quotes( $capability_type );
		} else {
			$capabilities_code = "'capabilities' => array(
			'edit_post' => " . self::surround_with_quotes( $edit_post_capability ) . ",
			'edit_posts' => " . self::surround_with_quotes( $edit_posts_capability ) . ",
			'edit_others_posts' => " . self::surround_with_quotes( $edit_others_posts_capability ) . ",
			'publish_posts' => " . self::surround_with_quotes( $publish_posts_capability ) . ",
			'read_post' => " . self::surround_with_quotes( $read_post_capability ) . ",
			'read_private_posts' => " . self::surround_with_quotes( $read_private_posts_capability ) . ",
			'delete_post' => " . self::surround_with_quotes( $delete_post_capability ) . "
		)";
		}

		$code = "add_action( 'init', 'register_cpt_{$cpt}' );

function register_cpt_{$cpt}() {

	\$labels = array(
		'name' => __( '{$plural}', '{$cpt}' ),
		'singular_name' => __( '{$single}', '{$cpt}' ),
		'add_new' => __( '{$label_add_new}', '{$cpt}' ),
		'add_new_item' => __( '{$label_add_new_item}', '{$cpt}' ),
		'edit_item' => __( '{$label_edit_item}', '{$cpt}' ),
		'new_item' => __( '{$label_new_item}', '{$cpt}' ),
		'view_item' => __( '{$label_view_item}', '{$cpt}' ),
		'search_items' => __( '{$label_search_items}', '{$cpt}' ),
		'not_found' => __( '{$label_not_found}', '{$cpt}' ),
		'not_found_in_trash' => __( '{$label_not_found_in_trash}', '{$cpt}' ),
		'parent_item_colon' => __( '{$label_parent_text}', '{$cpt}' ),
		'menu_name' => __( '{$label_menu_name}', '{$cpt}' ),
	);

	\$args = array(
		'labels' => \$labels,
		'hierarchical' => {$hierarchical},
		{$description_code}
		'supports' => array( {$features} ),
		{$tax_code}
		'public' => {$public},
		'show_ui' => {$show_ui},
		{$show_in_menu_code}
		{$menu_position_code}
		{$menu_icon_code}
		'show_in_nav_menus' => {$show_in_nav_menus},
		'publicly_queryable' => {$publicly_querable},
		'exclude_from_search' => {$exclude_from_search},
		'has_archive' => {$has_archive},
		'query_var' => {$query_var},
		'can_export' => {$can_export},
		{$rewrite_code}
		{$capabilities_code}
	);

	register_post_type( '{$cpt}', \$args );
}";

		//remove any excess newlines from the code output
		$code = preg_replace("/\t\t\r\n/", '', $code);
		$code = preg_replace("/\t\t\n/", '', $code);

		return '<pre class="code">' . $code . '</pre>';

	}

	public static function if_null( $input, $alt ) {
		if ( empty( $input ) ) {
			return $alt;
		}

		return $input;
	}

	public static function if_not_null( $input, $alt ) {
		if ( ! empty( $input ) ) {
			return $alt;
		}

		return '';
	}

	public static function ends_with( $haystack, $needle ) {
		$length = strlen( $needle );
		$start  = $length * - 1; //negative
		return ( substr( $haystack, $start, $length ) === $needle );
	}

	public static function surround_with_quotes( &$input ) {
		return str_replace( "''", "'", "'" . $input . "'" );
	}

	public static function escape( $input ) {
		return str_replace( "'", "\'", $input );
	}
}
