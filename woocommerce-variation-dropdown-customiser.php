<?php
/*
Plugin Name: WooCommerce - Variation Dropdown Customiser
Plugin URI: https://github.com/mrgrt/woocommerce-variation-dropdown-customiser
Description: Customise the "choose an option" dropdown for WooCommerce.
Author: Grahame Thomson
Version: 1.0
Author URI: http://www.grahamethomson.com
*/

define('WCVDC_ATTRIBUTE_FIELD', 'wcvdc_attribute_field');

add_filter('woocommerce_dropdown_variation_attribute_options_args', 'wcvdc_dropdown_choice', 10);
add_filter('woocommerce_get_sections_products', 'wcvdc_dropdown_section' );
add_filter('woocommerce_get_settings_products', 'wcvdc_dropdown_settings', 10, 2 );
add_action('woocommerce_after_product_attribute_settings', 'wcvdc_attribute_settings', 10, 2 );
add_action('wp_ajax_woocommerce_save_attributes', 'wcvdc_ajax_save_attributes');


// Displays the custom "Choose an option" on the front end
function wcvdc_dropdown_choice( $args ){
  global $product;
  global $post;

  // Get the woocommerce settings
  $variation_dropdown_text = get_option( 'variation_dropdown_text' );
  $variation_dropdown_label = get_option( 'variation_dropdown_label' );


  // Get the global setting
  $global_variation_dropdown_text = get_option( WCVDC_ATTRIBUTE_FIELD . '_global_' . str_replace(' ', '_', strtolower(wc_attribute_label($args['attribute']))));


  if($variation_dropdown_text){
    $args['show_option_none'] =  $variation_dropdown_text . " ";
  }

  if($variation_dropdown_label=="yes"){
    $args['show_option_none'] .=  strtolower(wc_attribute_label($args['attribute'],$product));
  }


  if($global_variation_dropdown_text){
    $args['show_option_none'] = $global_variation_dropdown_text;
  }

  // Get the product attribute value
  $dropdownTextAttribute = wcvdc_get_attribute_value($post, $args['attribute'], WCVDC_ATTRIBUTE_FIELD);


  if($dropdownTextAttribute){
    $args['show_option_none'] = $dropdownTextAttribute;
  }


  return $args;
}


function wcvdc_get_attribute_value($post, $attributeName, $attributefield){

  $postMeta = get_post_meta($post->ID, '_' . $attributefield);
  $dropdownText = array_shift($postMeta) ?: array();
  $dropdownTextAttribute = $dropdownText[$attributeName];

  return $dropdownTextAttribute;

}


function wcvdc_dropdown_section( $sections ) {
  $sections['woocommerce-variation-dropdown-customiser'] = __( "Variation Dropdown Settings", 'text-domain' );
  return $sections;
}

// Add settings to woocommerce
function wcvdc_dropdown_settings( $settings, $current_section ) {

  //Check if is the section we are looking for.
  if ( $current_section == 'woocommerce-variation-dropdown-customiser' ) {

    $global_attributes = wc_get_attribute_taxonomies();

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


    // Create a field for each global attribute
    foreach($global_attributes as $global_attribute){
      $settings[] = array(
        'name'     => __($global_attribute->attribute_label . ' dropdown: ', 'text-domain' ),
        'desc_tip' => __( 'This will change the dropdown text to choose a varation. Usually this says "choose an option".', 'text-domain' ),
        'id'       => WCVDC_ATTRIBUTE_FIELD . '_global_'. str_replace(' ', '_', strtolower($global_attribute->attribute_label)),
        'type'     => 'text',
      );

    }


    $settings[] = array( 'type' => 'sectionend', 'id' => 'woocommerce-variation-dropdown-customiser' );


  }

  return $settings;

}

// Show the custom attribut settings
function wcvdc_attribute_settings($attribute, $i) {
    global $post;
    $dropdownTextAttribute = wcvdc_get_attribute_value($post, $attribute->get_name(), WCVDC_ATTRIBUTE_FIELD);
    echo '
        <tr>
            <td>
                <label>
                    <input type="text" class="" name="' . WCVDC_ATTRIBUTE_FIELD . '[' . $i . ']"
                    value="' . $dropdownTextAttribute. '">Dropdown text.
                </label>
            </td>
        </tr>
    ';
}


// Save the custom attribute data
function wcvdc_ajax_save_attributes() {
    parse_str($_POST['data'], $data);
    $product_id = absint($_POST['post_id']);
    $value_Attributes = array();
    $attributes = $data['attribute_names'];
    foreach ($attributes as $key => $attribute) {
        if (isset($data[WCVDC_ATTRIBUTE_FIELD][$key])) {
            $value_Attributes[$attribute] = $data[WCVDC_ATTRIBUTE_FIELD][$key];
        }
    }
    update_post_meta($product_id, '_' . WCVDC_ATTRIBUTE_FIELD,$value_Attributes);
}
