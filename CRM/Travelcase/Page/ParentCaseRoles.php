<?php

/* 
 * This class is used to display the documents which belongs to a case
 * 
 */

class CRM_Travelcase_Page_ParentCaseRoles extends CRM_Core_Page {
  
  protected $caseId;
  
  protected $travel_case_type_id;
  
  public function __construct($caseId) {
    parent::__construct();
    
    $this->travel_case_type_id = CRM_Core_OptionGroup::getValue('case_type', 'TravelCase', 'name', 'String', 'value');
    $this->caseId = $caseId;
  }
  
  public function run() {
    $this->preProcess();
    
    $case = civicrm_api3('Case', 'getsingle', array('id' => $this->caseId));
    if ($case['case_type_id'] != $this->travel_case_type_id) {
      return '';
    }
    
    $config = CRM_Travelcase_Config::singleton();
    $case_id_field = $config->getCustomFieldCaseId('column_name');
    $table = $config->getCustomGroupLinkCaseTo('table_name');
    
    $sql = "SELECT `".$case_id_field."` AS `case_id` FROM `".$table."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1=>array($this->caseId, 'Integer')));
    $relationships = array();
    if ($dao->fetch() && $dao->case_id) {
      $client_sql = "SELECT `c`.id as `contact_id`,`e`.`email` AS `email`, `c`.`display_name` AS `contact_display_name`
          FROM `civicrm_case_contact` `ccc`
          INNER JOIN `civicrm_contact` `c` ON `ccc`.`contact_id` = `c`.`id`
          LEFT JOIN `civicrm_email` `e` ON `c`.`id` = `e`.`contact_id` AND `e`.`is_primary`
          WHERE `ccc`.`case_id` = %1";
      $client_dao = CRM_Core_DAO::executeQuery($client_sql, array(1=>array($dao->case_id, 'Integer')));
      while($client_dao->fetch()) {
        $relationships[] = array(
          'contact_link' => CRM_Utils_System::url('civicrm/contact/view', 'action=view&reset=1&cid='.$client_dao->contact_id),
          'contact_id' => $client_dao->contact_id,
          'contact_display_name' => $client_dao->contact_display_name,
          'email_link' => CRM_Utils_System::url('civicrm/contact/view/activity', 'action=reset=1&action=add&atype=3&cid='.$client_dao->contact_id.'&caseid='.$this->caseId),
          'email' => $client_dao->email,
          'relationship_type' => ts('Client'),
        );
      }
      $sql2 = "SELECT `c`.id as `contact_id`,`e`.`email` AS `email`, `c`.`display_name` AS `contact_display_name`, `rt`.`name_b_a` AS `relationship_type`
          FROM `civicrm_relationship` `r`
          INNER JOIN `civicrm_relationship_type` `rt` ON `r`.`relationship_type_id` = `rt`.`id`
          INNER JOIN `civicrm_contact` `c` ON `r`.`contact_id_b` = `c`.`id`
          LEFT JOIN `civicrm_email` `e` ON `c`.`id` = `e`.`contact_id` AND `e`.`is_primary`
          WHERE `r`.`case_id` = %1";
      $dao2 = CRM_Core_DAO::executeQuery($sql2, array(1=>array($dao->case_id, 'Integer')));
      while($dao2->fetch()) {
        $relationships[] = array(
          'contact_link' => CRM_Utils_System::url('civicrm/contact/view', 'action=view&reset=1&cid='.$dao2->contact_id),
          'contact_id' => $dao2->contact_id,
          'contact_display_name' => $dao2->contact_display_name,
          'email_link' => CRM_Utils_System::url('civicrm/contact/view/activity', 'action=reset=1&action=add&atype=3&cid='.$dao2->contact_id.'&caseid='.$this->caseId),
          'email' => $dao2->email,
          'relationship_type' => $dao2->relationship_type,
        );
      }
    }
    
    //get template file name
    $pageTemplateFile = $this->getHookedTemplateFileName();

    $this->assign('caseId', $this->caseId);
    $this->assign('relationships', $relationships);    
    
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

