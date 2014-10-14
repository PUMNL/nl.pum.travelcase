<?php

$ta_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'info_for_travel_agency'));
$passport_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Passport_Information'));
$flight_pref = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Flight_details'));

$dep_date = civicrm_api3('CustomField', 'getvalue', array('name' => 'requested_departure_date', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$return_date = civicrm_api3('CustomField', 'getvalue', array('name' => 'requested_return_date', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$destination = civicrm_api3('CustomField', 'getvalue', array('name' => 'destination', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$request_for_booking = civicrm_api3('CustomField', 'getvalue', array('name' => 'request_for_booking', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));
$notes = civicrm_api3('CustomField', 'getvalue', array('name' => 'notes', 'return' => 'column_name', 'custom_group_id' => $ta_info['id']));

$seat = civicrm_api3('CustomField', 'getvalue', array('name' => 'Seat', 'return' => 'column_name', 'custom_group_id' => $flight_pref['id']));
$diet = civicrm_api3('CustomField', 'getvalue', array('name' => 'Diet_preference', 'return' => 'column_name', 'custom_group_id' => $flight_pref['id']));

$name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$last_name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name_Last_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$passport_number = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Number', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$passport_valid = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Valid_until', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));
$partner_name = civicrm_api3('CustomField', 'getvalue', array('name' => 'Passport_Name_Partner_Last_Name', 'return' => 'column_name', 'custom_group_id' => $passport_info['id']));

$view = new view();
$view->name = 'travel_requests_for_bcd_';
$view->description = '';
$view->tag = 'Travel case';
$view->base_table = 'civicrm_case';
$view->human_name = 'Travel requests (for BCD)';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Travel requests';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'role';
$handler->display->display_options['access']['role'] = travelcase_get_role_ids(array('BCD'));
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '10';
$handler->display->display_options['style_plugin'] = 'table';
/* Relationship: CiviCRM Case Contact: Case Contact's Contact ID */
$handler->display->display_options['relationships']['contact_id']['id'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['table'] = 'civicrm_case_contact';
$handler->display->display_options['relationships']['contact_id']['field'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['label'] = 'Client';
$handler->display->display_options['relationships']['contact_id']['required'] = TRUE;
/* Field: CiviCRM Custom: Info for travel agency: Destination */
$handler->display->display_options['fields']['destination']['id'] = 'destination';
$handler->display->display_options['fields']['destination']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['destination']['field'] = $destination;
$handler->display->display_options['fields']['destination']['text_format'] = 'full_html';
/* Field: CiviCRM Custom: Info for travel agency: Requested departure date */
$handler->display->display_options['fields']['requested_departure_date']['id'] = 'requested_departure_date';
$handler->display->display_options['fields']['requested_departure_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_departure_date']['field'] = $dep_date;
$handler->display->display_options['fields']['requested_departure_date']['label'] = 'Departure date';
$handler->display->display_options['fields']['requested_departure_date']['date_format'] = 'd_m_y';
/* Field: CiviCRM Custom: Info for travel agency: Requested return date */
$handler->display->display_options['fields']['requested_return_date']['id'] = 'requested_return_date';
$handler->display->display_options['fields']['requested_return_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_return_date']['field'] = $return_date;
$handler->display->display_options['fields']['requested_return_date']['label'] = 'Return date';
$handler->display->display_options['fields']['requested_return_date']['date_format'] = 'd_m_y';
/* Field: CiviCRM Custom: Passport Information: Passport Name - First Name(s) */
$handler->display->display_options['fields']['passport_name_60']['id'] = 'passport_name_60';
$handler->display->display_options['fields']['passport_name_60']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_60']['field'] = $name;
$handler->display->display_options['fields']['passport_name_60']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_60']['label'] = 'First Name(s)';
$handler->display->display_options['fields']['passport_name_60']['text_format'] = 'full_html';
/* Field: CiviCRM Custom: Passport Information: Passport Name - Last Name(s) */
$handler->display->display_options['fields']['passport_name_last_name_224']['id'] = 'passport_name_last_name_224';
$handler->display->display_options['fields']['passport_name_last_name_224']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_last_name_224']['field'] = $last_name;
$handler->display->display_options['fields']['passport_name_last_name_224']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_last_name_224']['label'] = 'Last Name(s)';
/* Field: CiviCRM Custom: Passport Information: Passport Name - Partner Last Name */
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['id'] = 'passport_name_partner_last_name_534';
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['field'] = $partner_name;
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_name_partner_last_name_534']['label'] = 'Partner Last Name';
/* Field: CiviCRM Custom: Passport Information: Passport Number */
$handler->display->display_options['fields']['passport_number_225']['id'] = 'passport_number_225';
$handler->display->display_options['fields']['passport_number_225']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_number_225']['field'] = $passport_number;
$handler->display->display_options['fields']['passport_number_225']['relationship'] = 'contact_id';
/* Field: CiviCRM Custom: Passport Information: Passport Expiry Date */
$handler->display->display_options['fields']['passport_valid_until_226']['id'] = 'passport_valid_until_226';
$handler->display->display_options['fields']['passport_valid_until_226']['table'] = $passport_info['table_name'];
$handler->display->display_options['fields']['passport_valid_until_226']['field'] = $passport_valid;
$handler->display->display_options['fields']['passport_valid_until_226']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['passport_valid_until_226']['date_format'] = 'd_m_y';
/* Field: CiviCRM Custom: Flight Preferences: Diet preference */
$handler->display->display_options['fields']['diet_preference_641']['id'] = 'diet_preference_641';
$handler->display->display_options['fields']['diet_preference_641']['table'] = $flight_pref['table_name'];
$handler->display->display_options['fields']['diet_preference_641']['field'] = $diet;
$handler->display->display_options['fields']['diet_preference_641']['relationship'] = 'contact_id';
/* Field: CiviCRM Custom: Flight Preferences: Seat */
$handler->display->display_options['fields']['seat_640']['id'] = 'seat_640';
$handler->display->display_options['fields']['seat_640']['table'] = $flight_pref['table_name'];
$handler->display->display_options['fields']['seat_640']['field'] = $seat;
$handler->display->display_options['fields']['seat_640']['relationship'] = 'contact_id';
/* Field: CiviCRM Custom: Info for travel agency: Remarks for travel agency */
$handler->display->display_options['fields']['notes']['id'] = 'notes';
$handler->display->display_options['fields']['notes']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['notes']['field'] = $notes;
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
/* Filter criterion: CiviCRM Custom: Info for travel agency: Request travel agency to book ticket */
$handler->display->display_options['filters']['request_for_booking']['id'] = 'request_for_booking';
$handler->display->display_options['filters']['request_for_booking']['table'] = $ta_info['table_name'];
$handler->display->display_options['filters']['request_for_booking']['field'] = $request_for_booking;
$handler->display->display_options['filters']['request_for_booking']['value'] = '1';

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page');
$handler->display->display_options['path'] = 'bcd/travel-requests';
$handler->display->display_options['menu']['type'] = 'normal';
$handler->display->display_options['menu']['title'] = 'Travel requests';
$handler->display->display_options['menu']['weight'] = '0';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;
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
