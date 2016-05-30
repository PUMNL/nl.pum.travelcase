<?php

class CRM_Travelcase_InfoForDsa_Autofill {
  
  public function __construct() {
    
  }
  
  public function run($limit=1000, $debug=false) {
    $config = CRM_Travelcase_Config::singleton();
    $dsa = CRM_Travelcase_InfoForDsaConfig::singleton();
    $ma = CRM_Travelcase_MainActivityConfig::singleton();
    
    $sql = "SELECT `ma`.`start_date` AS `start_date`, `ma`.`end_date` AS `end_date`, `travelcase`.`entity_id` AS `case_id`, `dsa`.`id` AS `dsa_id`"
        . "FROM `".$config->getCustomGroupLinkCaseTo('table_name')."` `travelcase`"
        . "LEFT JOIN `".$ma->getCustomGroupMainActivityInfo('table_name')."` `ma` ON `travelcase`.`".$config->getCustomFieldCaseId('column_name')."` = `ma`.`entity_id`"
        . "LEFT JOIN `".$dsa->getCustomGroupInfoForDsa('table_name')."` `dsa` ON `dsa`.`entity_id` = `travelcase`.`entity_id`"
        . "WHERE (`dsa`.`id` IS NULL) OR (`dsa`.`".$dsa->getCustomFieldFillFromLinkedEntity('column_name')."` = '1'"
        . "AND (`dsa`.`".$dsa->getCustomFieldStartDate('column_name')."` != `ma`.`".$ma->getCustomFieldStartDate('column_name')."`"
        . "OR `dsa`.`".$dsa->getCustomFieldEndDate('column_name')."` != `ma`.`".$ma->getCustomFieldEndDate('column_name')."`))"
        . "LIMIT %1";
    
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($limit, 'Integer')));
    while($dao->fetch()) {
      try {
        if ($dao->dsa_id) {
          $update = "UPDATE `".$dsa->getCustomGroupInfoForDsa('table_name')."` SET "
              . "`".$dsa->getCustomFieldStartDate('column_name')."` = '".$dao->start_date."',"
              . "`".$dsa->getCustomFieldEndDate('column_name')."` = '".$dao->end_date."'"
              . "WHERE `id` = %1";
          
          CRM_Core_DAO::executeQuery($update, array(1 => array($dao->dsa_id, 'Integer')));
        } else {
          $insert = "INSERT INTO `".$dsa->getCustomGroupInfoForDsa('table_name')."`"
              . " (`entity_id`, `".$dsa->getCustomFieldStartDate('column_name')."`,`".$dsa->getCustomFieldEndDate('column_name')."`, `".$dsa->getCustomFieldFillFromLinkedEntity('column_name')."`)"
              . " VALUES (%1, '".$dao->start_date."', '".$dao->end_date."', '1');";
          
          CRM_Core_DAO::executeQuery($insert, array(1 => array($dao->case_id, 'Integer')));  
        }
      } catch (Exception $e) {
        if ($debug) {
          throw $e;
        }
      }
    }
  }
  
}

