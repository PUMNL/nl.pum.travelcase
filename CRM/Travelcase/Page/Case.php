<?php

/* 
 * This class is used to display the documents which belongs to a case
 * 
 */

class CRM_Travelcase_Page_Case extends CRM_Core_Page {
  
  protected $caseId;
  
  public function __construct($caseId) {
    parent::__construct();
    
    $this->caseId = $caseId;
  }
  
  public function run() {
    $ignoreCaseTypes = $this->getCaseTypeIdsToIgnore();
    $case_type_id = civicrm_api3('Case', 'getvalue', array('id' => $this->caseId, 'return' => 'case_type_id'));
    if (!in_array($case_type_id, $ignoreCaseTypes)) {

      $this->preProcess();

      //get template file name
      $pageTemplateFile = $this->getHookedTemplateFileName();

      $sys_config = CRM_Core_Config::singleton();
      $config = CRM_Travelcase_Config::singleton();
      $session = CRM_Core_Session::singleton();

      $case_status = array();
      $params = array('name' => 'case_status');
      CRM_Core_BAO_OptionGroup::retrieve($params, $case_status);
      $sql = "SELECT civicrm_case.*, civicrm_case_contact.contact_id as client_id, civicrm_contact.display_name, ov.label as case_status_label "
        . ",`ta`.`" . $config->getCustomFieldTravelDataDestination('column_name') . "` AS `destination`, `ta`.`"
        . $config->getCustomFieldTravelDataDepartureDate('column_name') . "` AS `departure_date`, `ta`.`"
        . $config->getCustomFieldTravelDataReturnDate('column_name') . "` AS `return_date`"
        . "FROM `" . $config->getCustomGroupLinkCaseTo('table_name') . "` AS `case_link`
          INNER JOIN `civicrm_case` ON `case_link`.`entity_id` = `civicrm_case`.`id`"
        . "INNER JOIN `civicrm_case_contact` ON `civicrm_case`.`id` = `civicrm_case_contact`.`case_id`"
        . "INNER JOIN `civicrm_contact` ON `civicrm_case_contact`.`contact_id`  = `civicrm_contact`.`id`"
        . "LEFT JOIN `" . $config->getCustomGroupTravelData('table_name') . "` `ta` ON `civicrm_case`.`id` = `ta`.`entity_id`"
        . "LEFT JOIN  civicrm_option_value ov ON ( civicrm_case.status_id=ov.value AND ov.option_group_id='" . $case_status['id'] . "')"
        . "WHERE `case_link`.`" . $config->getCustomFieldCaseId('column_name') . "` = '" . $this->caseId . "' AND civicrm_case.is_deleted = '0'";

      $dao = CRM_Core_DAO::executeQuery($sql);
      $cases = array();
      while ($dao->fetch()) {
        $cases[] = array(
          'client_id' => $dao->client_id,
          'case_id' => $dao->id,
          'display_name' => $dao->display_name,
          'status' => $dao->case_status_label,
          'destination' => $dao->destination,
          'departure_date' => CRM_Utils_Date::customFormat($dao->departure_date, $sys_config->dateformatFull),
          'return_date' => CRM_Utils_Date::customFormat($dao->return_date, $sys_config->dateformatFull),
        );
      }

      $this->assign('caseId', $this->caseId);
      $this->assign('travel_cases', $cases);
      $this->assign('permission', 'edit');

      $relationships_to_check = $config->getTravelCaseRelationshipsForCaseType($case_type_id);

      $related_contacts = array();
      foreach ($relationships_to_check as $rtype_id) {
        if ($rtype_id == 'additional_person') {
          $related_contacts[] = 'additional_person';
        } else {
          $relationship = new CRM_Contact_BAO_Relationship();
          $relationship->relationship_type_id = $rtype_id;
          $relationship->case_id = $this->caseId;
          if ($relationship->find(TRUE) && $session->get('userID') != $relationship->contact_id_b) {
            $contact = array();
            $params = array('id' => $relationship->contact_id_b);
            CRM_Contact_BAO_Contact::retrieve($params, $contact);
            $related_contacts[] = $contact;
          }
        }
      }
      $this->assign('related_contacts', $related_contacts);


      //render the template
      $content = self::$_template->fetch($pageTemplateFile);

      CRM_Utils_System::appendTPLFile($pageTemplateFile, $content);

      //its time to call the hook.
      CRM_Utils_Hook::alterContent($content, 'page', $pageTemplateFile, $this);

      return $content;
    }
  }
  
  protected function preProcess() {
    
  }

  /**
   * Method to retrieve the case types where travel case tab should NOT appear
   *
   * @return array $result
   */
  private function getCaseTypeIdsToIgnore() {
    $result = array();
    $caseTypeNames = array(
      'ExitExpert',
      'Expertapplication',
      'Grant',
      'Opportunity',
      'OrganiseEvent',
      'Projectintake',
      'RemoteCoaching',
      'TravelCase'
    );
    foreach ($caseTypeNames as $caseTypeName) {
      try {
        $result[] = (int) civicrm_api3('OptionValue', 'getvalue', array(
          'name' => $caseTypeName,
          'option_group_id' => 'case_type',
          'return' => 'value'
        ));
      } catch (CiviCRM_API3_Exception $ex) {}
    }
    return $result;
  }
}

