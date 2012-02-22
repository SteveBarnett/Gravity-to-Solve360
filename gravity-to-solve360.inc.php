<?php

$debug = true;
// $debug = false;

// Solve360 details

// REQUIRED Edit with the email address you login to Solve360 with
$gravity_to_solve360_user = 'xxxx@xxxx';
// REQUIRED Edit with token, Workspace > My Account > API Reference > API Token
$gravity_to_solve360_token = 'xxxx';

// Date options

// Overrides for debugging
// $debug_start_date = '2012-02-12 10:30:00';
// $debug_end_date = '2012-01-02 00:00:00';

// Notification emails

$to = 'xxxx@xxxx';
$from = 'Gravity to Solve360 Export <xxxx@xxxx>';
// $cc .= 'Name <xxxx@xxxx>';
// $bcc .= 'XXXX <xxxx@xxxx>';

$output = '<h3>Gravity to Solve360</h3>';

$errors = '';


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
	$output .= '<h2>Debug mode</h2>';
	}
	else
	{
	$output .= '<h2>Live mode</h2>';
	}

	if(!$debug) {
		// Configure service gateway object
		require 'Solve360Service.php';
		$solve360Service = new Solve360Service($gravity_to_solve360_user, $gravity_to_solve360_token);
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

	$end_date = $debug_end_date ? $debug_end_date : date('Y-m-d H:i:s');

	$output .= '
	<p>Exporting all Gravity Forms entries between <strong>' . $start_date . '</strong> and <strong>' . $end_date . '</strong>.</p>';


	// Make array of form ids and Solve fields

	$forms_and_fields = array();

	for($form_id=1; $form_id<=100; $form_id++)
	{
		// get form meta - array of form data: id, title, description, fields, etc
		$form_meta = RGFormsModel::get_form_meta($form_id);
		$notenumber = 0;

		if($debug && $form_meta) $output .= '<h4>$form_meta for form ' . $form_id . '</h4>' . print_r($form_meta, true);

		if($form_meta)
		{

			// get field numbers for later use.
			foreach($form_meta['fields'] as &$fields)
			{

				// check adminLabels
				if(stripos($fields['adminLabel'], 'solve360 ') !== false)
				{
					if(stripos($fields['adminLabel'], 'fullname') !== false)
					{
						$forms_and_fields[$form_id]['firstname'] = $fields['id'] . '.3';
						$forms_and_fields[$form_id]['lastname'] = $fields['id'] . '.6';
					}
					elseif(stripos($fields['adminLabel'], 'note') !== false)
					{
						// use the label text as the note name.
						$notetext = str_ireplace('solve360 note ','',$fields['adminLabel']);
						$forms_and_fields[$form_id]['note'][$notenumber]['id'] = $fields['id'];
						$forms_and_fields[$form_id]['note'][$notenumber]['text'] = $notetext;
						$notenumber++;
					}
					elseif(stripos($fields['adminLabel'], 'firstname') !== false || stripos($fields['adminLabel'], 'lastname') !== false || stripos($fields['adminLabel'], 'businessemail') !== false)
					{
						$forms_and_fields[$form_id][str_ireplace('solve360 ','',$fields['adminLabel'])] = $fields['id'];
					}
				}

				// check labels
				if(stripos($fields['label'], 'solve360 ') !== false)
				{
					if(stripos($fields['label'], 'referencenumber') !== false)
					{
						$forms_and_fields[$form_id]['referencenumber'] = $fields['id'];
					}
					elseif(stripos($fields['label'], 'note') !== false)
					{
						$notetext = str_ireplace('solve360 note ','',$fields['label']);
						$forms_and_fields[$form_id]['note'][$notenumber]['id'] = $fields['id'];
						$forms_and_fields[$form_id]['note'][$notenumber]['text'] = $notetext;
						$notenumber++;
					}
					elseif(stripos($fields['label'], 'category') !== false)
					{
						// use the value as the solve tag id
						$forms_and_fields[$form_id]['categoriestoadd'][] = $fields['defaultValue'];
					}
					elseif(stripos($fields['label'], 'ownership') !== false)
					{
						// use the value as the solve tag id
						$forms_and_fields[$form_id]['ownership'] = $fields['defaultValue'];
					}
				}

			}

		}
	}

	if($debug) $output .= '<h4>$forms_and_fields</h4>' . print_r($forms_and_fields, true);

	// Get leads

	$contactstosend = array();
	$solve_lead = 0;


	foreach($forms_and_fields as $id => $details)
	{
		// this is the one that should be working (and is identical to dev). 
		$leads = RGFormsModel::get_leads($id, 0, "DESC", "", 0, 1000, null, null, false, $start_date, $end_date);

		if($leads)
		{

	if($debug) $output .= '<h4>$leads for form ' . $id . '</h4>' . print_r($leads, true);

			foreach($leads as $lead)
			{

				if($lead['date_created'] > $start_date) {

					$contactstosend[$solve_lead] = array(
						'firstname' => $lead[$forms_and_fields[$id]['firstname']],
						'lastname' => $lead[$forms_and_fields[$id]['lastname']],
						'businessemail' => $lead[$forms_and_fields[$id]['businessemail']],
						'cellularphone' => $lead[$forms_and_fields[$id]['cellularphone']],
						'categoriestoadd' => $forms_and_fields[$id]['categoriestoadd'],
					);

					$solve_note = '';
					foreach($details['note'] as $note)
					{
						$solve_note .= $note['text'] . "\n";
						if($note['id'])
						{
							if(RGFormsModel::get_field_value_long($lead['id'], $note['id'])) {
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

					$contactstosend[$solve_lead]['ownership'] = $forms_and_fields[$id]['ownership'];

				$solve_lead++;

				}

			}
		}
	}

	if($debug) $output .= '<h4>$contactstosend</h4>' . print_r($contactstosend, true);


	if(count($contactstosend) > 0) {
	$output .= '
	<h3>Export details</h3>
	<table>
		<thead>
			<th scope="col">Last name</th>
			<th scope="col">First name</th>';

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

		if(!$debug) {

			// Saving the contact
			// Check if the contact already exists by searching for a matching email address.
			// If a match is found update the existing contact, otherwise create a new one.


			// reformat categories, remove note
			$contactData['categories'] = array(
				'add' => array('category' => $contactData['categoriestoadd'])
			);
			unset($contactData['categoriestoadd']);

			$note = $contactData['note'];
			unset($contactData['note']);

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

			// Preparing data for the note
			$noteData = array(
			'details' => nl2br($note)
			);

			$note = $solve360Service->addActivity($contactId, 'note', $noteData);

			// add "Add a view for linked emails" activity
			$aavfle = $solve360Service->addActivity($contactId, 'linkedemails', '');

			// emails
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

	update_option('gravity_to_solve360_last_export_date', $end_date);

} // if(!$errors)


$output .=  $errors;

echo $output;