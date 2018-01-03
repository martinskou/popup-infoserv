<?php
/*
Plugin Name: Pop up by Infoserv
Plugin URI: http://www.infoserv.dk/
Description: Create pop up with content of your choice. Works well with Divi Builder.
Version: 1.0
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

function pui_enqueue_script() {   
    wp_register_script('cookie_js', plugin_dir_url( __FILE__ ) . 'js/js-cookie.js', array( 'jquery' ), mt_rand(10,1000), true );
    wp_enqueue_script('cookie_js');

    wp_register_script( 'pui_script_js', plugin_dir_url( __FILE__ ) . 'js/script.js' , array( 'jquery' ), mt_rand(10,1000) );
    wp_enqueue_script('pui_script_js');
    // The Query
    $args = array(
        'post_type' => 'popups',
    );
    
    $pui_query = new WP_Query( $args );
    $popups = array();

    while ( $pui_query->have_posts() ) : $pui_query->the_post();

   
        $popups[] = array(
                        "id" => get_the_ID(),
                        "popupExpire" => get_post_meta(get_the_ID(), 'expire_popup', true),
                        "popupDelay" => get_post_meta(get_the_ID(), 'delay_popup', true),
                        "triggers" => get_post_meta(get_the_ID(), 'triggers', false),
                        "triggerType" => get_post_meta(get_the_ID(), 'trigger_type', true),
                        "triggerSection" => get_post_meta(get_the_ID(), 'trigger_section', true),
                        "thisPageId" => (string)get_queried_object_id(),
                        );
    
    endwhile;
    wp_reset_postdata();

    $dataToBePassed = array(
        'popups' => $popups,

    );
    wp_localize_script( 'pui_script_js', 'php_vars', $dataToBePassed );

}


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





add_action( 'init', 'create_performances' );

function create_performances() {
    register_post_type( 'popups',
        array(
            'labels' => array(
                'name' => __('Pop ups', 'popup-by-infoserv'),
                'singular_name' => __('pop up', 'popup-by-infoserv'),
                'add_new' => __('Tilføj ny'),
                'add_new_item' => __('Tilføj ny pop up', 'popup-by-infoserv'),
                'edit' => __('Rediger', 'popup-by-infoserv'),
                'edit_item' => __('Rediger pop up', 'popup-by-infoserv'),
                'new_item' => __('Ny pop up', 'popup-by-infoserv'),
                'view' => __('Vis', 'popup-by-infoserv'),
                'view_item' => __('Vis pop up', 'popup-by-infoserv'),
                'search_items' => __('Søg i pop ups', 'popup-by-infoserv'),
                'not_found' => __('Ingen pop ups fundet', 'popup-by-infoserv'),
                'not_found_in_trash' => __('Ingen pop upd fundet i papirkurven.', 'popup-by-infoserv'),
                'parent' => __('Hoved pop up', 'popup-by-infoserv')
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'custom-fields' ),
            'taxonomies' => array(),
            'has_archive' => false,
            'rewrite' => array('slug' => 'pop-ups', 'with_front' => false)
        )
    );
};
 
function my_et_builder_post_types( $post_types ) {
    $post_types[] = 'popups';
     
    return $post_types;
}
add_filter( 'et_builder_post_types', 'my_et_builder_post_types' );


//metaboxes
function adding_custom_meta_boxes( $post_type, $post ) {
    add_meta_box( 
        'triggers',
        __( 'Triggere' ),
        'render_triggers_meta_box',
        'popups',
        'side',
        'low' 
    );
     add_meta_box( 
        'delay_popup',
        __( 'Antal sekunder før pop up' ),
        'render_delay_popup_meta_box',
        'popups',
        'side',
        'low'
    );
     add_meta_box( 
        'expire_popup',
        __( 'Antal dage før pop up vises igen' ),
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
        <label for="trigger_type" style=""><b>Vælg trigger</b></label></p>
        <select type="dropdown" name="trigger_type" id="trigger_type">
            <option value="all" '. ($trigger_type == "all" ? ' selected' : ' ') .'>Alle sider</option>
            <option value="specific" '. ($trigger_type == "specific" ? ' selected' : ' ') .'>Specifikke sider</option>
            <option value="section" '. ($trigger_type == "section" ? ' selected' : ' ') .'>Ved scroll til sektion</option>
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
                $html .= ($post->post_parent !== 0 ? '  (Forælder: '. get_the_title( $post->post_parent ) .')' : ''); 
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
        <label for="trigger_section" style=""><b>ID på sektion</b></label></p><p><i>(eksempelvis #kontaktformular)</i></p>
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
    $html .= '<p style="float:left;width:100%;"><label for="meta-checkbox-'. get_the_id() .'">Sæt regler op for, hvornår denne pop up skal vises.</label></p>';
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
        <label for="expire_popup" style="">Når brugeren lukker pop-up vinduet, sættes en cookie. Vælg her, hvor mange dage denne cookie skal gælde, før pop up vinduet vises for denne bruger igen.<br></label></p>
        <input type="number" name="expire_popup" id="expire_popup" value="'. $expire_popup .'" /><i>Standardværdi: 60</i>';
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
    $html = '<div class="prfx-row-content" style="width: 100%; height: 130px;">';
    $html .='<p style="float:left;">
        <label for="delay_popup" style="">Vælg hvor mange sekunder der skal gå, før pop up vises, efter siden er loadet.<br></label></p>
        <input type="number" name="delay_popup" id="delay_popup" value="'. $delay_popup .'" /><i>Standardværdi: 3</i>';
    $html .= '</div>';
    echo $html;
}

// Saves the custom meta input for 'icons' 
function pages_meta_save( $post_id ) {
        
        $trigger_type = "all";
        $trigger_section = "";
        $triggers = array();
        $expire_popup = "60";
        $delay_popup = "3";
        // Checks for input and sanitizes/saves if needed
        if( isset( $_POST[ 'triggers' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $triggers = $_POST[ 'triggers' ];
            
        }
        if( isset( $_POST[ 'trigger_type' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $trigger_type = $_POST[ 'trigger_type' ];
            
        }
        if( isset( $_POST[ 'trigger_section' ]) && wp_verify_nonce($_POST['wp_triggers_nonce'], plugin_basename(__FILE__)) ) {
            $trigger_section = $_POST[ 'trigger_section' ];
            
        }

        if( isset( $_POST[ 'expire_popup' ]) && wp_verify_nonce($_POST['wp_expire_popup_nonce'], plugin_basename(__FILE__)) ) {
            $expire_popup = $_POST[ 'expire_popup' ];
            
        }
        if( isset( $_POST[ 'delay_popup' ]) && wp_verify_nonce($_POST['wp_delay_popup_nonce'], plugin_basename(__FILE__)) ) {
            $delay_popup = $_POST[ 'delay_popup' ];
            
        }
      
        update_post_meta( $post_id, 'triggers', $triggers );

        update_post_meta( $post_id, 'trigger_type', $trigger_type );

        update_post_meta( $post_id, 'trigger_section', $trigger_section );

        update_post_meta( $post_id, 'expire_popup', $expire_popup );

        update_post_meta( $post_id, 'delay_popup', $delay_popup );
 
}
add_action( 'save_post', 'pages_meta_save' );

//automatic updater via git
if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
        $config = array(
            'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
            'proper_folder_name' => 'popup-infoserv', // this is the name of the folder your plugin lives in
            'api_url' => 'https://github.com/martinskou/popup-infoserv', // the GitHub API url of your GitHub repo
            'raw_url' => 'https://raw.githubusercontent.com/martinskou/popup-infoserv/master/init.php?token=AOuBVDMn0N5W_4tw_DXaBKh1V3hhvjUxks5aVczdwA%3D%3D', // the GitHub raw url of your GitHub repo
            'github_url' => 'https://github.com/martinskou/popup-infoserv', // the GitHub url of your GitHub repo
            'zip_url' => 'https://github.com/username/repository-name/zipball/master', // the zip url of the GitHub repo
            'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
            'requires' => '3.0', // which version of WordPress does your plugin require?
            'tested' => '3.3', // which version of WordPress is your plugin tested up to?
            'readme' => 'README.md', // which file to use as the readme for the version number
            'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
        );
        new WP_GitHub_Updater($config);
    }