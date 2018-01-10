<?php
/*
Plugin Name: Pop up by Infoserv
Plugin URI: http://www.infoserv.dk/
Description: Create pop up with content of your choice. Works well with Divi Builder.
Version: 1.2.0
Author: Jesper Hellner Sørensen
Author URI: http://www.infoserv.dk/

* Text Domain: popup-by-infoserv
* Domain Path: /lang/
*
* @package WordPress
* @author Jesper Hellner Sørensen
* @since 1.0.0
*
*/
if (!defined('ABSPATH')) exit;
include( plugin_dir_path( __FILE__ ) . 'popups.php');



//check if any updates on github
require plugin_dir_path( __FILE__ ) . 'assets/plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/martinskou/popup-infoserv/',
    __FILE__,
    'popup-infoserv'
);

//Optional: If you're using a private repository, specify the access token like this:
//$myUpdateChecker->setAuthentication('your-token-here');

//Optional: Set the branch that contains the stable release.
//$myUpdateChecker->setBranch('stable-branch-name');


//ready for translation
add_action('plugins_loaded', 'pui_load_textdomain');

function pui_load_textdomain() {
    load_plugin_textdomain( 'popup-by-infoserv', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

add_action( 'wp_enqueue_scripts', 'pui_load_plugin_css' );

function pui_load_plugin_css() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_register_style( 'pui_css',  plugin_dir_url( __FILE__ ) . 'css/style.css' ,array(),rand());
    wp_enqueue_style( 'pui_css' );

}


add_action('wp_enqueue_scripts', 'pui_enqueue_script');

//adding scripts and localizing them with WP data
function pui_enqueue_script() {   
    
    //registering cookie jquery library
    wp_register_script('cookie_js', plugin_dir_url( __FILE__ ) . 'assets/js-cookie.js', array( 'jquery' ), "", true );
    wp_enqueue_script('cookie_js');

    //registering plugin script
    wp_register_script( 'pui_exit_intent_js', plugin_dir_url( __FILE__ ) . 'assets/jquery.exitintent.min.js' , array( 'jquery' ), "", true );
    wp_enqueue_script('pui_exit_intent_js');

    //registering plugin script
    wp_register_script( 'pui_script_js', plugin_dir_url( __FILE__ ) . 'js/script.js' , array( 'jquery' ), mt_rand(10,1000) );
    wp_enqueue_script('pui_script_js');
    // The Query
    $args = array(
        'post_type' => 'popups',
    );
    
    $pui_query = new WP_Query( $args );
    $popups = array();

    $protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';

    while ( $pui_query->have_posts() ) : $pui_query->the_post();
        $trigger_arr = get_post_meta(get_the_ID(), 'triggers', false);
        $trigger_type = get_post_meta(get_the_ID(), 'trigger_type', true);
        
        $default_val = array("popupId" => get_the_ID(),
                             "popupExpire" => get_post_meta(get_the_ID(), 'expire_popup', true),
                             "triggerType" => get_post_meta(get_the_ID(), 'trigger_type', true),
                             "impressions" => get_post_meta(get_the_ID(), 'impressions', true),
                             "thisPageId" => (string)get_queried_object_id(),
                             "ajaxurl" => admin_url("admin-ajax.php", $protocol),
                             "ajax_nonce" => wp_create_nonce("stats_nonce"));

        switch ($trigger_type) {
            case "all":
                array_push($popups, array_merge($default_val, array("popupDelay" => get_post_meta(get_the_ID(), 'delay_popup', true))));
                break;
            case "specific":
                array_push($popups, array_merge($default_val, array("popupDelay" => get_post_meta(get_the_ID(), 'delay_popup', true),
                                                                    "triggers" => get_post_meta(get_the_ID(), 'triggers', false))));
                break;
            case "section":
                array_push($popups, array_merge($default_val, array("triggerSection" => get_post_meta(get_the_ID(), 'trigger_section', true))));
                break;
            case "exitintent":
                array_push($popups, array_merge($default_val, array("popupDelay" => get_post_meta(get_the_ID(), 'delay_popup', true))));
                break;
        }
    
    endwhile;
    wp_reset_postdata();

    $dataToBePassed = array(
        'popups' => $popups,

    );
    wp_localize_script( 'pui_script_js', 'php_vars', $dataToBePassed );

}

//script for admin editing of popup post type
add_action('admin_enqueue_scripts', 'pui_admin_enqueue_script');

function pui_admin_enqueue_script( $hook_suffix ){
    $cpt = 'popups';

    //if( in_array($hook_suffix, array('post.php', 'post-new.php') ) ){
        $screen = get_current_screen();

       // if( is_object( $screen ) && $cpt == $screen->post_type ){
            wp_register_script( 'pui_admin_script_js', plugin_dir_url( __FILE__ ) . 'js/admin.js' , array( 'jquery' ),mt_rand(10,1000) );
            wp_enqueue_script('pui_admin_script_js');

       // }
    //}
}

//creating the popup posttype
add_action( 'init', 'create_popup_posttype' );

function create_popup_posttype() {
    register_post_type( 'popups',
        array(
            'labels' => array(
                'name' => __('Pop-ups', 'popup-by-infoserv'),
                'singular_name' => __('pop-up', 'popup-by-infoserv'),
                'add_new' => __('Tilføj ny'),
                'add_new_item' => __('Tilføj ny pop-up', 'popup-by-infoserv'),
                'edit' => __('Rediger', 'popup-by-infoserv'),
                'edit_item' => __('Rediger pop-up', 'popup-by-infoserv'),
                'new_item' => __('Ny pop-up', 'popup-by-infoserv'),
                'view' => __('Vis', 'popup-by-infoserv'),
                'view_item' => __('Vis pop up', 'popup-by-infoserv'),
                'search_items' => __('Søg i pop-ups', 'popup-by-infoserv'),
                'not_found' => __('Ingen pop-ups fundet', 'popup-by-infoserv'),
                'not_found_in_trash' => __('Ingen pop upd fundet i papirkurven.', 'popup-by-infoserv'),
                'parent' => __('Hoved pop-up', 'popup-by-infoserv')
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'custom-fields' ),
            'taxonomies' => array(),
            'has_archive' => false,
            'publicly_queryable'  => false,
            'show_in_admin_bar'   => false,
            'rewrite' => array('slug' => 'pop-ups', 'with_front' => false)
        )
    );
};

//enabling the Divi Builder if activated
function my_et_builder_post_types( $post_types ) {
    $post_types[] = 'popups';
     
    return $post_types;
}
add_filter( 'et_builder_post_types', 'my_et_builder_post_types' );


//metaboxes
function adding_custom_meta_boxes( $post_type, $post ) {
    add_meta_box( 
        'triggers',
        __( 'Triggere', 'popup-by-infoserv' ),
        'render_triggers_meta_box',
        'popups',
        'side',
        'low' 
    );
     add_meta_box( 
        'delay_popup',
        __( 'Antal sekunder før pop up' , 'popup-by-infoserv'),
        'render_delay_popup_meta_box',
        'popups',
        'side',
        'low'
    );
     add_meta_box( 
        'expire_popup',
        __( 'Antal dage før pop up vises igen', 'popup-by-infoserv' ),
        'render_expire_popup_meta_box',
        'popups',
        'side',
        'low'
    );
    
}
add_action( 'add_meta_boxes', 'adding_custom_meta_boxes', 10, 2 );


function get_post_pages($post){
    
   $args = array(
    'posts_per_page'   => 200,
    'offset'           => 0,
    'category'         => '',
    'category_name'    => '',
    'orderby'          => 'post_type post_parent title',
    'order'            => 'ASC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => array('page', 'post'),
    'post_mime_type'   => '',
    'post_parent'      => '',
    'author'       => '',
    'author_name'      => '',
    'post_status'      => 'publish',
    'suppress_filters' => true 
    );
    $posts_array = new WP_Query( $args );

    return $posts_array;
}

function get_trigger_type_input($trigger_type){
    $html ='<p style="float:left;width:100%;">
        <label for="trigger_type" style=""><b>'. __('Vælg trigger', 'popup-by-infoserv') .'</b></label></p>
        <select type="dropdown" name="trigger_type" id="trigger_type">
            <option value="all" '. ($trigger_type == "all" ? ' selected' : ' ') .'>'. __('Alle sider', 'popup-by-infoserv') .'</option>
            <option value="specific" '. ($trigger_type == "specific" ? ' selected' : ' ') .'>'. __('Specifikke sider', 'popup-by-infoserv') .'</option>
            <option value="section" '. ($trigger_type == "section" ? ' selected' : ' ') .'>'. __('Ved scroll til sektion' , 'popup-by-infoserv') .'</option>
            <option value="exitintent" '. ($trigger_type == "exitintent" ? ' selected' : ' ') .'>'. __('På vej væk fra siden' , 'popup-by-infoserv') .'</option>
            </select>';
    return $html;
}

function get_trigger_pages($post,$posts_array,$pages_stored_meta,$trigger_type){
    $html = '<div id="triggers_container" style="'. ($trigger_type == 'specific' ? ' ' : 'display:none;') .' width: 100%; height: 300px;overflow: auto;margin-bottom: 20px;">';

            while ( $posts_array->have_posts() ) : $posts_array->the_post();
                global $post;
                $html .='<p>
                    <label for="meta-checkbox-'. get_the_id() .'">
                        <input type="checkbox" name="triggers[]" id="meta-checkbox-'. get_the_id() .'" value="'. get_the_id() .'" '. (in_array(get_the_id(), $pages_stored_meta[0]) ? ' checked' : ' ') .'  />
                        <strong>'. get_the_title() .'</strong>';
                $html .= ($post->post_parent !== 0 ? ' ' . __('(Forælder:', 'popup-by-infoserv') . get_the_title( $post->post_parent ) .')' : ''); 
                $html .= ' (' . get_post_type(get_the_id()) . ')';
                $html .= '</label>
                </p>';
            endwhile;
            wp_reset_postdata();
    $html .= '</div>';
    return $html;
}
function get_trigger_section_input($post,$trigger_section,$trigger_type){
    $html ='<div id="trigger_section_container" style="'. ($trigger_type == 'section' ? ' ' : 'display:none;') .'"><p style="float:left;width:100%;">
        <label for="trigger_section" style=""><b>'. __('ID på sektion', 'popup-by-infoserv') .'</b></label></p><p><i>(eksempelvis #kontaktformular)</i></p>
        <input type="text" id="trigger_section" name="trigger_section" value="'. $trigger_section .'"></div>';
    return $html;
}

function render_triggers_meta_box($post) {
    
    wp_nonce_field(plugin_basename(__FILE__), 'wp_triggers_nonce');

    $posts_array = get_post_pages($post);

    if(!empty(get_post_meta( $post->ID, 'triggers' ))){
        $pages_stored_meta = get_post_meta( $post->ID, 'triggers' );
    }
    else{
        $pages_stored_meta = array(array());
    }
    if(!empty(get_post_meta( $post->ID, 'trigger_type', true ))){
        $trigger_type = get_post_meta( $post->ID, 'trigger_type', true );
    }
    else{
        $trigger_type = "all";
    }
    if(!empty(get_post_meta( $post->ID, 'trigger_section', true ))){
        $trigger_section = get_post_meta( $post->ID, 'trigger_section', true );
    }
    else{
        $trigger_section = "";
    }
    //echo $trigger_type;

    $html = '<div class="prfx-row-content" style="width: 100%; ">';
    $html .= '<p style="float:left;width:100%;"><label for="meta-checkbox-'. get_the_id() .'">'. __('Sæt regler op for, hvornår denne pop up skal vises.', 'popup-by-infoserv') .'</label></p>';
    $html .= get_trigger_type_input($trigger_type);
    $html .= get_trigger_pages($post,$posts_array,$pages_stored_meta,$trigger_type);
    $html .= get_trigger_section_input($post,$trigger_section,$trigger_type);
    $html .= '</div>';
    echo $html;
}

function render_expire_popup_meta_box($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'wp_expire_popup_nonce');
    
    if(!empty(get_post_meta( $post->ID, 'expire_popup' ))){
        $expire_popup = get_post_meta( $post->ID, 'expire_popup', true );
    }
    else{
        $expire_popup = "60";
    }
    //print_r($icons );
    $html = '<div class="prfx-row-content" style="width: 100%; height: 160px;">';
    $html .='<p style="float:left;">
        <label for="expire_popup" style="">'. __('Når brugeren lukker pop-up vinduet, sættes en cookie. Vælg her, hvor mange dage denne cookie skal gælde, før pop up vinduet vises for samme bruger igen.', 'popup-by-infoserv') .'<br></label></p>
        <input type="number" name="expire_popup" id="expire_popup" value="'. $expire_popup .'" /><div><i>Standard: 60</i></div>';
    $html .= '</div>';
    echo $html;
}

function render_delay_popup_meta_box($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'wp_delay_popup_nonce');
  
    if(!empty(get_post_meta( $post->ID, 'delay_popup' ))){
        $delay_popup = get_post_meta( $post->ID, 'delay_popup', true );
    }
    else{
        $delay_popup = "3";
    }
    //print_r($icons );
    $html = '<div class="prfx-row-content" style="width: 100%; height: 180px;">';
    $html .='<p style="float:left;">
        <label for="delay_popup" style="">'. __('Vælg hvor mange sekunder der skal gå, før pop up vises, efter siden er loadet. Denne indstilling bliver ignoreret ved følgende triggers: "Scroll til sektion" og "På vej væk fra siden."', 'popup-by-infoserv') .'<br></label></p>
        <input type="number" name="delay_popup" id="delay_popup" value="'. $delay_popup .'" /><div><i>Standard: 3</i></div>';
    $html .= '</div>';
    echo $html;
}

// Saves the custom meta inputs 
function pages_meta_save( $post_id ) {
        
        $trigger_type = "all";
        $trigger_section = "";
        $triggers = array();
        $expire_popup = "60";
        $delay_popup = "3";

        // If this isn't a 'popups' post, don't update it.
        $post_type = get_post_type($post_id);
        if ( "popups" != $post_type ) return;
        
        // Checks for input and sanitizes/saves if needed
        if( isset( $_POST[ 'triggers' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $triggers = $_POST[ 'triggers' ];
            
        }
        if( isset( $_POST[ 'trigger_type' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $trigger_type = sanitize_text_field($_POST[ 'trigger_type' ]);
            
        }
        if( isset( $_POST[ 'trigger_section' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $trigger_section = sanitize_text_field($_POST[ 'trigger_section' ]);
            
        }

        if( isset( $_POST[ 'expire_popup' ]) && wp_verify_nonce($_POST['wp_expire_popup_nonce'], plugin_basename(__FILE__)) ) {
            $expire_popup = sanitize_text_field($_POST[ 'expire_popup' ]);
            
        }
        if( isset( $_POST[ 'delay_popup' ]) && wp_verify_nonce($_POST['wp_delay_popup_nonce'], plugin_basename(__FILE__)) ) {
            $delay_popup = sanitize_text_field($_POST[ 'delay_popup' ]);
            
        }
        $meta = array(
                    array('name' => 'triggers', 'value' => $triggers),
                    array('name' => 'trigger_type', 'value' => $trigger_type),
                    array('name' => 'trigger_section', 'value' => $trigger_section ),
                    array('name' => 'expire_popup', 'value' => $expire_popup ),
                    array('name' => 'delay_popup', 'value' => $delay_popup )
                    );
        
        foreach($meta as $mt){
            update_post_meta( $post_id, $mt['name'], $mt['value']);
        }
}
add_action( 'save_post', 'pages_meta_save' );

//updating statistics on pop up

add_action( 'wp_ajax_pui_stats_action', 'pui_stats_action' );
add_action( 'wp_ajax_nopriv_pui_stats_action', 'pui_stats_action' ); // This lines it's because we are using AJAX on the FrontEnd.

function pui_stats_action(){
    check_ajax_referer( 'stats_nonce', 'security' );
    $fieldname = "impressions"; // This variable will get the POST 'fieldname'
    $fieldvalue = $_POST['fieldvalue'];  // This variable will get the POST 'fieldvalue'
    $postid = $_POST['postid'];             // This variable will get the POST 'postid'

    $current_user = wp_get_current_user();
    
    //only updating stats count if admin is not logged in
    if(!user_can( $current_user, 'administrator' )){
        update_post_meta($postid, $fieldname, $fieldvalue); // We will update the field.
    }
    
    wp_die(); // this is required to terminate immediately and return a proper response
};


//adding statistics to list view in admin
add_filter('manage_popups_posts_columns', 'bs_popup_table_head');
function bs_popup_table_head( $defaults ) {
    $defaults['impressions'] = __('Visninger', 'popup-by-infoserv');
    return $defaults;
}

add_action( 'manage_popups_posts_custom_column', 'bs_popup_table_content', 10, 2 );

function bs_popup_table_content( $column_name, $post_id ) {
    if ($column_name == 'impressions') {
    echo get_post_meta( $post_id, 'impressions', true );
    }

};

add_filter( 'manage_edit-popups_sortable_columns', 'bs_popup_table_sorting' );
function bs_popup_table_sorting( $columns ) {
  $columns['impressions'] = 'impressions';
  return $columns;
};

add_filter( 'request', 'bs_popup_id_column_orderby' );
function bs_popup_id_column_orderby( $vars ) {
    if ( isset( $vars['orderby'] ) && 'impressions' == $vars['orderby'] ) {
        $vars = array_merge( $vars, array(
            'meta_key' => 'impressions',
            'orderby' => 'meta_value_num'
        ) );
    }

    return $vars;
};