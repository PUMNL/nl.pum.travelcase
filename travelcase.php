<?php

require_once 'travelcase.civix.php';

/**
 * Display the linked travel case in the case summary
 * 
 * Implementatio of hook_civicrm_caseSummary
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseSummary
 */
function travelcase_civicrm_caseSummary($caseId) {
  $page = new CRM_Travelcase_Page_Case($caseId);
  $content = $page->run();
  $page2 = new CRM_Travelcase_Page_CaseLink($caseId);
  $content2 = $page2->run();
  $page3 = new CRM_Travelcase_Page_ParentCaseRoles($caseId);
  $content3 = $page3->run();
  return array(
    'travelcase_cases' => array('value' => $content),
    'travelcase_linked_to_case' => array('value' => $content2),
    'travelcase_parent_case_roles' => array('value' => $content3),
  );
}

function travelcase_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Case_Form_Case') {
    //set default values
    CRM_Travelcase_Utils_SetDefaultValues::buildForm($formName, $form);
  }
}

function travelcase_civicrm_postSave_civicrm_donor_link($dao) {
  if ($dao->entity == 'Case') {
    CRM_Travelcase_Utils_AddDonorFromParentCase::copyDonorLinkFromCase($dao->entity_id);
  }
}

/**
 * Update invoice number for a case
 * 
 */
function travelcase_civicrm_pre( $op, $objectName, $id, &$params ) {
  if ($objectName == 'Case' && $op == 'create') {
    CRM_Travelcase_Utils_AddPumCaseNumberToInvoice::pre($op, $objectName, $id, $params);
  }
}

/**
 * Update invoice number for a case
 * 
 */
function travelcase_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  CRM_Travelcase_Utils_AddPumCaseNumberToInvoice::custom($op, $groupID, $entityID, $params);
  CRM_Travelcase_Utils_AddDonorFromParentCase::custom($op, $groupID, $entityID, $params);
}

/**
 * Options for event link and case link
 * 
 * @param type $fieldID
 * @param type $options
 * @param type $detailedFormat
 */
function travelcase_civicrm_customFieldOptions( $fieldID, &$options, $detailedFormat = false ) {
  $config = CRM_Travelcase_Config::singleton();
  //auto fill option list for link to case field
  if ($fieldID == $config->getCustomFieldCaseId('id')) {
        $case_type = array();
        $params =array('name' => 'case_type');
        CRM_Core_BAO_OptionGroup::retrieve($params, $case_type);
        $closedId = CRM_Core_OptionGroup::getValue('case_status', 'Closed', 'name');
    $sql = "SELECT `civicrm_case`.*, civicrm_contact.display_name, ov.label as case_type_label FROM `civicrm_case` 
        INNER JOIN civicrm_case_contact ON civicrm_case.id = civicrm_case_contact.case_id
        INNER JOIN civicrm_contact ON civicrm_case_contact.contact_id = civicrm_contact.id
        INNER JOIN  civicrm_option_value ov ON ( civicrm_case.case_type_id=ov.value AND ov.option_group_id='".$case_type['id']."' )
        WHERE civicrm_case.`is_deleted` = 0 AND civicrm_case.status_id != $closedId";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()) {
      $label = $dao->subject;
      if ($detailedFormat) {
        $options[$dao->id] = array(
          'id' => $dao->id,
          'value' => $dao->id,
          'label' => $label
        );
      } else {
        $options[$dao->id] = $label;
      }
    }
  }
  
  //auto fill option list for link to event field
  if ($fieldID == $config->getCustomFieldEventId('id')) {
    $events = CRM_Event_BAO_Event::getEvents();
    foreach($events as $event_id => $event) {
      if ($detailedFormat) {
        $options[$event_id] = array(
          'id' => $event_id,
          'value' => $event_id,
          'label' => $event
        );
      } else {
        $options[$event_id] = $event;
      }
    }
  }
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function travelcase_civicrm_config(&$config) {
  _travelcase_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function travelcase_civicrm_xmlMenu(&$files) {
  _travelcase_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function travelcase_civicrm_install() {
  return _travelcase_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function travelcase_civicrm_uninstall() {
  return _travelcase_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function travelcase_civicrm_enable() {
  return _travelcase_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function travelcase_civicrm_disable() {
  return _travelcase_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function travelcase_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _travelcase_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function travelcase_civicrm_managed(&$entities) {
  return _travelcase_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function travelcase_civicrm_caseTypes(&$caseTypes) {
  _travelcase_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function travelcase_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _travelcase_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
