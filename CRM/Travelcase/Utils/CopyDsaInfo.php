<?php

class CRM_Travelcase_Utils_CopyDsaInfo {
  
  public static function getMAInfo($case_id) {
    $ma = CRM_Travelcase_MainActivityConfig::singleton();
    
    $sql = "SELECT `ma`.`start_date` AS `start_date`, `ma`.`end_date` AS `end_date`"
        . "FROM `".$ma->getCustomGroupMainActivityInfo('table_name')."` `ma`"
        . "WHERE `entity_id` = %1";
    
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($case_id, 'Integer')));
    $return = false;
    if($dao->fetch()) {
      $return['start_date'] = $dao->start_date;
      $return['end_date'] = $dao->end_date;
    }
    return $return;
  }
  
  public static function pre( $op, $objectName, $id, &$params ) {
    if ($op != 'create') {
      return;
    }
    
    if ($objectName != 'Case') {
      return;
    }
        
    $config = CRM_Travelcase_Config::singleton();
    $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();
    
    $autofill_field = $dsa->getCustomFieldFillFromLinkedEntity('id');
    if (!empty($params['custom'][$autofill_field][-1]['value'])) {
      return;
    }
    
    $case_id_field = $config->getCustomFieldCaseId('id');    
    if (!isset($params['custom'][$case_id_field][-1])) {
      return;
    }
    
    $parent_case_id = $params['custom'][$case_id_field][-1]['value'];
    if ($parent_case_id) {
      $ma_info = self::getMAInfo($parent_case_id);
      if (is_array($ma_info)) {
        $start_date = $dsa->getCustomFieldStartDate('id');
        $end_date = $dsa->getCustomFieldEndDate('id');
        
        $sdate = new DateTime($ma_info['start_date']);
        $edate = new DateTime($ma_info['start_date']);
    
        $params['custom'][$start_date][-1]['value'] = $sdate->format('Ymd');
        $params['custom'][$end_date][-1]['value'] = $edate->format('Ymd');
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
    
    $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();    
    $autofill_field = $dsa->getCustomFieldFillFromLinkedEntity('id');
    if (!empty($params['custom'][$autofill_field])) {
      return;
    }
    
    $parent_case_id = $values[$case_id_field];
    if ($parent_case_id) {
      $ma_info = self::getMAInfo($parent_case_id);
      if (is_array($ma_info)) {
        $sql = "SELECT * FROM `".$dsa->getCustomGroupInfoForDsa('table_name')."` WHERE `entity_id` = %1";
        $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($entityID, 'Integer')));
        if ($dao->fetch()) {
          //update
          $sql = "UPDATE `".$dsa->getCustomGroupInfoForDsa('table_name')."` SET `".$dsa->getCustomFieldStartDate('column_name')."`  = %1, `".$dsa->getCustomFieldEndDate('column_name')."`  = %2 WHERE `id` = %3";
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($ma_info['start_date'], 'String'),
            2 => array($ma_info['end_date'], 'String'),
            3 => array($dao->id, 'Integer')
          ));
        } else {
          $sql = "INSERT INTO `".$dsa->getCustomGroupInfoForDsa('table_name')."` (`entity_id`, `".$dsa->getCustomFieldStartDate('column_name')."`, `".$dsa->getCustomFieldStartDate('column_name')."`) VALUES (%1, %2, %3)";
          CRM_Core_DAO::executeQuery($sql, array(
            1 => array($entityID, 'Integer'),
            2 => array($ma_info['start_date'], 'String'),
            3 => array($ma_info['end_date'], 'String'),
          ));
        }
      }
    }
    
  }

}
