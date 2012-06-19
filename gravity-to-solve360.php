<?php
/**
 * @package Gravity_to_Solve360
 * @version 0.96
 */
/*
Plugin Name: Gravity to Solve360
Description: Exports data from completed <a href="http://www.gravityforms.com/">Gravity Forms</a> to a specified <a href="http://norada.com/">Solve360</a> account.
Version: 0.96
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
	// Export
	add_management_page('Gravity to Solve360', 'Gravity to Solve360', 'manage_options', 'gravity-to-solve360', 'gts360');
	// Options
	add_options_page('Gravity to Solve360', 'Gravity to Solve360', 'manage_options', 'gravity-to-solve360-options', 'gts360_options');
}

global $accepted_fields;

$accepted_fields = array(
	'gravity_to_solve360_debug_mode',
	'gravity_to_solve360_debug_start_date',
	'gravity_to_solve360_user',
	'gravity_to_solve360_token',
	'gravity_to_solve360_to',
	'gravity_to_solve360_from',
	'gravity_to_solve360_cc',
	'gravity_to_solve360_bcc',
	'gravity_to_solve360_cron_frequency'
);

if(!get_option('gravity_to_solve360_debug_mode')) update_option('gravity_to_solve360_debug_mode', 'true');

// Tools > Export to Solve360

function gts360() {
	require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');
}

// Cron - run after any form submission

add_action("gform_after_submission", "gravity_to_solve360_after_submission", 10, 2);

function gravity_to_solve360_after_submission() {
	wp_schedule_single_event(time(), 'gravity_to_solve360_cron');
}

add_action('gravity_to_solve360_cron','gravity_to_solve360_send');

function gravity_to_solve360_send() {
	require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');
}

function gts360_options() {

global $accepted_fields;

// Save data

if($_POST && wp_verify_nonce($_POST['gravity_to_solve360_nonce'],'gravity_to_solve360_edit')) {
	
	foreach($accepted_fields as $accepted_field) {
		update_option( $accepted_field, $_POST[$accepted_field] );
	}
	
}

?>
<div class="wrap">

<form method="post" id="gravity_to_solve360">

<h2>Gravity to Solve360 - options</h2>

<table>

	<tr>
		<td>
			<h3>Debug details</h3>
		</td>
		<td>
		</td>
	</tr>

	<tr>

		<td>
			Debug mode
		</td>
		<td>
		<?php $gravity_to_solve360_debug_mode = (get_option('gravity_to_solve360_debug_mode') == 'true'); ?>
			<input type="radio" name="gravity_to_solve360_debug_mode" value="true" id="gravity_to_solve360_debug_mode_enabled" <?php if($gravity_to_solve360_debug_mode) echo 'checked="checked" '; ?> />
			&nbsp; <label for="gravity_to_solve360_debug_mode_enabled">On</label> &nbsp; &nbsp;
			
			<input type="radio" name="gravity_to_solve360_debug_mode" value="false" id="gravity_to_solve360_debug_mode_disabled" <?php if(!$gravity_to_solve360_debug_mode) echo 'checked="checked" '; ?> />
			&nbsp; <label for="gravity_to_solve360_debug_mode_disabled">Off</label> &nbsp; &nbsp;
		</td>
	</tr>
	<tr>

		<td>
			<label for="gravity_to_solve360_debug_start_date">Override Start Date</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_debug_start_date" id="gravity_to_solve360_debug_start_date" value="<?php echo get_option('gravity_to_solve360_debug_start_date'); ?>" />
			Current Start Date: <?php echo get_option('gravity_to_solve360_last_export_date'); ?>
		</td>
	</tr>
	
	<tr>
		<td>
			<h3>Solve details</h3>
		</td>
		<td>
		</td>
	</tr>
	
	<tr>
	
		<td>
			<label for="gravity_to_solve360_user">Solve360 User</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_user" id="gravity_to_solve360_user" value="<?php echo get_option('gravity_to_solve360_user'); ?>" />		
		</td>
	</tr>

	<tr>
	
		<td>
			<label for="gravity_to_solve360_token">Solve360 API Token</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_token" id="gravity_to_solve360_token" value="<?php echo get_option('gravity_to_solve360_token'); ?>" />		
		</td>
	</tr>

	<tr>
		<td>
			<h3>Solve notification details</h3>
		</td>
		<td>
			<p>user@example.com, Another User &lt;anotheruser@example.com&gt;</p>
		</td>
	</tr>
	
	<tr>
		<td>
			<label for="gravity_to_solve360_to">To:</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_to" id="gravity_to_solve360_to" value="<?php echo get_option('gravity_to_solve360_to'); ?>" />
		</td>
	</tr>
	
	<tr>
		<td>
			<label for="gravity_to_solve360_from">From:</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_from" id="gravity_to_solve360_from" value="<?php echo get_option('gravity_to_solve360_from'); ?>" />		
		</td>
	</tr>
	
	<tr>
		<td>
			<label for="gravity_to_solve360_cc">CC:</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_cc" id="gravity_to_solve360_cc" value="<?php echo get_option('gravity_to_solve360_cc'); ?>" />		
		</td>
	</tr>
	
	<tr>
		<td>
			<label for="gravity_to_solve360_bcc">Bcc:</label>
		</td>
		<td>
			<input type="text" class="regular-text" name="gravity_to_solve360_bcc" id="gravity_to_solve360_bcc" value="<?php echo get_option('gravity_to_solve360_bcc'); ?>" />		
		</td>
	</tr>

	<tr>
		<td>
			<h3>Automatic sending to Solve360</h3>
		</td>
		<td>
		</td>
	</tr>

	<tr>
		<td>
			<label for="gravity_to_solve360_cron_frequency">Frequency</label>
		</td>
		<td>
			<select name="gravity_to_solve360_cron_frequency" id="gravity_to_solve360_cron_frequency">
				<?php

				global $gravity_to_solve360_cron_frequencies;

				foreach ($gravity_to_solve360_cron_frequencies as $frequency_name => $frequency_display)
				{
					echo '<option value="' . $frequency_name . '"';
					if($frequency_name == get_option('gravity_to_solve360_cron_frequency')) echo ' selected="selected" ';
					echo '>' . $frequency_display . '</option>';
				}
				?>
			</select>		
		</td>
	</tr>

</table>


<br/><br/>
<p class="submit" style="text-align: left;">
	<input type="submit" name="submit" value="Save Settings" class="button-primary"/>
</p>

<?php wp_nonce_field('gravity_to_solve360_edit','gravity_to_solve360_nonce'); ?>

</form>

</div>

<?php

}