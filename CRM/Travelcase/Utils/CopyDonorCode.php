<?php

class CRM_Travelcase_Utils_CopyDonorCode {
  
  public static function pre( $op, $objectName, $id, &$params ) {
    if ($op != 'create') {
      return;
    }
    
    if ($objectName != 'Case') {
      return;
    }
        
    $config = CRM_Travelcase_Config::singleton();
    $sc_config = CRM_Travelcase_SponsorCodeConfig::singleton();
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    if (!isset($params['custom'][$case_id_field][-1])) {
      return;
    }
    
    $parent_case_id = $params['custom'][$case_id_field][-1]['value'];
    if ($parent_case_id) {
      $sponsor_code = $sc_config->getSponsorCodeByCaseId($parent_case_id);
      if (!empty($sponsor_code)) {
        $sponsor_id_field = $sc_config->getCustomFieldSponsorCode('id');
        $params['custom'][$sponsor_id_field][-1]['value'] = $sponsor_code;
      }
    }
  }
  
  
  
  public static function custom($op, $groupID, $entityID, &$params) {
    if ($op != 'edit') { //create doesn't work, we use the pre hook for create
      return;
    }

    $config = CRM_Travelcase_Config::singleton();
    if ($config->getCustomGroupLinkCaseTo('id') != $groupID) {
      return;
    }
    
    //ok, requirements met
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    if (empty($values[$case_id_field])) {
      return;
    }
    
    $parent_case_id = $values[$case_id_field];
    if ($parent_case_id) {
      $sc_config = CRM_Travelcase_SponsorCodeConfig::singleton();
      $sponsor_code = $sc_config->getSponsorCodeByCaseId($parent_case_id);
      if (!empty($sponsor_code)) {
        $sql = "SELECT * FROM `".$sc_config->getCustomGroupSponsorCode('table_name')."` WHERE `entity_id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($entityID, 'Integer')));
        if ($dao->fetch()) {
          //update
          $sql = "UPDATE `".$sc_config->getCustomGroupSponsorCode('table_name')."` SET `".$sc_config->getCustomFieldSponsorCode('column_name')."`  = %1 WHERE `id` = %2";
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($sponsor_code, 'String'),
            2 => array($dao->id, 'Integer')
          ));
        } else {
          $sql = "INSERT INTO `".$sc_config->getCustomGroupSponsorCode('table_name')."` (`entity_id`, `".$sc_config->getCustomFieldSponsorCode('column_name')."`) VALUES (%1, %2)";
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($entityID, 'Integer'),
            2 => array($sponsor_code, 'String'),
          ));
        }
      }
    }
    
  }

}
