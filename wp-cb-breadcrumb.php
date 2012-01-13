<?php
/*
Plugin Name: WPCB BreadCrumb
Plugin URI: http://www.amiworks.com/
Description: WP CB plugin to display breadcrumbs.
Version: 0.1
Author: Aman Kumar Jain
Author URI: http://amanjain.com
*/

function WPCBBreadCrumbLoad()
{
	require_once(dirname(__FILE__).DIRECTORY_SEPARATOR."WP-CB-BreadCrumb-Class.php");	
}

	
add_action('cfct-modules-loaded', 'WPCBBreadCrumbLoad');

