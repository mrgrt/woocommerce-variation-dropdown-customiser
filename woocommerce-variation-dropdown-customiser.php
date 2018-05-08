<?php
/*
Plugin Name: WooCommerce - Variation Dropdown Customiser
Plugin URI: https://github.com/mrgrt/woocommerce-variation-dropdown-customiser
Description: Customise the "choose an option" dropdown for WooCommerce.
Author: Grahame Thomson
Version: 1.0
Author URI: http://www.grahamethomson.com
*/



add_filter( 'woocommerce_dropdown_variation_attribute_options_args', 'mmx_remove_select_text', 10);
add_filter( 'woocommerce_get_sections_products', 'woorei_mysettings_add_section' );
add_filter( 'woocommerce_get_settings_products', 'wcslider_all_settings', 10, 2 );

function mmx_remove_select_text( $args ){
  global $product;

  $variation_dropdown_text = get_option( 'variation_dropdown_text' );
  $variation_dropdown_label = get_option( 'variation_dropdown_label' );

  $args['show_option_none'] =  $variation_dropdown_text . " ";

  if($variation_dropdown_label=="yes"){
    $args['show_option_none'] .=  strtolower(wc_attribute_label($args['attribute'],$product));
  }

  return $args;
}


function woorei_mysettings_add_section( $sections ) {
  $sections['woocommerce-variation-dropdown-customiser'] = __( "Variation Dropdown Settings", 'text-domain' );
  return $sections;
}

function wcslider_all_settings( $settings, $current_section ) {

  //Check if is the section we are looking for.
  if ( $current_section == 'woocommerce-variation-dropdown-customiser' ) {

    $settings = [];
    $settings[] = array( 'name' => __( 'Variation Dropdown Settings', 'text-domain' ), 'type' => 'title', 'desc' => __( 'Settings to customise the dropdown for vartions.', 'text-domain' ), 'id' => 'woocommerce-variation-dropdown-customiser' );

    // Add the text to customsise for the dropdown.
    $settings[] = array(
      'name'     => __('Variation dropdown text: ', 'text-domain' ),
      'desc_tip' => __( 'This will change the dropdown text to choose a varation. Usually this says "choose an option".', 'text-domain' ),
      'id'       => 'variation_dropdown_text',
      'type'     => 'text',
    );

    // Add option for dynamically inserting the attribute name after text.
    $settings[] = array(
      'name'     => __('Use attribute name: ', 'text-domain' ),
      'desc_tip' => __( 'Check this box to automatically use the attribute name after the dropdown text e.g. "Chose an {attribute name}."', 'text-domain' ),
      'id'       => 'variation_dropdown_label',
      'type'     => 'checkbox',
    );

    $settings[] = array( 'type' => 'sectionend', 'id' => 'woocommerce-variation-dropdown-customiser' );


  }


  return $settings;

}
