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
$handler->display->display_options['style_options']['columns'] = array(
  'id' => 'id',
  'display_name' => 'display_name',
  'destination' => 'destination',
  'requested_departure_date' => 'requested_departure_date',
  'requested_return_date' => 'requested_return_date',
);
$handler->display->display_options['style_options']['default'] = 'requested_departure_date';
$handler->display->display_options['style_options']['info'] = array(
  'id' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'display_name' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'destination' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'requested_departure_date' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'requested_return_date' => array(
    'sortable' => 1,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
);
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['label'] = 'Explenation';
$handler->display->display_options['empty']['area']['empty'] = TRUE;
$handler->display->display_options['empty']['area']['content'] = 'There are no travel requests';
$handler->display->display_options['empty']['area']['format'] = 'filtered_html';
/* Relationship: CiviCRM Case Contact: Case Contact's Contact ID */
$handler->display->display_options['relationships']['contact_id']['id'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['table'] = 'civicrm_case_contact';
$handler->display->display_options['relationships']['contact_id']['field'] = 'contact_id';
$handler->display->display_options['relationships']['contact_id']['label'] = 'Client';
$handler->display->display_options['relationships']['contact_id']['required'] = TRUE;
/* Field: CiviCRM Cases: Case ID */
$handler->display->display_options['fields']['id']['id'] = 'id';
$handler->display->display_options['fields']['id']['table'] = 'civicrm_case';
$handler->display->display_options['fields']['id']['field'] = 'id';
$handler->display->display_options['fields']['id']['label'] = '';
$handler->display->display_options['fields']['id']['exclude'] = TRUE;
$handler->display->display_options['fields']['id']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['id']['separator'] = '';
/* Field: CiviCRM Cases: Contact ID */
$handler->display->display_options['fields']['contact_id']['id'] = 'contact_id';
$handler->display->display_options['fields']['contact_id']['table'] = 'civicrm_contact';
$handler->display->display_options['fields']['contact_id']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['contact_id']['field'] = 'id';
$handler->display->display_options['fields']['contact_id']['label'] = '';
$handler->display->display_options['fields']['contact_id']['exclude'] = TRUE;
$handler->display->display_options['fields']['contact_id']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['contact_id']['separator'] = '';
/* Field: CiviCRM Contact: Display Name*/
$handler->display->display_options['fields']['display_name']['id'] = 'display_name';
$handler->display->display_options['fields']['display_name']['table'] = 'civicrm_contact';
$handler->display->display_options['fields']['display_name']['field'] = 'display_name';
$handler->display->display_options['fields']['display_name']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['display_name']['label'] = 'Client';
$handler->display->display_options['fields']['display_name']['alter']['make_link'] = TRUE;
$handler->display->display_options['fields']['display_name']['alter']['path'] = 'bcd/travel-request/[id]';
$handler->display->display_options['fields']['display_name']['alter']['absolute'] = TRUE;
$handler->display->display_options['fields']['display_name']['link_to_civicrm_contact'] = 0;
/* Field: CiviCRM Custom: Info for travel agency: Destination */
$handler->display->display_options['fields']['destination']['id'] = 'destination';
$handler->display->display_options['fields']['destination']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['destination']['field'] = $destination;
$handler->display->display_options['fields']['destination']['alter']['strip_tags'] = TRUE;
/* Field: CiviCRM Custom: Info for travel agency: Requested departure date */
$handler->display->display_options['fields']['requested_departure_date']['id'] = 'requested_departure_date';
$handler->display->display_options['fields']['requested_departure_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_departure_date']['field'] = $dep_date;
$handler->display->display_options['fields']['requested_departure_date']['label'] = 'Departure date';
$handler->display->display_options['fields']['requested_departure_date']['date_format'] = 'custom';
$handler->display->display_options['fields']['requested_departure_date']['custom_date_format'] = 'd-m-Y';
$handler->display->display_options['fields']['requested_departure_date']['second_date_format'] = 'long';
/* Field: CiviCRM Custom: Info for travel agency: Requested return date */
$handler->display->display_options['fields']['requested_return_date']['id'] = 'requested_return_date';
$handler->display->display_options['fields']['requested_return_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_return_date']['field'] = $return_date;
$handler->display->display_options['fields']['requested_return_date']['label'] = 'Return date';
$handler->display->display_options['fields']['requested_return_date']['date_format'] = 'custom';
$handler->display->display_options['fields']['requested_return_date']['custom_date_format'] = 'd-m-Y';
$handler->display->display_options['fields']['requested_return_date']['second_date_format'] = 'long';
/* Field: Global: Custom text */
$handler->display->display_options['fields']['nothing']['id'] = 'nothing';
$handler->display->display_options['fields']['nothing']['table'] = 'views';
$handler->display->display_options['fields']['nothing']['field'] = 'nothing';
$handler->display->display_options['fields']['nothing']['label'] = '';
$handler->display->display_options['fields']['nothing']['alter']['text'] = 'Remove from list';
$handler->display->display_options['fields']['nothing']['alter']['make_link'] = TRUE;
$handler->display->display_options['fields']['nothing']['alter']['path'] = 'bcd/remove-travel-request/[contact_id]/[id]';
$handler->display->display_options['fields']['nothing']['alter']['absolute'] = TRUE;
$handler->display->display_options['fields']['nothing']['element_label_colon'] = FALSE;
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
/* Display: My Work Count Item - Block */
$handler = $view->new_display('block', 'My Work Count Item - Block', 'block_1');
$handler->display->display_options['defaults']['pager'] = FALSE;
$handler->display->display_options['pager']['type'] = 'none';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['defaults']['style_plugin'] = FALSE;
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['defaults']['style_options'] = FALSE;
$handler->display->display_options['defaults']['row_plugin'] = FALSE;
$handler->display->display_options['row_plugin'] = 'fields';
$handler->display->display_options['defaults']['row_options'] = FALSE;
$handler->display->display_options['defaults']['header'] = FALSE;
/* Header: Global: Result summary */
$handler->display->display_options['header']['result']['id'] = 'result';
$handler->display->display_options['header']['result']['table'] = 'views';
$handler->display->display_options['header']['result']['field'] = 'result';
$handler->display->display_options['header']['result']['content'] = '<a href="/bcd/travel-requests">Travel requests (@total)</a>';
$handler->display->display_options['defaults']['empty'] = FALSE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: CiviCRM Cases: Case ID */
$handler->display->display_options['fields']['id']['id'] = 'id';
$handler->display->display_options['fields']['id']['table'] = 'civicrm_case';
$handler->display->display_options['fields']['id']['field'] = 'id';
$handler->display->display_options['fields']['id']['label'] = '';
$handler->display->display_options['fields']['id']['exclude'] = TRUE;
$handler->display->display_options['fields']['id']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['id']['separator'] = '';
/* Field: CiviCRM Contacts: Contact ID */
$handler->display->display_options['fields']['contact_id']['id'] = 'contact_id';
$handler->display->display_options['fields']['contact_id']['table'] = 'civicrm_contact';
$handler->display->display_options['fields']['contact_id']['field'] = 'id';
$handler->display->display_options['fields']['contact_id']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['contact_id']['label'] = '';
$handler->display->display_options['fields']['contact_id']['exclude'] = TRUE;
$handler->display->display_options['fields']['contact_id']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['contact_id']['separator'] = '';
/* Field: CiviCRM Contacts: Display Name */
$handler->display->display_options['fields']['display_name']['id'] = 'display_name';
$handler->display->display_options['fields']['display_name']['table'] = 'civicrm_contact';
$handler->display->display_options['fields']['display_name']['field'] = 'display_name';
$handler->display->display_options['fields']['display_name']['relationship'] = 'contact_id';
$handler->display->display_options['fields']['display_name']['label'] = 'Client';
$handler->display->display_options['fields']['display_name']['exclude'] = TRUE;
$handler->display->display_options['fields']['display_name']['alter']['make_link'] = TRUE;
$handler->display->display_options['fields']['display_name']['alter']['path'] = 'bcd/travel-request/[id]';
$handler->display->display_options['fields']['display_name']['alter']['absolute'] = TRUE;
$handler->display->display_options['fields']['display_name']['link_to_civicrm_contact'] = 0;
/* Field: CiviCRM Custom: Info for travel agency: Destination */
$handler->display->display_options['fields']['destination']['id'] = 'destination';
$handler->display->display_options['fields']['destination']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['destination']['field'] = $destination;
$handler->display->display_options['fields']['destination']['exclude'] = TRUE;
$handler->display->display_options['fields']['destination']['alter']['strip_tags'] = TRUE;
/* Field: CiviCRM Custom: Info for travel agency: Requested departure date */
$handler->display->display_options['fields']['requested_departure_date']['id'] = 'requested_departure_date';
$handler->display->display_options['fields']['requested_departure_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_departure_date']['field'] = $dep_date;
$handler->display->display_options['fields']['requested_departure_date']['label'] = 'Departure date';
$handler->display->display_options['fields']['requested_departure_date']['exclude'] = TRUE;
$handler->display->display_options['fields']['requested_departure_date']['date_format'] = 'long';
$handler->display->display_options['fields']['requested_departure_date']['second_date_format'] = 'long';
/* Field: CiviCRM Custom: Info for travel agency: Requested return date */
$handler->display->display_options['fields']['requested_return_date']['id'] = 'requested_return_date';
$handler->display->display_options['fields']['requested_return_date']['table'] = $ta_info['table_name'];
$handler->display->display_options['fields']['requested_return_date']['field'] = $return_date;
$handler->display->display_options['fields']['requested_return_date']['label'] = 'Return date';
$handler->display->display_options['fields']['requested_return_date']['exclude'] = TRUE;
$handler->display->display_options['fields']['requested_return_date']['date_format'] = 'long';
$handler->display->display_options['fields']['requested_return_date']['second_date_format'] = 'long';
/* Field: Global: Custom text */
$handler->display->display_options['fields']['nothing']['id'] = 'nothing';
$handler->display->display_options['fields']['nothing']['table'] = 'views';
$handler->display->display_options['fields']['nothing']['field'] = 'nothing';
$handler->display->display_options['fields']['nothing']['label'] = '';
$handler->display->display_options['fields']['nothing']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['nothing']['hide_empty'] = TRUE;

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
