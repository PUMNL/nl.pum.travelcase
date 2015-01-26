<?php

/**
 * InfoForDsa.Autofill API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_info_for_dsa_autofill_spec(&$spec) {
  
}

/**
 * InfoForDsa.Autofill API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_info_for_dsa_autofill($params) {
  $returnValues = array();

  $limit = !empty($params['limit']) ? $params['limit'] : 1000;
  $debug = isset($params['debug']) ? $params['debug'] : false;
  
  $autofill = new CRM_Travelcase_InfoForDsa_Autofill();
  $autofill->run($limit, $debug);
  
  return civicrm_api3_create_success($returnValues, $params, 'NewEntity', 'NewAction');
}

