<?php

$ta_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'info_for_travel_agency'));
$passport_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Passport_Information'));
$flight_pref = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Flight_details'));

$dep_date = civicrm_api3('CustomField', 'getvalue', array('name' => 'requested_departure_date', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$return_date = civicrm_api3('CustomField', 'getvalue', array('name' => 'requested_return_date', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$destination = civicrm_api3('CustomField', 'getvalue', array('name' => 'destination', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$request_for_booking = civicrm_api3('CustomField', 'getvalue', array('name' => 'request_for_booking', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$notes = civicrm_api3('CustomField', 'getvalue', array('name' => 'notes', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$invoice = civicrm_api3('CustomField', 'getvalue', array('name' => 'invoice_info', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));

$seat = civicrm_api3('CustomField', 'getvalue', array('name' => 'Seat', 'return' => 'column_name', 'custom_group_id' => $flight_pref['id']));
$diet = civicrm_api3('CustomField', 'getvalue', array('name' => 'Diet_preference', 'return' => 'column_name', 'custom_group_id' => $flight_pref['id']));

$name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$last_name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name_Last_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$passport_number = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Number', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$passport_valid = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Valid_until', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$partner_name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name_Partner_Last_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));

$case_coordinator_rel_type_id = civicrm_api3('RelationshipType', 'getvalue', array('name_a_b' => 'Case Coordinator is', 'return' => 'id'));

$view = new view();
$view->name = 'travel_request_detail_for_bcd_';
$view->description = '';
$view->tag = 'Travel case';
$view->base_table = 'civicrm_case';
$view->human_name = 'Travel request details (for BCD)';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Travel request';
$handler->display->display_options['css_class'] = 'pum-list-view';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'role';
$handler->display->display_options['access']['role'] = travelcase_get_role_ids(array('BCD'));
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '1';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Relationship: CiviCRM Case Contact: Case Contact's Contact ID */
$handler->display->display_options['relationships']['contact_id']['id'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['table'] = 'civicrm_case_contact';
$handler->display->display_options['relationships']['contact_id']['field'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['label'] = 'Client';
$handler->display->display_options['relationships']['contact_id']['required'] = TRUE;
/* Relationship: CiviCRM Cases: CiviCRM Relationship (starting from contact case ID) */
$handler->display->display_options['relationships']['relationship_id']['id'] = 'relationship_id';
$handler->display->display_options['relationships']['relationship_id']['table'] = 'civicrm_case';
$handler->display->display_options['relationships']['relationship_id']['field'] = 'relationship_id';
$handler->display->display_options['relationships']['relationship_id']['label'] = 'Coordinator relationship';
$handler->display->display_options['relationships']['relationship_id']['relationship_type'] = $case_coordinator_rel_type_id;
/* Relationship: CiviCRM Relationships: Contact ID B */
$handler->display->display_options['relationships']['contact_id_b_']['id'] = 'contact_id_b_';
$handler->display->display_options['relationships']['contact_id_b_']['table'] = 'civicrm_relationship';
$handler->display->display_options['relationships']['contact_id_b_']['field'] = 'contact_id_b_';
$handler->display->display_options['relationships']['contact_id_b_']['relationship'] = 'relationship_id';
$handler->display->display_options['relationships']['contact_id_b_']['label'] = 'Coordinator';
/* Field: CiviCRM Cases: Case ID */
$handler->display->display_options['fields']['id']['id'] = 'id';
$handler->display->display_options['fields']['id']['table'] = 'civicrm_case';
$handler->display->display_options['fields']['id']['field'] = 'id';
$handler->display->display_options['fields']['id']['label'] = '';
$handler->display->display_options['fields']['id']['exclude'] = TRUE;
$handler->display->display_options['fields']['id']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['id']['separator'] = '';
/* Field: CiviCRM Custom: Info for travel agency: Destination */
$handler->display->display_options['fields']['destination']['id'] = 'destination';
$handler->display->display_options['fields']['destination']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['destination']['field'] = $destination;
$handler->display->display_options['fields']['destination']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['destination']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Info for travel agency: Requested departure date */
$handler->display->display_options['fields']['requested_departure_date']['id'] = 'requested_departure_date';
$handler->display->display_options['fields']['requested_departure_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_departure_date']['field'] = $dep_date;
$handler->display->display_options['fields']['requested_departure_date']['label'] = 'Departure date';
$handler->display->display_options['fields']['requested_departure_date']['date_format'] = 'd_m_y';
$handler->display->display_options['fields']['requested_departure_date']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Info for travel agency: Requested return date */
$handler->display->display_options['fields']['requested_return_date']['id'] = 'requested_return_date';
$handler->display->display_options['fields']['requested_return_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_return_date']['field'] = $return_date;
$handler->display->display_options['fields']['requested_return_date']['label'] = 'Return date';
$handler->display->display_options['fields']['requested_return_date']['date_format'] = 'd_m_y';
$handler->display->display_options['fields']['requested_return_date']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Passport Information: Passport Name - First Name(s) */
$handler->display->display_options['fields']['passport_name_60']['id'] = 'passport_name_60';
$handler->display->display_options['fields']['passport_name_60']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_60']['field'] = $name;
$handler->display->display_options['fields']['passport_name_60']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_60']['label'] = 'First Name(s)';
$handler->display->display_options['fields']['passport_name_60']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['passport_name_60']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Passport Information: Passport Name - Last Name(s) */
$handler->display->display_options['fields']['passport_name_last_name_224']['id'] = 'passport_name_last_name_224';
$handler->display->display_options['fields']['passport_name_last_name_224']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_last_name_224']['field'] = $last_name;
$handler->display->display_options['fields']['passport_name_last_name_224']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_last_name_224']['label'] = 'Last Name(s)';
$handler->display->display_options['fields']['passport_name_last_name_224']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['passport_name_last_name_224']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Passport Information: Passport Name - Partner Last Name */
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['id'] = 'passport_name_partner_last_name_534';
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['field'] = $partner_name;
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['label'] = 'Partner Last Name';
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Passport Information: Passport Number */
$handler->display->display_options['fields']['passport_number_225']['id'] = 'passport_number_225';
$handler->display->display_options['fields']['passport_number_225']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_number_225']['field'] = $passport_number;
$handler->display->display_options['fields']['passport_number_225']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_number_225']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['passport_number_225']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Passport Information: Passport Expiry Date */
$handler->display->display_options['fields']['passport_valid_until_226']['id'] = 'passport_valid_until_226';
$handler->display->display_options['fields']['passport_valid_until_226']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_valid_until_226']['field'] = $passport_valid;
$handler->display->display_options['fields']['passport_valid_until_226']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_valid_until_226']['date_format'] = 'd_m_y';
$handler->display->display_options['fields']['passport_valid_until_226']['element_label_type'] = 'strong';
/* Field: CiviCRM Email: Email Address */
$handler->display->display_options['fields']['email_1']['id'] = 'email_1';
$handler->display->display_options['fields']['email_1']['table'] = 'civicrm_email';
$handler->display->display_options['fields']['email_1']['field'] = 'email';
$handler->display->display_options['fields']['email_1']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['email_1']['location_type'] = '0';
$handler->display->display_options['fields']['email_1']['location_op'] = '0';
$handler->display->display_options['fields']['email_1']['is_primary'] = 0;
$handler->display->display_options['fields']['email_1']['link_to_user'] = '0';
$handler->display->display_options['fields']['email_1']['element_label_type'] = 'strong';
/* Field: CiviCRM Address: Full Street Address */
$handler->display->display_options['fields']['street_address']['id'] = 'street_address';
$handler->display->display_options['fields']['street_address']['table'] = 'civicrm_address';
$handler->display->display_options['fields']['street_address']['field'] = 'street_address';
$handler->display->display_options['fields']['street_address']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['street_address']['label'] = 'Address';
$handler->display->display_options['fields']['street_address']['location_type'] = '0';
$handler->display->display_options['fields']['street_address']['location_op'] = '0';
$handler->display->display_options['fields']['street_address']['is_primary'] = 1;
$handler->display->display_options['fields']['street_address']['is_billing'] = 0;
$handler->display->display_options['fields']['street_address']['element_label_type'] = 'strong';
/* Field: CiviCRM Address: ZIP / Postal Code */
$handler->display->display_options['fields']['postal_code']['id'] = 'postal_code';
$handler->display->display_options['fields']['postal_code']['table'] = 'civicrm_address';
$handler->display->display_options['fields']['postal_code']['field'] = 'postal_code';
$handler->display->display_options['fields']['postal_code']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['postal_code']['label'] = 'Postal Code';
$handler->display->display_options['fields']['postal_code']['location_type'] = '0';
$handler->display->display_options['fields']['postal_code']['location_op'] = '0';
$handler->display->display_options['fields']['postal_code']['is_primary'] = 1;
$handler->display->display_options['fields']['postal_code']['is_billing'] = 0;
$handler->display->display_options['fields']['postal_code']['element_label_type'] = 'strong';
/* Field: CiviCRM Address: City / Suburb */
$handler->display->display_options['fields']['city']['id'] = 'city';
$handler->display->display_options['fields']['city']['table'] = 'civicrm_address';
$handler->display->display_options['fields']['city']['field'] = 'city';
$handler->display->display_options['fields']['city']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['city']['label'] = 'City';
$handler->display->display_options['fields']['city']['location_type'] = '0';
$handler->display->display_options['fields']['city']['location_op'] = '0';
$handler->display->display_options['fields']['city']['is_primary'] = 0;
$handler->display->display_options['fields']['city']['is_billing'] = 0;
$handler->display->display_options['fields']['city']['element_label_type'] = 'strong';
/* Field: CiviCRM Address: Country */
$handler->display->display_options['fields']['country']['id'] = 'country';
$handler->display->display_options['fields']['country']['table'] = 'civicrm_address';
$handler->display->display_options['fields']['country']['field'] = 'country';
$handler->display->display_options['fields']['country']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['country']['location_type'] = '0';
$handler->display->display_options['fields']['country']['location_op'] = '0';
$handler->display->display_options['fields']['country']['is_primary'] = 1;
$handler->display->display_options['fields']['country']['is_billing'] = 0;
$handler->display->display_options['fields']['country']['country_display'] = '2';
$handler->display->display_options['fields']['country']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Flight Preferences: Diet preference */
$handler->display->display_options['fields']['diet_preference_641']['id'] = 'diet_preference_641';
$handler->display->display_options['fields']['diet_preference_641']['table'] = $flight_pref['table_name'];
$handler->display->display_options['fields']['diet_preference_641']['field'] = $diet;
$handler->display->display_options['fields']['diet_preference_641']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['diet_preference_641']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Flight Preferences: Seat */
$handler->display->display_options['fields']['seat_640']['id'] = 'seat_640';
$handler->display->display_options['fields']['seat_640']['table'] = $flight_pref['table_name'];
$handler->display->display_options['fields']['seat_640']['field'] = $seat;
$handler->display->display_options['fields']['seat_640']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['seat_640']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Info for travel agency: Invoice Info */
$handler->display->display_options['fields']['invoice']['id'] = 'invoice';
$handler->display->display_options['fields']['invoice']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['invoice']['field'] = $invoice;
$handler->display->display_options['fields']['invoice']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['invoice']['element_label_type'] = 'strong';
/* Field: sponsor code */
$handler->display->display_options['fields']['sponsor_code']['id'] = 'sponsor_code';
$handler->display->display_options['fields']['sponsor_code']['table'] = 'views';
$handler->display->display_options['fields']['sponsor_code']['field'] = 'php';
$handler->display->display_options['fields']['sponsor_code']['ui_name'] = 'sponsor_code';
$handler->display->display_options['fields']['sponsor_code']['label'] = '';
$handler->display->display_options['fields']['sponsor_code']['exclude'] = TRUE;
$handler->display->display_options['fields']['sponsor_code']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['sponsor_code']['use_php_setup'] = 0;
$handler->display->display_options['fields']['sponsor_code']['php_value'] = 'return _travelcase_get_fa_sponsor_code($row->id);';
$handler->display->display_options['fields']['sponsor_code']['use_php_click_sortable'] = '0';
$handler->display->display_options['fields']['sponsor_code']['php_click_sortable'] = '';
/* Field: sponsor display name */
$handler->display->display_options['fields']['sponsor_name']['id'] = 'sponsor_name';
$handler->display->display_options['fields']['sponsor_name']['table'] = 'views';
$handler->display->display_options['fields']['sponsor_name']['field'] = 'php';
$handler->display->display_options['fields']['sponsor_name']['ui_name'] = 'sponsor_name';
$handler->display->display_options['fields']['sponsor_name']['label'] = '';
$handler->display->display_options['fields']['sponsor_name']['exclude'] = TRUE;
$handler->display->display_options['fields']['sponsor_name']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['sponsor_name']['use_php_setup'] = 0;
$handler->display->display_options['fields']['sponsor_name']['php_value'] = 'return _travelcase_get_fa_sponsor_display_name($row->id);';
$handler->display->display_options['fields']['sponsor_name']['use_php_click_sortable'] = '0';
$handler->display->display_options['fields']['sponsor_name']['php_click_sortable'] = '';
/* Field: Sponsor */
$handler->display->display_options['fields']['display_name_1']['id'] = 'display_name_1';
$handler->display->display_options['fields']['display_name_1']['table'] = 'views';
$handler->display->display_options['fields']['display_name_1']['field'] = 'nothing';
$handler->display->display_options['fields']['display_name_1']['ui_name'] = 'Sponsor';
$handler->display->display_options['fields']['display_name_1']['label'] = 'Sponsor';
$handler->display->display_options['fields']['display_name_1']['alter']['alter_text'] = TRUE;
$handler->display->display_options['fields']['display_name_1']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['display_name_1']['alter']['text'] = '[sponsor_code]  - [sponsor_name]';
$handler->display->display_options['fields']['display_name_1']['element_label_type'] = 'strong';
/* Field: CiviCRM Custom: Info for travel agency: Remarks for travel agency */
$handler->display->display_options['fields']['notes']['id'] = 'notes';
$handler->display->display_options['fields']['notes']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['notes']['field'] = $notes;
$handler->display->display_options['fields']['notes']['element_label_type'] = 'strong';
/* Field: CiviCRM Contacts: Display Name */
$handler->display->display_options['fields']['display_name']['id'] = 'display_name';
$handler->display->display_options['fields']['display_name']['table'] = 'civicrm_contact';
$handler->display->display_options['fields']['display_name']['field'] = 'display_name';
$handler->display->display_options['fields']['display_name']['relationship'] = 'contact_id_b_';
$handler->display->display_options['fields']['display_name']['label'] = 'Proj. officer';
$handler->display->display_options['fields']['display_name']['link_to_civicrm_contact'] = 0;
$handler->display->display_options['fields']['display_name']['element_label_type'] = 'strong';
/* Field: CiviCRM Email: Email Address */
$handler->display->display_options['fields']['email']['id'] = 'email';
$handler->display->display_options['fields']['email']['table'] = 'civicrm_email';
$handler->display->display_options['fields']['email']['field'] = 'email';
$handler->display->display_options['fields']['email']['relationship'] = 'contact_id_b_';
$handler->display->display_options['fields']['email']['label'] = 'E-mail (Proj. officer)';
$handler->display->display_options['fields']['email']['location_type'] = '0';
$handler->display->display_options['fields']['email']['location_op'] = '0';
$handler->display->display_options['fields']['email']['is_primary'] = 1;
$handler->display->display_options['fields']['email']['element_label_type'] = 'strong';
/* Field: CiviCRM Phone Details: Phone Number */
$handler->display->display_options['fields']['phone']['id'] = 'phone';
$handler->display->display_options['fields']['phone']['table'] = 'civicrm_phone';
$handler->display->display_options['fields']['phone']['field'] = 'phone';
$handler->display->display_options['fields']['phone']['relationship'] = 'contact_id_b_';
$handler->display->display_options['fields']['phone']['label'] = 'Phone (Proj. officer)';
$handler->display->display_options['fields']['phone']['location_type'] = '0';
$handler->display->display_options['fields']['phone']['location_op'] = '0';
$handler->display->display_options['fields']['phone']['is_primary'] = 1;
$handler->display->display_options['fields']['phone']['phone_type'] = '0';
$handler->display->display_options['fields']['phone']['element_label_type'] = 'strong';
/* Sort criterion: CiviCRM Custom: Info for travel agency: Requested departure date */
$handler->display->display_options['sorts']['requested_departure_date']['id'] = 'requested_departure_date';
$handler->display->display_options['sorts']['requested_departure_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['sorts']['requested_departure_date']['field'] = $dep_date;
$handler->display->display_options['sorts']['requested_departure_date']['granularity'] = 'day';
/* Sort criterion: CiviCRM Custom: Info for travel agency: Requested return date */
$handler->display->display_options['sorts']['requested_return_date']['id'] = 'requested_return_date';
$handler->display->display_options['sorts']['requested_return_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['sorts']['requested_return_date']['field'] = $return_date;
$handler->display->display_options['sorts']['requested_return_date']['granularity'] = 'day';
/* Contextual filter: CiviCRM Cases: Case ID */
$handler->display->display_options['arguments']['id']['id'] = 'id';
$handler->display->display_options['arguments']['id']['table'] = 'civicrm_case';
$handler->display->display_options['arguments']['id']['field'] = 'id';
$handler->display->display_options['arguments']['id']['default_action'] = 'not found';
$handler->display->display_options['arguments']['id']['default_argument_type'] = 'fixed';
$handler->display->display_options['arguments']['id']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['id']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['id']['summary_options']['items_per_page'] = '25';
/* Filter criterion: CiviCRM Custom: Info for travel agency: Request travel agency to book ticket */
$handler->display->display_options['filters']['request_for_booking']['id'] = 'request_for_booking';
$handler->display->display_options['filters']['request_for_booking']['table'] = $ta_info['table_name'];
$handler->display->display_options['filters']['request_for_booking']['field'] = $request_for_booking;
$handler->display->display_options['filters']['request_for_booking']['value'] = '1';

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page');
$handler->display->display_options['path'] = 'bcd/travel-request';
$translatables['travel_requests_for_bcd_'] = array(
  t('Master'),
  t('Travel requests'),
  t('more'),
  t('Apply'),
  t('Reset'),
  t('Sort by'),
  t('Asc'),
  t('Desc'),
  t('Items per page'),
  t('- All -'),
  t('Offset'),
  t('« first'),
  t('‹ previous'),
  t('next ›'),
  t('last »'),
  t('Client'),
  t('Destination'),
  t('Departure date'),
  t('Return date'),
  t('First Name(s)'),
  t('Last Name(s)'),
  t('Partner Last Name'),
  t('Passport Number'),
  t('Passport Expiry Date'),
  t('Diet preference'),
  t('Seat'),
  t('Remarks for travel agency'),
  t('Page'),
);