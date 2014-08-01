<?php
/**
 * Plugin Name: Check Empty Pages
 * Plugin URI: http://stboston.com
 * Description: Check Empty Pages
 * Version: 0.1
 * Author: Brian Hanna
 * Author URI: http://stbsoton.com
 * License: GPL2
 */

function cep_register_menu(){
	add_management_page('Check Empty Pages','Check Empty Pages','manage_options','check_empty_pages','check_empty_pages_tools_page');
}

add_action('admin_menu', 'cep_register_menu');

/*function cep_add_scripts(){
	// embed the javascript file that makes the AJAX request
	wp_enqueue_script( 'cep-request', plugin_dir_url( __FILE__ ) . 'js/ajax.js', array( 'jquery' ) );
	 
	// declare the URL to the file that handles the AJAX request (wp-admin/admin-ajax.php)
	wp_localize_script( 'cep-request', 'CEPAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}*/

function cms_is_in_menu( $menu = null, $object_id = null ) {

    // get menu object
    $menu_object = wp_get_nav_menu_items( esc_attr( $menu ) );

    // stop if there isn't a menu
    if( ! $menu_object )
        return false;

    // get the object_id field out of the menu object
    $menu_items = wp_list_pluck( $menu_object, 'object_id' );

    // use the current post if object_id is not specified
    if( !$object_id ) {
        global $post;
        $object_id = get_queried_object_id();
    }

    // test if the specified page is in the menu or not. return true or false.
    return in_array( (int) $object_id, $menu_items );

}

function check_empty_pages_tools_page() {
?> 
<div class="wrap">
	<h2>Check Empty Pages</h2>
	<p>The following pages are missing content or have improper content on the page. Refresh the page to update the list.</p>
	<table class="widefat cep-style">
	<thead><tr><td>Page Name</td><td>Options</td></tr></thead>
	<tbody>
		<?php
			//Get the right pages
			$args = array(
						'post_type' 		=> 'page',
						'post_status'		=> 'publish',
						'posts_per_page'	=> -1,
						'order_by'			=> 'menu_order',
						'meta_query'		=> array(
													array(
														'key'		=> '_links_to',
														'value'		=> '',
														'compare'	=> 'NOT EXISTS'
													)
												)
						);
			$pages = get_posts($args);
			//Start the loop
			foreach ($pages as $page) {
				//Check if there's missing or improper content on the page
				$content = $page->post_content;
				if ((! $content) || (strpos($content,'Click here to add your own text'))){
					//Get page ancestors
					$ancestors = get_ancestors($page->ID, 'page');
					echo '<tr><td>';
					//Reverse the ancestor array
					asort($ancestors);
					//Print out ancestors
					foreach ($ancestors as $key => $val) {
						echo get_page($val)->post_title.' > ';
					}
					//Check if the page is in menu and print out the table entry, with options
					echo get_the_title($page->ID).(cms_is_in_menu('TRSD Header Menu',$page->ID) ? '' : ' <span style="color:red;">Not In Menu</span>') .'</td><td><a href="'.get_permalink($page->ID).'" target="_blank">View</a> | <a href="'. get_bloginfo('url','display').'/wp-admin/post.php?post='. $page->ID.'&action=edit" target="_blank">Edit</a></td></tr>';
				}
			}
		?>
	</tbody>
	</table>
</div>
<?
}