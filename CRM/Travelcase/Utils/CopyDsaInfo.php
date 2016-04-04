<?php

class CRM_Travelcase_Utils_CopyDsaInfo {
  
  public static function getMAInfo($case_id) {
    $ma = CRM_Travelcase_MainActivityConfig::singleton();
    
    $sql = "SELECT `ma`.`".$ma->getCustomFieldStartDate('column_name')."` AS `start_date`, `ma`.`".$ma->getCustomFieldEndDate('column_name')."` AS `end_date`"
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
    if (empty($params['custom'][$autofill_field][-1]['value'])) {
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
        $edate = new DateTime($ma_info['end_date']);
    
        $params['custom'][$start_date][-1]['value'] = $sdate->format('Ymd');
        $params['custom'][$end_date][-1]['value'] = $edate->format('Ymd');
      }
    }
  }

  public static function custom_ma_info($op, $groupID, $entityID, &$params) {
    if ($op != 'edit') {
      return;
    }

    $ma = CRM_Travelcase_MainActivityConfig::singleton();
    if ($ma->getCustomGroupMainActivityInfo('id') != $groupID) {
      return;
    }

    //reformat params so it is easier to use
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }

    $ma_info = array(
      'start_date' => (isset($values[$ma->getCustomFieldStartDate('id')]) ? $values[$ma->getCustomFieldStartDate('id')] : ''),
      'end_date' => (isset($values[$ma->getCustomFieldEndDate('id')]) ? $values[$ma->getCustomFieldEndDate('id')] : ''),
    );

    $travel_cases = self::getTravelCases($entityID);
    foreach($travel_cases as $travel_case_id) {
      $autofill = self::autoFillCaseFromParent($travel_case_id);
      if ($autofill) {
        self::fillFromParentCase($entityID, $travel_case_id, $ma_info);
      }
    }

  }

  public static function custom_link_case_to($op, $groupID, $entityID, &$params) {
    if ($op != 'edit') {
      return;
    }

    $config = CRM_Travelcase_Config::singleton();
    if ($groupID != $config->getCustomGroupLinkCaseTo('id')) {
      return;
    }

    $autofill = self::autoFillCaseFromParent($entityID);
    if (!$autofill) {
      return;
    }

    //ok, requirements met
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }

    $parent_case_id_field_id = $config->getCustomFieldCaseId('id');
    if (empty($values[$parent_case_id_field_id])) {
      return;
    }

    $parent_case_id = $values[$parent_case_id_field_id];
    if ($parent_case_id) {
      $ma_info = self::getMAInfo($parent_case_id);
      self::fillFromParentCase($parent_case_id, $entityID, $ma_info);
    }
  }
  
  public static function custom_info_for_dsa($op, $groupID, $entityID, &$params) {
    if ($op != 'edit') {
      return;
    }

    $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();
    if ($dsa->getCustomGroupInfoForDsa('id') != $groupID) {
      return;
    }

    //ok, requirements met
    $values = array();
    foreach($params as $param) {
      $values[$param['custom_field_id']] = $param['value'];
    }

    $autofill_field = $dsa->getCustomFieldFillFromLinkedEntity('id');
    if (empty($values[$autofill_field])) {
      return;
    }
    
    $parent_case_id = self::getParentCase($entityID);
    if ($parent_case_id) {
      $ma_info = self::getMAInfo($parent_case_id);
      self::fillFromParentCase($parent_case_id, $entityID, $ma_info);
    }
    
  }

  public static function autoFillCaseFromParent($case_id) {
    $config = CRM_Travelcase_InfoForDsaConfig::singleton();
    $sql = "SELECT `".$config->getCustomFieldFillFromLinkedEntity('column_name')."` AS `autofill` FROM `".$config->getCustomGroupInfoForDsa('table_name')."` WHERE `entity_id`  = %1";
    $params = array(
      1 => array($case_id, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      if ($dao->autofill) {
        return true;
      }
    }
    return false;
  }

  public static function getTravelCases($parent_case_id) {
    $config = CRM_Travelcase_Config::singleton();
    $sql = "SELECT `entity_id` AS `case_id` FROM `".$config->getCustomGroupLinkCaseTo('table_name')."` WHERE `".$config->getCustomFieldCaseId('column_name')."` = %1";
    $params = array(
        1 => array($parent_case_id, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    $return = array();
    while($dao->fetch()) {
      $return[] = $dao->case_id;
    }
    return $return;
  }

  public static function getParentCase($case_id) {
    $config = CRM_Travelcase_Config::singleton();
    $sql = "SELECT `".$config->getCustomFieldCaseId('column_name')."` AS `case_id` FROM `".$config->getCustomGroupLinkCaseTo('table_name')."` WHERE `entity_id` = %1";
    $params = array(
      1 => array($case_id, 'Integer'),
    );
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    if ($dao->fetch()) {
      return $dao->case_id;
    }
    return false;
  }

  public static function fillFromParentCase($parent_case_id, $client_case_id, $ma_info) {
    $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();
    if (is_array($ma_info)) {
      $sql = "SELECT * FROM `".$dsa->getCustomGroupInfoForDsa('table_name')."` WHERE `entity_id` = %1";
      $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($client_case_id, 'Integer')));
      if ($dao->fetch()) {
        //update
        $sql = "UPDATE `".$dsa->getCustomGroupInfoForDsa('table_name')."` SET `".$dsa->getCustomFieldStartDate('column_name')."`  = %1, `".$dsa->getCustomFieldEndDate('column_name')."`  = %2 WHERE `id` = %3";
        CRM_Core_DAO::executeQuery($sql, array(
            1 => array($ma_info['start_date'] ? $ma_info['start_date'] : '' , 'String'),
            2 => array($ma_info['end_date'] ? $ma_info['end_date'] : '', 'String'),
            3 => array($dao->id, 'Integer')
        ));
      } else {
        $sql = "INSERT INTO `".$dsa->getCustomGroupInfoForDsa('table_name')."` (`entity_id`, `".$dsa->getCustomFieldStartDate('column_name')."`, `".$dsa->getCustomFieldEndDate('column_name')."`) VALUES (%1, %2, %3)";
        CRM_Core_DAO::executeQuery($sql, array(
            1 => array($client_case_id, 'Integer'),
            2 => array($ma_info['start_date'] ? $ma_info['start_date'] : '', 'String'),
            3 => array($ma_info['end_date'] ? $ma_info['end_date'] : '', 'String'),
        ));
      }
    }
  }

}
