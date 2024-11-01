<?php

/*
Plugin Name: TIEexpire
Plugin URI: http://www.setupmyvps.com/tieexpire/
Description: Simple post expiration plugin. Expires posts based on multiple criteria.
Version: 1.1
Author: TIEro
Author URI: http://www.setupmyvps.com
License: GPL2
*/


// Register the hooks for plugin activation and deactivation.
register_activation_hook(__FILE__, 'do_activation');
register_deactivation_hook(__FILE__, 'do_deactivation');

// Add actions to define scheduled job and place settings menu on the Dashboard.
add_action('my_expiry_job', 'do_TIEexpiry_all');
add_action('admin_menu', 'TIEexpire_settings_page');

// On plugin activation, schedule the hourly expiry job.
function do_activation() {
	if( !wp_next_scheduled( 'my_expiry_job' ) ) {
		wp_schedule_event( current_time ( 'timestamp' ), 'hourly', 'my_expiry_job' ); 
	}
	add_option('TIEexpire_pub', 'publish');
	add_option('TIEexpire_catsradio', 'include');
	add_option('TIEtools_notify_poster','off');
	add_option('TIEtools_notify_admin','off');
	add_option('TIEtools_notify_other','off');
}

// On plugin deactivation, remove the scheduled job. Note that the {prefix}_wti_totals view remains at present.
function do_deactivation() {
	// Remove scheduled expiry job
	wp_clear_scheduled_hook( 'my_expiry_job' );
}

// Define the Settings page function for options.
function TIEexpire_settings_page() {
  add_menu_page('Post Expiry', 'Post Expiry', 'administrator', 'TIEexpire_settings', 'TIExpire_option_settings');
}

// This is the scheduled job that runs every hour. You can change the order if it suits your purposes to do so.
function do_TIEexpiry_all() {
	global $statuslist;
	global $notify_is;
	$pubposts = (get_option('TIEexpire_pub') == 'publish') ? 'publish' : '' ;
	$draftposts = (get_option('TIEexpire_draft') == 'draft') ? 'draft' : '' ;
	$pendingposts = (get_option('TIEexpire_pending') == 'pending') ? 'pending' : '' ;
	$privateposts = (get_option('TIEexpire_private') == 'private') ? 'private' : '' ;
	$notify_poster = (get_option('TIEtools_notify_poster') == 'on') ? 'on' : '' ;
	$notify_admin = (get_option('TIEtools_notify_admin') == 'on') ? 'on' : '' ;
	$notify_other = (get_option('TIEtools_notify_other') == 'on') ? 'on' : '' ;
	$notify_email = (get_option('TIEtools_notify_email') != '') ? get_option('TIEtools_notify_email') : '';

	if ($notify_poster = 'on' || $notify_admin = 'on' || ($notify_other = 'on' && $notify_email != '')) {
		$notify_is = 'on'; }
	else {
		$notify_is = 'off';
	}
	
	if ($pubposts == 'publish') {
		$statuslist = "'publish'";
		if ($draftposts == 'draft') {
			$statuslist .= ",'draft'";
		}
		if ($pendingposts == 'pending') {
			$statuslist .= ",'pending'";
		}
		if ($privateposts == 'private') {
			$statuslist .= ",'private'";
		}
	}
	elseif ($draftposts == 'draft') {
		$statuslist = "'draft'";
		if ($pendingposts == 'pending') {
			$statuslist .= ",'pending'";
		}
		if ($privateposts == 'private') {
			$statuslist .= ",'private'";
		}
	}
	elseif ($pendingposts == 'pending') {
		$statuslist .= "'pending'";
		if ($privateposts == 'private') {
			$statuslist .= ",'private'";
		}
	}
	elseif ($privateposts == 'private') {
		$statuslist = "'private'";
	}
	else {
	$statuslist = '';
	}
	expirebydays();
	expirebyposts();
	expirebyviews();
	expireunlikedposts();
}

// Code for the options page on the Dashboard.
function TIExpire_option_settings() {

	// Get all the user-defined options for all expiry types. Default to zero or blank if they don't exist.
	$pubposts = (get_option('TIEexpire_pub') == 'publish') ? 'checked' : '' ;
	$draftposts = (get_option('TIEexpire_draft') == 'draft') ? 'checked' : '' ;
	$pendingposts = (get_option('TIEexpire_pending') == 'pending') ? 'checked' : '' ;
	$privateposts = (get_option('TIEexpire_private') == 'private') ? 'checked' : '' ;
	$catstoinclude = (get_option('TIEexpire_catsin') != '') ? get_option('TIEexpire_catsin') : '0';
	$catstoexclude = (get_option('TIEexpire_catsout') != '') ? get_option('TIEexpire_catsout') : '0';
	$catsincludeon = (get_option('TIEexpire_catsradio') == 'include') ? 'checked' : '' ;
	$catsexcludeon = (get_option('TIEexpire_catsradio') == 'exclude') ? 'checked' : '' ;
	$catsindays = (get_option('TIEexpire_catsdays') == 'on') ? 'checked' : '' ;
	$catsinposts = (get_option('TIEexpire_catsposts') == 'on') ? 'checked' : '' ;
	$catsinviews = (get_option('TIEexpire_catsviews') == 'on') ? 'checked' : '' ;
	$catsinlikes = (get_option('TIEexpire_catslikes') == 'on') ? 'checked' : '' ;
    $numberofdays = (get_option('TIEexpire_days') != '') ? get_option('TIEexpire_days') : '0';
	$numberofposts = (get_option('TIEexpire_posts') != '') ? get_option('TIEexpire_posts') : '0';
	$numberofviewdays = (get_option('TIEexpire_viewdays') != '') ? get_option('TIEexpire_viewdays') : '0';
	$numberofviews = (get_option('TIEexpire_views') != '') ? get_option('TIEexpire_views') : '0';
	$numberoflikedays = (get_option('TIEexpire_likedays') != '') ? get_option('TIEexpire_likedays') : '0';
	$numberoflikes = (get_option('TIEexpire_likes') != '') ? get_option('TIEexpire_likes') : '0';
	$notify_poster = (get_option('TIEtools_notify_poster') == 'on') ? 'checked' : '' ;
	$notify_admin = (get_option('TIEtools_notify_admin') == 'on') ? 'checked' : '' ;
	$notify_other = (get_option('TIEtools_notify_other') == 'on') ? 'checked' : '' ;
	$notify_email = (get_option('TIEtools_notify_email') != '') ? get_option('TIEtools_notify_email') : '';
	
	// The header section line of the options page, with the logo and basic info. And the donation bit. :)
	$plugname = '</pre><div class="wrap">
				 <h2><img src="' . plugins_url( 'expire.png' , __FILE__ ) . '" border=0 alt="Post Expiry Settings" style="vertical-align:middle"> Post Expiry Settings</h2>';
	$topline = '<p style="max-width:60%">All settings are cumulative and are determined separately, so you can build your expiry plan by stacking options. The plugin works down through the different expiry types in the order they are displayed. A zero value in a field switches off that check.
				<p style="max-width:60%"><strong>This plugin is now available as part of the free <a href="http://wordpress.org/plugins/tietools-automatic-maintenance-kit" target="_blank">TIEtools</a> plugin, which also includes duplicate post control and server log file removal.</strong>
				<p style="max-width:60%">If you like this plugin and use it on your site(s), please show your appreciation by <a href="http://wordpress.org/plugins/tieexpire-automated-post-expiry/" target="_blank">rating it at WordPress</a> or even throwing some pennies my way!<p>
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
				<input type="hidden" name="cmd" value="_s-xclick">
				<input type="image" src="' . plugins_url( 'donate.png' , __FILE__ ) . '" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
				<img alt="" border="0" src="https://www.paypalobjects.com/en_GB/i/scr/pixel.gif" width="1" height="1">
				<input type="hidden" name="hosted_button_id" value="ESL342R25YKLL">
				</form>' ;
		   
	// The HTML for the options page. Age and Post Limit are always available, so they're bunched together in one block.
    $html= '<hr width=60% align="left">
			<form action="options.php" method="post" name="options">
			' . wp_nonce_field('update-options') . '
			<p><h3>Post status filters</h3>
			<p style="max-width:60%">Select post status you wish to include in all expiry checks. Unmarking all boxes effectively switches off all post expiry.
			<p><input type="checkbox" name="TIEexpire_pub" value="publish"' . $pubposts . ' />&nbsp;<label>Published</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="TIEexpire_draft" value="draft"' . $draftposts . ' />&nbsp;<label>Draft</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="TIEexpire_pending" value="pending"' . $pendingposts . ' />&nbsp;<label>Pending</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="TIEexpire_private" value="private"' . $privateposts . ' />&nbsp;<label>Private</label>
			<br>&nbsp;
			<hr width=60% align="left">
			<p><h3>Category filters</h3>
			<p style="max-width:60%">Select the filter you wish to use and enter the categories as a comma-delimited list of numbers. For example, to expire posts in categories 1-3, click the radio button and enter "1,2,3" in the first box (without quotes). Enter a zero category to switch off the filter. Note that sub-categories are treated the same as top-level items and must be listed individually.
			<p><input type="radio" name="TIEexpire_catsradio" id="include" value="include"' . $catsincludeon . ' />&nbsp;<label>Categories to include in expiry checks: </label><input type="text" name="TIEexpire_catsin" size=10 value="' . $catstoinclude . '" />
			<br><input type="radio" name="TIEexpire_catsradio" id="exclude" value="exclude"' . $catsexcludeon . ' />&nbsp;<label>Categories to exclude from expiry checks: </label><input type="text" name="TIEexpire_catsout" size=10 value="' . $catstoexclude . '" />
			<br>&nbsp;
			<hr width=60% align="left">
			<p><h3>Expiry notification</h3>
			<p style="max-width:60%">Select the people you wish to notify of expired entries. An email will be sent to each person for each post affected.
			<p><input type="checkbox" name="TIEtools_notify_poster" value="on"' . $notify_poster . ' />&nbsp;<label>Post author</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="TIEtools_notify_admin" value="on"' . $notify_admin . ' />&nbsp;<label>Site admin</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="TIEtools_notify_other" value="on"' . $notify_other . ' />&nbsp;<label>Someone else</label>
			&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" name="TIEtools_notify_email" size=20 value="' . $notify_email . '" />&nbsp;<label>(enter email)</label>
			<br>&nbsp;
			<hr width=60% align="left">
			<p><h3>Expiry by age</h3>
			<p><label>Move all selected posts more than </label><input type="text" name="TIEexpire_days" size=5 value="' . $numberofdays . '" /> days old to the trash.
			<p><input type="checkbox" name="TIEexpire_catsdays" value="on"' . $catsindays . ' />&nbsp;<label>Use category filters</label>
			<br>&nbsp;
			<hr width=60% align="left">
			<p><h3>Expiry post limit</h3>
			<p><label>Keep the most recent </label><input type="text" name="TIEexpire_posts" size=8 value="' . $numberofposts . '" /> selected posts and move all others to the trash.
			<p><input type="checkbox" name="TIEexpire_catsposts" value="on"' . $catsinposts . ' />&nbsp;<label>Use category filters</label>';
			
	// Check whether BAW Post Views Count is installed by detecting the bawpv.php file and, if so, show the options.
	if (is_plugin_active('baw-post-views-count/bawpv.php')) {

		$html .= '<br>&nbsp;
				  <hr width=60% align="left">
				  <p><h3>Expiry by number of views</h3>
				  <p><label>Move all selected posts over </label><input type="text" name="TIEexpire_viewdays" size=5 value="' . $numberofviewdays . '" /> days old
				  <br><label>and with fewer than </label><input type="text" name="TIEexpire_views" size=8 value="' . $numberofviews . '" /> total views to the trash.
				  <p><input type="checkbox" name="TIEexpire_catsviews" value="on" ' . $catsinviews . '/>&nbsp;<label>Use category filters</label>' ;
	}

	// Check whether WTI Like Post is installed by detecting the wti_like_post.php file and, if so, show the options.
	if (is_plugin_active('wti-like-post/wti_like_post.php')) {

		$html .= '<br>&nbsp;
				  <hr width=60% align="left">
				  <p><h3>Expiry by number of likes</h3>
				  <p><label>Move all selected posts over </label><input type="text" name="TIEexpire_likedays" size=5 value="' . $numberoflikedays . '" /> days old
				  <br><label>and with fewer than </label><input type="text" name="TIEexpire_likes" size=8 value="' . $numberoflikes . '" /> total likes to the trash.
				  <p><input type="checkbox" name="TIEexpire_catslikes" value="on" ' . $catsinlikes . '/>&nbsp;<label>Use category filters</label>' ;
	}

	// Finish the HTML block by adding the update button and the standard hidden WP fields to store the user's options.
	$html.='<hr width=60% align="left">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="TIEexpire_days, TIEexpire_posts, TIEexpire_viewdays, 
															TIEexpire_views, TIEexpire_likedays, TIEexpire_likes, 
															TIEexpire_catsin, TIEexpire_catsout, TIEexpire_catsdays, 
															TIEexpire_catsposts, TIEexpire_catsviews, TIEexpire_catslikes, 
															TIEexpire_catsradio, TIEexpire_pub, TIEexpire_draft, 
															TIEexpire_pending, TIEexpire_private, TIEtools_notify_poster, 
															TIEtools_notify_admin, TIEtools_notify_other, TIEtools_notify_email, 
															TIEexpire_notify_text" />
			<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></form></div>
			<div style="clear:both">
			<pre>
		   ';

	// Display the topline and page HTML. The IF part shows the "Settings saved" line when appropriate.
	echo $plugname;
	if( isset($_GET['settings-updated']) ) {
		echo '<p style="max-width:60%;background-color:#FFFFE0;border-color:#e6db55;border-style:solid;border-width:1px;padding:3px;line-height:200%;">Post expiration settings saved.' ;
	}
	echo $topline;
	echo $html;

}

// Expire posts by age in days.
function expirebydays() {

	// Get the user-defined number of days, categories to include/exclude and settings for category filter.
	global $wpdb;
	global $statuslist;
	global $notify_is;
	$numberofdays = (get_option('TIEexpire_days') != '') ? get_option('TIEexpire_days') : '0';
	$catstoinclude = (get_option('TIEexpire_catsin') != '') ? get_option('TIEexpire_catsin') : '0';
	$catstoexclude = (get_option('TIEexpire_catsout') != '') ? get_option('TIEexpire_catsout') : '0';
	$catsincludeon = (get_option('TIEexpire_catsradio') != '') ? get_option('TIEexpire_catsradio') : '' ;
	$catsindays = (get_option('TIEexpire_catsdays') != '') ? get_option('TIEexpire_catsdays') : '' ;
	
	// Find posts, then move them to Trash.
	if ($numberofdays > 0) {
		$dayquery = "SELECT * FROM $wpdb->posts
					 WHERE $wpdb->posts.post_status IN ($statuslist)
					 AND $wpdb->posts.post_type = 'post'
					 AND $wpdb->posts.post_date < DATE_SUB(NOW(), INTERVAL $numberofdays DAY) ";
					 
	// Check if category filter is on and, if so, check filter and apply it.
		if ($catsindays	== 'on') {
			if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
				$dayquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
							  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
				}
			elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
					$dayquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
								  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
			}
		}
	
	// Run query and move results to Trash.
		$result = $wpdb->get_results($dayquery);
		foreach ($result as $post) {
		    setup_postdata($post);  
			$postid = $post->ID;   
			if ($notify_is == 'on') {
				$postauthorid = $post->post_author;
				$postname = $post->post_title;
				TIEexpire_send_notification($postauthorid, $postname);
			}
			wp_delete_post($postid);
		}
	}
}	
	
// Retain a given number of most recent posts and expire all others.
function expirebyposts() {

	// Get the user-defined post ceiling and category filter details.
	global $wpdb;
	global $statuslist;
	global $notify_is;
	$numberofposts = (get_option('TIEexpire_posts') != '') ? get_option('TIEexpire_posts') : '0';
	$catstoinclude = (get_option('TIEexpire_catsin') != '') ? get_option('TIEexpire_catsin') : '0';
	$catstoexclude = (get_option('TIEexpire_catsout') != '') ? get_option('TIEexpire_catsout') : '0';
	$catsincludeon = (get_option('TIEexpire_catsradio') != '') ? get_option('TIEexpire_catsradio') : '' ;
	$catsinposts = (get_option('TIEexpire_catsposts') != '') ? get_option('TIEexpire_catsposts') : '' ;
	
	// Get the total number of selected posts, depending on category filters
	$countquery = "SELECT COUNT(*) FROM $wpdb->posts
				   WHERE $wpdb->posts.post_status IN ($statuslist) 
				   AND $wpdb->posts.post_type = 'post' " ;
	
	if ($catsinposts == 'on') {
		if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
			$countquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
							WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
		}	  
		elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
			$countquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
						   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";								  
		}
	}
	
	$countposts = $wpdb->get_var($countquery);
	
	// Work out how many posts to remove, list in reverse order and move them to the Trash.

	if ($numberofposts > 0 && $countposts > $numberofposts) {
		$limitposts = $countposts-$numberofposts;
		$postquery = "SELECT * FROM $wpdb->posts
					  WHERE $wpdb->posts.post_status IN ($statuslist)
					  AND $wpdb->posts.post_type = 'post' ";
						  
		// Check if category filter is on and, if so, check filter and apply it.	
		if ($catsinposts	== 'on') {
			if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
				$postquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
							   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
			}
			elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
				$postquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
							   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
			}
		}
				
	// Complete and run query, then move results to Trash.
		$postquery .= "ORDER BY $wpdb->posts.post_date ASC
					   LIMIT $limitposts" ;
		$result = $wpdb->get_results($postquery);
		foreach ($result as $post) {
			setup_postdata($post);  
			$postid = $post->ID;
			if ($notify_is == 'on') {
				$postauthorid = $post->post_author;
				$postname = $post->post_title;
				TIEexpire_send_notification($postauthorid, $postname);
			}
			wp_delete_post($postid);
		}
	}	
}

// Expire posts with fewer than a given number of views after a given number of days.
function expirebyviews() { 

	// Requires BAW Post Views Count plugin, so check it's active.
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
	if (is_plugin_active('baw-post-views-count/bawpv.php')) {
    
		// Get the user-defined number of days and views.
		global $wpdb;
		global $statuslist;
		global $notify_is;
		$numberofviewdays = (get_option('TIEexpire_viewdays') != '') ? get_option('TIEexpire_viewdays') : '0';
		$numberofviews = (get_option('TIEexpire_views') != '') ? get_option('TIEexpire_views') : '0';
		$catstoinclude = (get_option('TIEexpire_catsin') != '') ? get_option('TIEexpire_catsin') : '0';
		$catstoexclude = (get_option('TIEexpire_catsout') != '') ? get_option('TIEexpire_catsout') : '0';
		$catsincludeon = (get_option('TIEexpire_catsradio') != '') ? get_option('TIEexpire_catsradio') : '' ;
		$catsinviews = (get_option('TIEexpire_catsviews') != '') ? get_option('TIEexpire_catsviews') : '' ;
		
		if ($numberofviewdays > 0 && $numberofviews > 0) {

		// Trash posts without enough views after given number of days.
			$postquery = "SELECT * FROM $wpdb->posts
						  JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id
						  WHERE $wpdb->posts.post_status IN ($statuslist) 
						  AND $wpdb->posts.post_type = 'post'
						  AND $wpdb->posts.post_date < DATE_SUB(NOW(), INTERVAL $numberofviewdays DAY)
						  AND $wpdb->postmeta.meta_key='_count-views_all'
						  AND $wpdb->postmeta.meta_value < $numberofviews" ;
						  
		// Check if category filter is on and, if so, check filter and apply it.
			if ($catsinviews == 'on') {
				if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
					$postquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
								   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
				}
				elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
					$postquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
								   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
				}
			}

	// Run query and move results to Trash.			  
		
			$result = $wpdb->get_results($postquery);
			foreach ($result as $post) {
				setup_postdata($post);  
				$postid = $post->ID;
				if ($notify_is == 'on') {
					$postauthorid = $post->post_author;
					$postname = $post->post_title;
					TIEexpire_send_notification($postauthorid, $postname);
				}				
				wp_delete_post($postid);
			}
		
		// Trash posts with no views at all after given number of days.
			$postquery = "SELECT * FROM $wpdb->posts
						  WHERE $wpdb->posts.post_status IN ($statuslist) 
						  AND $wpdb->posts.post_type = 'post'
						  AND $wpdb->posts.post_date < DATE_SUB(NOW(), INTERVAL $numberofviewdays DAY)
						  AND $wpdb->posts.ID NOT IN (SELECT DISTINCT post_id FROM $wpdb->postmeta
							 WHERE $wpdb->postmeta.meta_key='_count-views_all')" ;
							 
		// Adjust for category filters.
			if ($catsinviews == 'on') {
				if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
					$postquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
								   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
				}
				elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
					$postquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
								   WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
				}
			}

		// Run query and move results to Trash.				  
		
			$result = $wpdb->get_results($postquery);
			foreach ($result as $post) {
				setup_postdata($post);  
				$postid = $post->ID; 
				if ($notify_is == 'on') {
					$postauthorid = $post->post_author;
					$postname = $post->post_title;
					TIEexpire_send_notification($postauthorid, $postname);
				}				
				wp_delete_post($postid);
			}		
		}
	}
}
	
function expireunlikedposts() {

	// Requires WTI Like Post plugin, so check it's active.
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );	
	if (is_plugin_active('wti-like-post/wti_like_post.php')) {

		// Get the user-defined number of days and likes.
		global $wpdb;
		global $statuslist;
		global $notify_is;
		$numberoflikedays = (get_option('TIEexpire_likedays') != '') ? get_option('TIEexpire_likedays') : '0';
		$numberoflikes = (get_option('TIEexpire_likes') != '') ? get_option('TIEexpire_likes') : '0';
		$catstoinclude = (get_option('TIEexpire_catsin') != '') ? get_option('TIEexpire_catsin') : '0';
		$catstoexclude = (get_option('TIEexpire_catsout') != '') ? get_option('TIEexpire_catsout') : '0';
		$catsincludeon = (get_option('TIEexpire_catsradio') != '') ? get_option('TIEexpire_catsradio') : '' ;
		$catsinlikes = (get_option('TIEexpire_catslikes') != '') ? get_option('TIEexpire_catslikes') : '' ;		
		
		// Check the summary view exists and create it if not. Database prefix included for multiple blogs in one DB.
	
		$table_name = "{$wpdb->prefix}wti_totals";
		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
			$sql = "CREATE VIEW {$wpdb->prefix}wti_totals ( post_id, value ) 
				    AS SELECT post_id, SUM( value ) 
					FROM {$wpdb->prefix}wti_like_post
					GROUP BY post_id" ;
			$result = $wpdb->query($sql);
		}
	
		if ($numberoflikedays > 0 && $numberoflikes > 0) {
	
		// Trash all posts with no likes registered in given number of days.
			$novotesquery = "SELECT * FROM $wpdb->posts
							 WHERE $wpdb->posts.post_type = 'post'
							 AND $wpdb->posts.post_status IN ($statuslist)
							 AND $wpdb->posts.post_date < DATE_SUB(NOW(), INTERVAL $numberoflikedays DAY)
							 AND $wpdb->posts.ID NOT IN (SELECT DISTINCT post_id FROM {$wpdb->prefix}wti_totals)" ;
							 
		// Check if category filter is on and, if so, check filter and apply it.
			if ($catsinlikes == 'on') {
				if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
					$novotesquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
									  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
				}
				elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
					$novotesquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
									  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
				}
			}

	// Run query and move results to Trash.
			$result = $wpdb->get_results($novotesquery);
			foreach ($result as $post) {
				setup_postdata($post);  
				$postid = $post->ID;  
				if ($notify_is == 'on') {
					$postauthorid = $post->post_author;
					$postname = $post->post_title;
					TIEexpire_send_notification($postauthorid, $postname);
				}
				wp_delete_post($postid);
			}
		
		// Trash all posts with too few likes in given number of days.
			$negvotesquery = "SELECT * FROM $wpdb->posts
							  INNER JOIN {$wpdb->prefix}wti_totals
								ON $wpdb->posts.ID = {$wpdb->prefix}wti_totals.post_id
							  WHERE $wpdb->posts.post_type = 'post'
							  AND $wpdb->posts.post_status IN ($statuslist)
							  AND $wpdb->posts.post_date < DATE_SUB(NOW(), INTERVAL $numberoflikedays DAY)
							  AND {$wpdb->prefix}wti_totals.value < $numberoflikes" ;
							 
		// Check if category filter is on and, if so, check filter and apply it.
			if ($catsinlikes == 'on') {
				if ($catsincludeon == 'include' && $catstoinclude != '' && $catstoinclude != '0') {
					$novotesquery .= "AND $wpdb->posts.ID IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
									  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoinclude . "))";
				}
				elseif ($catsincludeon == 'exclude' && $catstoexclude != '' && $catstoexclude != '0') {	 
					$novotesquery .= "AND $wpdb->posts.ID NOT IN (SELECT DISTINCT object_id FROM $wpdb->term_relationships
									  WHERE $wpdb->term_relationships.term_taxonomy_id IN (" . $catstoexclude . "))";
				}
			}

		// Run query and move results to Trash.
			$result = $wpdb->get_results($negvotesquery);
			foreach ($result as $post) {
				setup_postdata($post);  
				$postid = $post->ID;  
				if ($notify_is == 'on') {
					$postauthorid = $post->post_author;
					$postname = $post->post_title;
					TIEexpire_send_notification($postauthorid, $postname);
				}				
				wp_delete_post($postid);
			}
		}
	}	
}

// Function to send notification emails. They are sent individually rather than using CC, BCC and so on.
function TIEexpire_send_notification($the_post_author_ID, $the_post_title) {
	$notify_poster = (get_option('TIEtools_notify_poster') == 'on') ? 'on' : '' ;
	$notify_admin = (get_option('TIEtools_notify_admin') == 'on') ? 'on' : '' ;
	$notify_other = (get_option('TIEtools_notify_other') == 'on') ? 'on' : '' ;
	$notify_email = (get_option('TIEtools_notify_email') != '') ? get_option('TIEtools_notify_email') : '';

	// Set some email parameters.
	$postauthor = get_userdata($the_post_author_ID)->user_nicename;
	$emailsubject = 'Post expired';

	// The email text is held in $notifytext and is different for each addressee.
	
	// Email to post author
	if ($notify_poster == 'on') {
		$sendemailto = get_userdata($the_post_author_ID)->user_email;
		$notifytext =  "Hello " . $postauthor . ",\n\nThis is an automated message from " . get_option('blogname') . " to inform you that the post titled " . $the_post_title . " has expired. Please contact the site admin at " . get_bloginfo('admin_email') . " if you believe there has been a mistake.\n\nMessage generated by TIEexpire for " . get_option('siteurl');
		wp_mail($sendemailto, $emailsubject, $notifytext);
	}

	// Email to site admin
	if ($notify_admin == 'on') {
		$sendemailto = get_bloginfo('admin_email');
		$notifytext =  "Hello Admin,\n\nThis is an automated message from " . get_option('blogname') . " to inform you that the post titled " . $the_post_title . " by " . $postauthor . " has expired.\n\nMessage generated by TIEexpire for " . get_option('siteurl');
		wp_mail($sendemailto, $emailsubject, $notifytext);
	}

	// Email to whoever else
	if ($notify_other == 'on' && notify_email != '') {
		$sendemailto = $notify_email;
		$notifytext =  "Hello,\n\nThis is an automated message from " . get_option('blogname') . " to inform you that the post titled " . $the_post_title . " by " . $postauthor . " has expired. Please contact admin if that's a mistake.\n\nMessage generated by TIEexpire for " . get_option('siteurl');
		wp_mail($sendemailto, $emailsubject, $notifytext);
	}
}
?>