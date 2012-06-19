<?php

$debug = (get_option('gravity_to_solve360_debug_mode') == 'true');

// Solve360 details

// REQUIRED Edit with the email address you login to Solve360 with
$gravity_to_solve360_user = get_option('gravity_to_solve360_user');
// REQUIRED Edit with token, Workspace > My Account > API Reference > API Token
$gravity_to_solve360_token = get_option('gravity_to_solve360_token');


// Date overrides for debugging
 $debug_start_date = get_option('gravity_to_solve360_debug_start_date');


// Initialise output and error logging

$output = '';

$output .= '<div class="wrap">';
$output .= '<h2>Gravity to Solve360</h2>';

$errors = false;

// check for Gravity Forms

if(!function_exists('is_plugin_active')) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php');
}

if(!is_plugin_active('gravityforms/gravityforms.php')) {
	$errors .= '<p>Gravity Forms plugin is not activated, and is required.</p>';
}

// check Solve service and credentials
// Solve360Service.php available from http://norada.com/?uri=norada/crm/external_api_introduction

if(!file_exists(ABSPATH . 'wp-content/plugins/gravity-to-solve360/Solve360Service.php')) {
	$errors .= '<p>Solve360Service.php is not present, and is required.</p>';
}
elseif(!$gravity_to_solve360_user || !$gravity_to_solve360_token) {
	$errors .= '<p>Solve360 User and Token not set, and are required.</p>';
}

if(!$errors) {

	if($debug) {
	$modeoutput .= '<h3>Debug mode</h3>';
	}
	else
	{
	$modeoutput .= '<h3>Live mode</h3>';
	}

	if($debug_start_date) {
	$start_date = $debug_start_date;
	}
	elseif(!get_option('gravity_to_solve360_last_export_date')) {
	$start_date =  '2011-01-01 00:00:00';
	}
	else {
		$start_date = get_option('gravity_to_solve360_last_export_date');
	}

	// $end_date = $debug_end_date ? $debug_end_date : date('Y-m-d H:i:s');
	$end_date = $debug_end_date ? $debug_end_date : current_time('mysql');

	// Gravity stores UTC time in database, but displays local time
	$output .= '
	<p>Exporting all Gravity Forms entries between <strong>' . $start_date . '</strong> and <strong>' . $end_date . '</strong>.</p>';

	// Make array of form ids and Solve fields

	$forms_and_fields = array();

	$active = RGForms::get("active") == "" ? null : RGForms::get("active");
    $forms = RGFormsModel::get_forms($active, "title");

    foreach($forms as $form) {

		$required_fields = array(
			'businessemail' => false, // required for matching contacts
			'category' => false, // required by API
			'ownership' => false // required by API
		);

		// get form meta - array of form data: id, title, description, fields, etc
		$form_meta = RGFormsModel::get_form_meta($form->id);
		$notenumber = 0;
		$found_fields = array();

		// get field numbers for later use.
		foreach($form_meta['fields'] as &$fields)
		{

			// check adminLabels
			if(stripos($fields['adminLabel'], 'solve360 ') !== false)
			{
				$adminLabel = str_ireplace('solve360 ','',$fields['adminLabel']);

				if(stripos($fields['adminLabel'], 'fullname') !== false)
				{
					$forms_and_fields[$form->id]['firstname'] = $fields['id'] . '.3';
					$forms_and_fields[$form->id]['lastname'] = $fields['id'] . '.6';
					$found_fields[] = 'firstname';
					$found_fields[] = 'lastname';
				}
				elseif(stripos($fields['adminLabel'], 'name') !== false)
				{
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$found_fields[] = 'name';

				}
				elseif(stripos($fields['adminLabel'], 'firstname') !== false)
				{
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$found_field[] = 'firstname';
				}

				elseif(stripos($fields['adminLabel'], 'lastname') !== false)
				{
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$found_fields[] = 'lastname';
				}
				elseif(stripos($fields['adminLabel'], 'note') !== false)
				{
					$notetext = str_ireplace('note ','',$adminLabel);
					$forms_and_fields[$form->id]['note'][$notenumber]['id'] = $fields['id'];
					$forms_and_fields[$form->id]['note'][$notenumber]['text'] = $notetext;
					$notenumber++;
					$found_fields[] = 'note';
					}
				elseif(stripos($fields['adminLabel'], 'businessemail') !== false)
				{
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$required_fields['businessemail'] = true;
					$found_fields[] = 'businessemail';
				}
				elseif(stripos($fields['adminLabel'], 'cellularphone') !== false)
				{
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$required_fields['cellularphone'] = true;
					$found_fields[] = 'cellularphone';
				}
				else {
					$forms_and_fields[$form->id][$adminLabel] = $fields['id'];
					$found_fields[] = $adminLabel;
				}
			}

			// check labels
			if(stripos($fields['label'], 'solve360 ') !== false)
			{
				if(stripos($fields['label'], 'note') !== false)
				{
					$notetext = str_ireplace('solve360 note ','',$fields['label']);
					$forms_and_fields[$form->id]['note'][$notenumber]['id'] = $fields['id'];
					$forms_and_fields[$form->id]['note'][$notenumber]['text'] = $notetext;
					$notenumber++;
					$found_fields[] = 'note (hidden)';
				}
				elseif(stripos($fields['label'], 'category') !== false)
				{
					$forms_and_fields[$form->id]['categoriestoadd'][] = $fields['defaultValue'];
					$required_fields['category'] = true;
					$found_fields[] = 'category';
				}
				elseif(stripos($fields['label'], 'ownership') !== false)
				{
					$forms_and_fields[$form->id]['ownership'] = $fields['defaultValue'];
					$required_fields['ownership'] = true;
					$found_fields[] = 'ownership';
				}
				else {
					$label = str_ireplace('solve360 ','',$fields['label']);
					$forms_and_fields[$form->id][$label] = $fields['defaultValue'];
					$found_fields[] = $label;
				}
			}

		} // foreach($form_meta['fields'] as &$fields)

		$output .= '<br />';


		sort($found_fields);

		if(count(array_unique($required_fields)) == 1) { // all or none required present

			if(current($required_fields) === false) { // no required fields present
				$output .= '<strong>' . $form->title . '</strong><br />No solve360 labels found, skipping form.<br />';
			}
			else {
				$output .= '<strong>' . $form->title . '</strong><br />Found fields: ';
				foreach ($found_fields as $found_field_name) {
					$output .= $found_field_name . ', ';
				}
				$output = substr($output, 0, -2);
				$output .= '.<br />';

			}

		}
		else { // some required fields are missing
			$output .= '<strong>' . $form->title . '</strong><br />';

				foreach($required_fields as $required_field => $required_field_present) {
				if(!$required_field_present) {
					$output .= '<div class="error"><p><em>' . $required_field . '</em> is required, and is not set. Add <strong>solve360 ' . $required_field . '</strong> to the appropriate field.</p></div>';
				}
			}
			$errors = true;
		}		

	} // foreach($forms as $form)

	if($debug) $output .= print_r($forms_and_fields, true);

	// create contacts array

	if ($errors) {
		$output .= '<br /><div class="updated"><p>Forms <strong>not</strong> exported to Solve360. Please fix the errors highlighted above.</p></h3>';
	}
	else {

		// Get leads

		$contactstosend = array();
		$solve_lead = 0;


		foreach($forms_and_fields as $id => $details)
		{
			// Gravity Forms uses UCT dates for database, but local time for display

				$gravity_to_solve360_offset .= time()-current_time('timestamp');
				$gravity_to_solve360_adjusted_start_date = date('Y-m-d H:i:s', strtotime($start_date) + $gravity_to_solve360_offset);
				$gravity_to_solve360_adjusted_end_date = date('Y-m-d H:i:s', strtotime($end_date) + $gravity_to_solve360_offset);

			$leads = RGFormsModel::get_leads($id, 0, "DESC", "", 0, 1000, null, null, false, $gravity_to_solve360_adjusted_start_date, $gravity_to_solve360_adjusted_end_date);

			if($leads)
			{

				foreach($leads as $lead)
				{

					if($lead['date_created'] >= $gravity_to_solve360_adjusted_start_date) {

						foreach($forms_and_fields[$id] as $field_name => $field_id) {

							if($field_name == 'note') {

								$solve_note = '';
								if(count($details['note'])>0) {

									foreach($details['note'] as $note)
									{
										$solve_note .= $note['text'] . "\n";
										if($note['id'])
										{
											if(RGFormsModel::get_field_value_long($lead['id'], $note['id'], $id)) {
												$solve_note .= RGFormsModel::get_field_value_long($lead['id'], $note['id']) . "\n";
											}
											else
											{
												$solve_note .= $lead[$note['id']] . "\n";
											}
										}
										$solve_note .= "\n";
									}
									$contactstosend[$solve_lead]['note'] = $solve_note;

								}
							} // if($field_name == 'note')
							elseif ($field_name == 'name') {

								// split name by first space
								$split_name = explode(' ', $lead[$forms_and_fields[$id]['name']], 2);

								$contactstosend[$solve_lead]['firstname'] = $split_name[0];
								$contactstosend[$solve_lead]['lastname'] = $split_name[1];

							}
							elseif ($field_name == 'categoriestoadd') {
								$contactstosend[$solve_lead]['categoriestoadd'] = $forms_and_fields[$id]['categoriestoadd'];
							}
							elseif ($field_name == 'ownership') {
								$contactstosend[$solve_lead]['ownership'] = $forms_and_fields[$id]['ownership'];
							}
							else {
								$contactstosend[$solve_lead][$field_name] = $lead[$forms_and_fields[$id][$field_name]];
							}


						} // foreach($forms_and_fields[$id] as $field_name => $field_id)

						$solve_lead++;

					} // if($lead['date_created'] > $gravity_to_solve360_adjusted_start_date) 

				} // foreach($leads as $lead)
			}
		} // foreach($forms_and_fields as $id => $details)

		if($debug) $output .= '<h4>$contactstosend</h4>' . print_r($contactstosend, true);


		if(count($contactstosend) > 0) {

		if(!$debug) {
		
			// Configure service gateway object
			
			require 'Solve360Service.php';
			$solve360Service = new Solve360Service($gravity_to_solve360_user, $gravity_to_solve360_token);
		}


		$output .= '
		<h3>Export details</h3>
		<table>
			<thead>
				<th scope="col">Last name</th>
				<th scope="col">First name</th>
				<th scope="col">Email address</th>';

		if(!$debug) {
		$output .= '
				<th scope="col">Solve360 status</th>';
		}

		$output .= '
			</thead>
			<tbody>
		';
		}

		foreach ($contactstosend as $contactData) {
			$output .= '<tr>';
			$output .= '<td>' . $contactData['lastname'] . '</td>';
			$output .= '<td>' . $contactData['firstname'] . '</td>';
			$output .= '<td>' . $contactData['businessemail'] . '</td>';

			if(!$debug) {

				// Saving the contact
				// Check if the contact already exists by searching for a matching email address.
				// If a match is found update the existing contact, otherwise create a new one.


				// reformat categories, remove note
				$contactData['categories'] = array(
					'add' => array('category' => $contactData['categoriestoadd'])
				);
				unset($contactData['categoriestoadd']);

				if($has_note) {
					$note = $contactData['note'];
					unset($contactData['note']);
				}

				$contacts = $solve360Service->searchContacts(array(
					'filtermode' => 'byemail',
					'filtervalue' => $contactData['businessemail'],
				));
				if ((integer) $contacts->count > 0) {
					$contactId = (integer) current($contacts->children())->id;
					$contactName = (string) current($contacts->children())->name;
					$contact = $solve360Service->editContact($contactId, $contactData);

					$solve360contactstatus = 'Updated';
					$output .= '<td>' . $solve360contactstatus  .'</td>';
				} else {
					$contact = $solve360Service->addContact($contactData);
					$contactName = (string) $contact->item->name;
					$contactId   = (integer) $contact->item->id;

					$solve360contactstatus = 'Added';
					$output .= '<td>' . $solve360contactstatus  .'</td>';
				}

				// OPTION Adding a activity 

				if($has_note) {

					// Preparing data for the note
					$noteData = array(
					'details' => nl2br($note)
					);

					$note = $solve360Service->addActivity($contactId, 'note', $noteData);

				}

				// add "Add a view for linked emails" activity
				$aavfle = $solve360Service->addActivity($contactId, 'linkedemails', '');

				// Notification emails

				$to = get_option('gravity_to_solve360_to');
				$from = get_option('gravity_to_solve360_from');
				$cc .= get_option('gravity_to_solve360_cc');
				$bcc .= get_option('gravity_to_solve360_bcc');

				$headers = 'From: ' . $from . "\r\n";
				if($cc) $headers .= 'Cc: ' . $cc . "\r\n";
				if($bcc) $headers .= 'Bcc: ' . $bcc . "\r\n";

				if (isset($contact->errors)) {
				mail(
					$to, 
					'Error while adding contact to Solve360', 
					'Error: ' . $contact->errors->asXml(),
					$headers
				);
				die ('System error');
				} else {
				mail(
					$to, 
					'Solve360: contact ' . $contactName . ' ' . $solve360contactstatus, 
					'Contact ' . $contactName . ' ' . $solve360contactstatus . ': https://secure.solve360.com/contact/' . $contactId,
					$headers
				);
				}

			} // if(!$debug)

			$output .= '</tr>';
		} // foreach ($contactstosend as $contactData)

		if(count($contactstosend) > 0) {
		$output .= '
			</tbody>
		</table>
		';
		}
		
		if(!$debug) {
		update_option('gravity_to_solve360_last_export_date', $end_date);
		}

	} // if(!$errors) required fields
	$errors = '';

} // if(!$errors) settings

$output = $modeoutput . $output;

$output .=  $errors;

$output .= '</div>';

echo $output;