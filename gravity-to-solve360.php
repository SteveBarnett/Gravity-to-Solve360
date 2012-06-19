<?php
/**
 * @package Gravity_to_Solve360
 * @version 0.95
 */
/*
Plugin Name: Gravity to Solve360
Description: Exports data from completed <a href="http://www.gravityforms.com/">Gravity Forms</a> to a specified <a href="http://norada.com/">Solve360</a> account.
Version: 0.95
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

// Cron - add 1, 5, 15 minutes to schedules

global $gravity_to_solve360_cron_frequencies;

$gravity_to_solve360_cron_frequencies = array(
	'manual' => 'Do not send automatically',
	'everyminute' => 'Every minute (not recommended)',
	'every5minutes' => 'Every 5 minutes',
	'every15minutes' => 'Every 15 minutes',
	'hourly' => 'Every hour',
	'twicedaily' => 'Twice a day',
	'daily' => 'Once a day'
	);

add_filter( 'cron_schedules', 'gts360_extend_schedules' );

function gts360_extend_schedules( $schedules ) {
	$schedules['everyminute'] = array(
		'interval' => 60,
		'display' => __( 'Once a minute' )
	);
	 $schedules['every5minutes'] = array(
		'interval' => 60 * 5,
		'display' => __( 'Once every 5 minutes' )
	);
	 $schedules['every15minutes'] = array(
		'interval' => 60 * 15,
		'display' => __( 'Once every 15 minutes' )
	);
	return $schedules;
}

// Cron - adding and removing

// $gravity_to_solve360_cron_frequency = get_option('gravity_to_solve360_cron_frequency');

// // clear all schedule hooks except the current one
// $gravity_to_solve360_cron_frequencies_unused = $gravity_to_solve360_cron_frequencies;
// unset($gravity_to_solve360_cron_frequencies_unused[$gravity_to_solve360_cron_frequency]);

// foreach($gravity_to_solve360_cron_frequencies_unused as $frequency_name => $frequency_display) {
// 	wp_clear_scheduled_hook('gravity_to_solve360_cron_' . $frequency_name);
// }

// if($gravity_to_solve360_cron_frequency != 'manual') {
	
// 	add_action( 'gravity_to_solve360_cron_' . $gravity_to_solve360_cron_frequency, 'gravity_to_solve360_cron' );

// 	if ( !wp_next_scheduled('gravity_to_solve360_cron_' . $gravity_to_solve360_cron_frequency)) {
// 		// wp_schedule_event(time(), $gravity_to_solve360_cron_frequency, 'gravity_to_solve360_cron_' . $gravity_to_solve360_cron_frequency );
// 		wp_schedule_event(current_time('timestamp')+30, $gravity_to_solve360_cron_frequency, 'gravity_to_solve360_cron_' . $gravity_to_solve360_cron_frequency );
// 	}
	
// }

// function gravity_to_solve360_cron() {
// 	require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');
// 	mail('steve@naga.co.za', 'test ' . date('Y-m-d H:i:s'), $output, "Content-type: text/html\r\n");
// }


// wp_clear_scheduled_hook('gravity_to_solve360_cron_everyminute');
// wp_clear_scheduled_hook('gravity_to_solve360_cron');
// wp_clear_scheduled_hook('gravity_to_solve360_cron_ts');


// if ( ! wp_next_scheduled('my_task_hook') ) {
// 	wp_schedule_event( time(), 'hourly', 'my_task_hook' ); // hourly, daily and twicedaily
// }
// add_action( 'my_task_hook', 'my_task_function' );
// function my_task_function() {
// 	wp_mail( 
// 		'steve@naga.co.za', 
// 		'Automatic mail', 
// 		'Hello, this is an automatically scheduled email from WordPress.'
// 	);
// }

// if ( ! wp_next_scheduled('my_task_hook2') ) {
// 	wp_schedule_event( time(), 'everyminute', 'my_task_hook2' ); // hourly, daily and twicedaily
// }
// add_action( 'my_task_hook2', 'my_task_function' );
// function my_task_function() {
// 	wp_mail( 
// 		'steve@naga.co.za', 
// 		'Automatic mail', 
// 		'Hello, this is an automatically scheduled email from WordPress.'
// 	);
// }


add_action("gform_after_submission", "gravity_to_solve360_after_submission", 10, 2);

function gravity_to_solve360_after_submission() {
	// wp_schedule_single_event(time(), 'gravity_to_solve360_cron');
	wp_schedule_single_event(current_time('timestamp'), 'gravity_to_solve360_cron');
	// wp_schedule_single_event(current_time('timestamp'), 'gravity_to_solve360_cron_ts');
}

add_action('gravity_to_solve360_cron','gravity_to_solve360_send');
// add_action('gravity_to_solve360_cron_ts','gravity_to_solve360_send_ts');

function gravity_to_solve360_send() {
	require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');
	mail('steve@naga.co.za', 'test ' . date('Y-m-d H:i:s'), $output, "Content-type: text/html\r\n");
}

// function gravity_to_solve360_send_ts() {
// 	require(ABSPATH . 'wp-content/plugins/gravity-to-solve360/gravity-to-solve360.inc.php');
// 	mail('steve@naga.co.za', 'test ts ' . date('Y-m-d H:i:s'), $output, "Content-type: text/html\r\n");
// }





function gts360_options() {

print_r(_get_cron_array());
// echo '<br /> 1340047883 ' . date('Y-m-d H:i:s', 1340047883) . '<br />';
// print_r(wp_get_schedules()); // has new slots


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