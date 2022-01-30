<?php
/*
Plugin Name: Auto Publish Drafts
Description: Auto Publish Drafts is a plugin that will automatically publish drafts every 5 minutes.
Version:     1.0
Author:      Wong Siong Kiat
License:     GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Text Domain: auto-publish-drafts
Requires at least: 4.9
Requires PHP: 5.6 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU
General Public License version 2, as published by the Free Software Foundation. You may NOT assume
that you can use any other version of the GPL.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

if(!defined('ABSPATH'))
	die('Invalid request.');

// Action to take when plugin is activated or deactivated.
register_activation_hook(__FILE__, 'activate_auto_publish_drafts');
register_deactivation_hook(__FILE__, 'deactivate_auto_publish_drafts');

// Plugins activated.
function activate_auto_publish_drafts() {
    if(!wp_get_schedule('auto_publish_drafts'))
        wp_schedule_event(time() + 300, 'publish_draft_every_5_minutes', 'auto_publish_drafts');
}

// Plugins deactivated.
function deactivate_auto_publish_drafts() {
    wp_clear_scheduled_hook('auto_publish_drafts');
}

// Custom cron schedule to auto publish drafts every 5 minutes.
function custom_publish_draft_cron_schedule($schedules) {
	$schedules['publish_draft_every_5_minutes'] = array(
		'interval' => 300,
		'display'  => __('Auto Publish Draft Every 5 Minutes'),
	);

	return $schedules;
}
add_filter('cron_schedules', 'custom_publish_draft_cron_schedule');

// Auto publish drafts every 5 minutes.
function auto_publish_draft() {
	$args = array(
		'fields' => 'ids',
		'post_type' => 'post',
		'post_status' => 'draft',
		'posts_per_page' => 1,
		'orderby' => 'date',
		'order' => 'ASC'
	);
	$draft_posts = new WP_Query($args);

	if($draft_posts->have_posts()) {
    	foreach($draft_posts->posts as $draft_post_id)
            wp_update_post(array('ID' => $draft_post_id, 'post_status' => 'publish'));

		wp_reset_postdata();
	}
}
add_action('auto_publish_drafts', 'auto_publish_draft');