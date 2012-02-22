<?php
/**
 * @package Gravity_to_Solve360
 * @version 0.5
 */
/*
Plugin Name: Gravity to Solve360
Description: Exports data from completed <a href="http://www.gravityforms.com/">Gravity Forms</a> to a specified <a href="http://norada.com/">Solve360</a> account.
Version: 0.5
Author: Steve Barnett
Author URI: http://naga.co.za
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// add menu page
add_action('admin_menu', 'create_gts360_menu');

function create_gts360_menu() {
	add_management_page('Export to Solve360', 'Export to Solve360', 'manage_options', 'gravity-to-solve360', 'gts360');

}

// Settings > Export to Solve360

function gts360() {

require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');

}
