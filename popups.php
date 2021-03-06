<?php

add_action('wp_footer', 'get_popups'); 

function get_popups(){
	// The Query
	$args = array(
		'post_type' => 'popups',
	);
	$pui_query = new WP_Query( $args );

	// The Loop
	if ( $pui_query->have_posts() ) {
		while ( $pui_query->have_posts() ) {
			$pui_query->the_post();
			$trigger_arr = get_post_meta(get_the_ID(), 'triggers', false);
			$trigger_type = get_post_meta(get_the_ID(), 'trigger_type', true);

			switch ($trigger_type) {
	            case "all":
	            	echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
							    <div class="popup-inner">
							      '. do_shortcode(get_the_content()) .'
							     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
							        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
							    </div>
							</div>';
					break;
				case "specific":
					if(in_array((string)get_queried_object_id(), $trigger_arr[0] ) ){
		            	echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
								    <div class="popup-inner">
								      '. do_shortcode(get_the_content()) .'
								     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
								        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
								    </div>
								</div>';
					}
					else if(in_array('all', $trigger_arr[0] )){
						echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
								    <div class="popup-inner">
								      '. do_shortcode(get_the_content()) .'
								     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
								        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
								    </div>
								</div>';
					}
					break;
				case "exitintent":
					//echo "TRIGGER TYPE!!" . $trigger_arr[0];
					if(in_array((string)get_queried_object_id(), $trigger_arr[0] ) ){
		            	echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
								    <div class="popup-inner">
								      '. do_shortcode(get_the_content()) .'
								     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
								        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
								    </div>
								</div>';
					}
					else if(in_array('all', $trigger_arr[0] )){
						echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
								    <div class="popup-inner">
								      '. do_shortcode(get_the_content()) .'
								     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
								        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
								    </div>
								</div>';
					}
					break;
				 case "click":
	            	echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
							    <div class="popup-inner">
							      '. do_shortcode(get_the_content()) .'
							     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
							        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
							    </div>
							</div>';
					break;
	            default: 
	            	echo '<div id="popup-'. get_the_id() . '" class="popup pui_popup" data-popup="popup-'. get_the_id() . '">
							    <div class="popup-inner">
							      '. do_shortcode(get_the_content()) .'
							     <p><a data-popup-close="popup-'. get_the_id() . '" href="#"></a></p>
							        <a class="popup-close" data-popup-close="popup-'. get_the_id() . '" href="#">x</a>
							    </div>
							</div>';
					break;
        	}
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// no posts found
	}
}