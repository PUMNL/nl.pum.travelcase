<?php

class CRM_Travelcase_Utils_AddDonorFromParentCase {

  public static function custom($op, $groupID, $entityID, &$params) {
    if ($op != 'create' && $op != 'edit') {
      return;
    }

    $config = CRM_Travelcase_Config::singleton();
    if ($config->getCustomGroupLinkCaseTo('id') != $groupID) {
      return;
    }
    
    //ok, requirements met
    //add iban to the iban list
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    $parent_case_id = $values[$case_id_field];
    
    self::dropCurrentDonorLinks($entityID);
    if ($parent_case_id) {
      self::copyDonorLink($parent_case_id, $entityID);
    } 
  }
  
  public static function copyDonorLinkFromCase($from_case_id) {
    $config = CRM_Travelcase_Config::singleton();
    $case_id_field = $config->getCustomFieldCaseId('column_name');
    $table = $config->getCustomGroupLinkCaseTo('table_name');
    $sql = "SELECT `entity_id` FROM `".$table."` WHERE `".$case_id_field."` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($from_case_id, 'Integer')));
    while($dao->fetch()) {
      self::copyDonorLink($from_case_id, $dao->entity_id);
    }
  }
  
  public static function dropCurrentDonorLinks($case_id) {
    CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Case', $case_id);
  }
  
  public static function copyDonorLink($from_case_id, $to_case_id) {
    CRM_Threepeas_BAO_PumDonorLink::deleteByEntityId('Case', $to_case_id);
    
    $sql = "SELECT *
        FROM `civicrm_donor_link` `d` 
        WHERE `d`.`entity` = 'Case' AND `d`.`entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($from_case_id, 'Integer')));
    while ($dao->fetch()) {
      $params = array(
      'donation_entity' => $dao->donation_entity, 
      'donation_entity_id' => $dao->donation_entity_id,
      'entity' => 'Case',
      'entity_id' => $to_case_id,
      'is_active' => $dao->is_active);
      CRM_Threepeas_BAO_PumDonorLink::add($params);
    }
  }

}
