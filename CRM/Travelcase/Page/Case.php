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
    $this->preProcess();
    
    //get template file name
    $pageTemplateFile = $this->getHookedTemplateFileName();
    
    $config = CRM_Travelcase_Config::singleton();
    $case_status = array();
    $params =array('name' => 'case_status');
    CRM_Core_BAO_OptionGroup::retrieve($params, $case_status);
    $sql = "SELECT civicrm_case.*, civicrm_case_contact.contact_id as client_id, civicrm_contact.display_name, ov.label as case_status_label "
        . "FROM `".$config->getCustomGroupLinkCaseTo('table_name')."` AS `case_link`
        INNER JOIN `civicrm_case` ON `case_link`.`entity_id` = `civicrm_case`.`id`"
        . "INNER JOIN `civicrm_case_contact` ON `civicrm_case`.`id` = `civicrm_case_contact`.`case_id`"
        . "INNER JOIN `civicrm_contact` ON `civicrm_case_contact`.`contact_id`  = `civicrm_contact`.`id`"
        . "LEFT JOIN  civicrm_option_value ov ON ( civicrm_case.status_id=ov.value AND ov.option_group_id='".$case_status['id']."')"
        . "WHERE `case_link`.`".$config->getCustomFieldCaseId('column_name')."` = '".$this->caseId."'";

    $dao = CRM_Core_DAO::executeQuery($sql);
    $cases = array();
    while($dao->fetch()) {
      $cases[] = array(
        'client_id' => $dao->client_id,
        'case_id' => $dao->id,
        'display_name' => $dao->display_name,
        'status' => $dao->case_status_label,
      );
    }
    
    $this->assign('caseId', $this->caseId);
    $this->assign('travel_cases', $cases);    
    $this->assign('permission', 'edit');
    
    
    //find expert
    $expert_relationship = new CRM_Contact_BAO_Relationship();
    $expert_relationship->relationship_type_id = $config->getRelationshipTypeExpert('id');
    $expert_relationship->case_id = $this->caseId;
    $this->assign('expert', false);
    if ($expert_relationship->find(TRUE)) {
      $expert =  array();
      $params = array('id' => $expert_relationship->contact_id_b);
      CRM_Contact_BAO_Contact::retrieve($params, $expert);
      $this->assign('expert', $expert);
    }
    
    //render the template
    $content = self::$_template->fetch($pageTemplateFile);
    
    CRM_Utils_System::appendTPLFile($pageTemplateFile, $content);

    //its time to call the hook.
    CRM_Utils_Hook::alterContent($content, 'page', $pageTemplateFile, $this);
    
    return $content;  
  }
  
  protected function preProcess() {
    
  }
}

