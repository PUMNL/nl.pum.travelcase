<?php

class CRM_Travelcase_Utils_CopyPumCaseNumber{
  
  public static function post( $op, $objectName, $id, &$objectRef ) {
    if ($op != 'create') {
      return;
    }
    
    if ($objectName != 'Case') {
      return;
    }
        
    $config = CRM_Travelcase_Config::singleton();    
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    $custom_values = CRM_Core_BAO_CustomValueTable::getEntityValues($id, null, array($case_id_field));
    
    if (empty($custom_values[$case_id_field])) {
      return;
    }
    
    $parent_case_id = $custom_values[$case_id_field];
    if ($parent_case_id) {
      $pum_case_number = CRM_Travelcase_PumCaseNumberConfig::singleton();
      $case_number = $pum_case_number->getPumCaseNumberArray($parent_case_id);
      
      if ($case_number) {
        self::updatePumCaseNumber($id, $case_number);
      }      
    }
  }
  
  protected static function updatePumCaseNumber($entityID, $case_number) {
    if (!$case_number) {
      return;
    }
    
    $pum_case_number = CRM_Travelcase_PumCaseNumberConfig::singleton();
    
    $sql = "SELECT * FROM `".$pum_case_number->getCustomGroupPumCaseNumber('table_name')."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($entityID, 'Integer')));
    if ($dao->fetch()) {
      //update
      $sql = "UPDATE `".$pum_case_number->getCustomGroupPumCaseNumber('table_name')."` SET 
          `".$pum_case_number->getCustomFieldSequence('column_name')."`  = %1,
          `".$pum_case_number->getCustomFieldCountry('column_name')."`  = %2,
          `".$pum_case_number->getCustomFieldType('column_name')."`  = %3 
          WHERE `id` = %4";
      CRM_Core_DAO::executeQuery($sql, array(
      1 => array($case_number['sequence'], 'Integer'),
      2 => array($case_number['country'], 'String'),
      3 => array($case_number['type'], 'String'),
      4 => array($dao->id, 'Integer')
    ));
    } else {
      $sql = "INSERT INTO `".$pum_case_number->getCustomGroupPumCaseNumber('table_name')."` 
          (`entity_id`, `".$pum_case_number->getCustomFieldSequence('column_name')."`, `".$pum_case_number->getCustomFieldCountry('column_name')."`, `".$pum_case_number->getCustomFieldType('column_name')."`) 
          VALUES (%1, %2, %3, %4)";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($entityID, 'Integer'),
        2 => array($case_number['sequence'], 'Integer'),
        3 => array($case_number['country'], 'String'),
        4 => array($case_number['type'], 'String'),
      ));
    }
  }
  
  
  
  public static function custom($op, $groupID, $entityID, &$params) {
    if ($op != 'edit') { //create doesn't work, we use the post hook for create
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
    
    $pum_case_number = CRM_Travelcase_PumCaseNumberConfig::singleton();
    $case_number = false;
    $case_id_field = $config->getCustomFieldCaseId('id');
    if (!empty($values[$case_id_field])) {
      $case_number = $pum_case_number->getPumCaseNumberArray($values[$case_id_field]);
    }
    
    if ($case_number) {
      self::updatePumCaseNumber($entityID, $case_number);
    }
    
  }

}
