<?php
/**
 * @package shap-interactive-map
 * @version 0.0.1
 */
/*
Plugin Name: Syrian Heritage Map
Plugin URI:  https://github.com/mcq-dev/
Description: Wordpress plugin Interactive map to navigate the places and images of the Syrian Heritage Archive project. Must be used together with the sha-importer plugin: https://github.com/csgis/wordpress-sha--importer
Author:	     mcq-dev
Author URI:	 https://www.mcqdev.de/
Version:     0.0.1
Contributors: mcq-dev, sosias, Wissam-G, stefanovacchi
License: MIT
License URI: https://opensource.org/licenses/MIT
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'rest_api_init', function () {
  register_rest_route( 'map/v1', '/places/?', array(
    'methods' => 'GET',
    'callback' => __NAMESPACE__.'\\get_all_taxonomy_places_filtered',
  ) );
} );

add_action('rest_api_init', function () {
    register_rest_route( 'map/v1', '/images_of_place/(?P<term_id>\d+)',array(
                  'methods'  => 'GET',
                  'callback' => __NAMESPACE__.'\\get_all_images_of_place_term_id'
        ));
  });

add_action('rest_api_init', function () {
    register_rest_route( 'map/v1', '/filters/?',array(
                  'methods'  => 'GET',
                  'callback' => __NAMESPACE__.'\\get_all_taxonomy_filter_terms'
        ));
  });


function get_all_images_of_place_term_id($request) {

    $args = array(
            'term_id' => $request['term_id']
    );

    $posts = get_posts(array(
        'post_type' => 'attachment',
        'post_status'    => 'inherit',
        'numberposts' => -1,
        'tax_query' => array(
          array(
            'taxonomy' => 'shap_places',
            'field' => 'term_id', 
            'terms' => $args['term_id'],
            'include_children' => true
          )
        )
      ));

    foreach ( $posts as $image ) {
        $meta = get_post_meta( $image->ID );
        $image->meta = $meta;
        $pool = wp_get_post_terms( $image->ID, ['shap_pool']);
        $image->pool = $pool;
        $thumbnail = wp_get_attachment_image_url( $image->ID , 'thumbnail');
        $image->thumbnail = $thumbnail;
    }

    if (empty($posts)) {
        return new WP_Error( 'empty_term', 'there is no post with this term', array('status' => 404) );
    }

    $response = new WP_REST_Response($posts);
    $response->set_status(200);

    return $response;
}

// shap_tags: "shap_tags",
// shap_places: "shap_places",
// shap_time: "shap_time",
// shap_theme: "shap_theme",
// shap_subject: "shap_subject",
// shap_pool: "shap_pool"


// format places as geojson and add some useful fields (meta)
function add_additional_fields_places($places_list){

    $places = array();
    foreach ( $places_list as $place ) {
        $element = new class{};
        $places[] = $element;
        $element->type = "Feature";
        $element->properties = $place;
        $meta = get_term_meta( $place->term_id );
        $place->meta = $meta;
        $element->geometry = new class{};
        $element->geometry->type = "Point";
        if(array_key_exists('longitude',$meta) && array_key_exists('latitude',$meta)){
            $element->geometry->coordinates = [ $meta['longitude'][0], $meta['latitude'][0] ];
        }else{
            $element->geometry->coordinates = [ 0, 0 ];
        }
    }

    $geojson = new class{};
    $geojson->type = "FeatureCollection";
    $geojson->crs = json_decode('{ "type": "name", "properties": { "name": "urn:ogc:def:crs:OGC:1.3:CRS84" }}');
    $geojson->features = $places;

    return $geojson;
}

// get all places
// if query pramaters are set, it filter the places. Otherwise return all places
// available params are:
// * time = construction date
function get_all_taxonomy_places_filtered($data){

    $time = $data->get_param( 'time' );
    $place = $data->get_param( 'place' );
    $fileclass = $data->get_param( 'fileclass' );

    if(!$time && !$place && !$fileclass){
        //return 'a';
        $places = get_terms( 'shap_places', array(
            'hide_empty' => false,
            'fields' => 'all'
        ));

        return add_additional_fields_places($places);
    }

    $time_args = null;
    if($time){
        $time_args = array(
            'taxonomy' => 'shap_time',
            'field' => 'name', 
            'terms' => $time,
            'include_children' => true
        );
    }

    $place = $data->get_param( 'place' );
    $place_args = null;
    if($place){
        $place_args = array(
            'taxonomy' => 'shap_places',
            'field' => 'name', 
            'terms' => $place,
            'include_children' => true
        );
    }

    $fileclass = $data->get_param( 'fileclass' );
    $fileclass_args = null;
    if($fileclass){
        $fileclass_args = array(
            'key' => 'shap_fileclass',
            'value' => $fileclass
        );
    }

    $ids = get_posts(array(
        'post_type' => 'attachment',
        'post_status'    => 'inherit',
        'numberposts' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            'relation' => 'AND',
            $time_args,
            $place_args
        ),
        'meta_query' => array(
            $fileclass_args
        )
      ));

    $args = array('orderby' => 'name', 'order' => 'ASC'); //, 'fields' => 'names');
    $places =  wp_get_object_terms( $ids, 'shap_places', $args);

    return add_additional_fields_places($places);
}

function get_all_taxonomy_tags(){
    return get_terms( 'shap_tags', array(
        'hide_empty' => false,
    ));
}

function get_all_taxonomy_filter_terms(){
    $filters = new class{};
    $filters->time = get_terms( 'shap_time', array(
        'hide_empty' => false,
        'fields' => 'names'
    ));
    $filters->place = array();
    $filters->placetype = array();
    $filters->fileclass = array();

    $places = get_terms( 'shap_places', array(
        'hide_empty' => false,
        'fields' => 'all'
    ));

    foreach ( $places as $place ) {
        $place->meta = get_term_meta( $place->term_id );

        $filters->place[] = $place->name;
        if(array_key_exists('place_type',$place->meta) && $place->meta['place_type'][0] != null){
            $filters->placetype[] = $place->meta['place_type'][0];
        }
    }

    $posts = get_posts(array(
        'post_type' => 'attachment',
        'post_status'    => 'inherit',
        'numberposts' => -1,
        'fields' => 'ids'
      ));

    foreach ( $posts as $post ) {
        $meta = get_post_meta( $post );
        if(array_key_exists('shap_fileclass',$meta) && $meta['shap_fileclass'][0] != null){
            $filters->fileclass[] = $meta['shap_fileclass'][0];
        }
    }

    // remove duplicates
    $filters->time = array_values(array_unique($filters->time));
    $filters->place = array_values(array_unique($filters->place));
    $filters->placetype = array_values(array_unique($filters->placetype));
    $filters->fileclass = array_values(array_unique($filters->fileclass));

    return $filters;
}

function mcq_insert_snippet() {
	global $post;
	if(has_shortcode($post->post_content, "SyrianHeritageMap")){
		wp_enqueue_style( 'map-css', plugins_url('dist/css/app.css', __FILE__ ) );
		wp_enqueue_script( 'map-js-chunk', plugins_url('dist/js/chunk-vendors.js', __FILE__ ) );
        wp_enqueue_script( 'map-js', plugins_url('dist/js/app.js', __FILE__ ) );
        wp_enqueue_style( 'mapbox-css', 'https://api.mapbox.com/mapbox-gl-js/v1.2.0/mapbox-gl.css');
	}
}
add_action( 'wp_footer', 'mcq_insert_snippet' );

// Add Map shortcode
function Map_shortcode() {
    // get the locale string and pass it to javascript as global_local variable
    $current_lang = apply_filters( 'wpml_current_language', NULL );
    $global_js_variable = '<script>let global_locale="'.$current_lang.'"</script>';
    
    return $global_js_variable.'<div id=app></div>';
}
add_shortcode('SyrianHeritageMap', 'Map_shortcode');