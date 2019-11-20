<?php
/**
 * Uninstall file, which would delete all user metadata and configuration settings
 *
 * @since 1.0
 */
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

global $wpdb;

$wpdb->query("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '%ld_video_tracking%';");
$wpdb->query("DELETE FROM $wpdb->options WHERE option_name='ld_video_tracking_version';");


$wpdb->query("DELETE FROM $wpdb->options WHERE option_name='ld_video_tracking_options';");