<?php

class CRM_Travelcase_Utils_AddPumCaseNumberToInvoice {
  
  public static function pre( $op, $objectName, $id, &$params ) {
    if ($op != 'create') {
      return;
    }
    
    if ($objectName != 'Case') {
      return;
    }
        
    $config = CRM_Travelcase_Config::singleton();
    $ti_config = CRM_Travelcase_InfoForTravelAgencyConfig::singleton();
    
    $case_id_field = $config->getCustomFieldCaseId('id');
    if (!isset($params['custom'][$case_id_field][-1])) {
      return;
    }
    
    $parent_case_id = $params['custom'][$case_id_field][-1]['value'];
    $invoice = "";
    if ($parent_case_id) {
      $pum_case_number = CRM_Travelcase_PumCaseNumberConfig::singleton();
      $invoice = $pum_case_number->getCaseNumberByCaseId($parent_case_id);
      $invoice_id_field = $ti_config->getInvoiceInfoCustomField('id');
      $params['custom'][$invoice_id_field][-1]['value'] = $invoice;
    }
  }
  
  
  
  public static function custom($op, $groupID, $entityID, &$params) {
    if ($op != 'edit' && $op != 'create') { //create doesn't work, we use the pre hook for create
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
    
    $invoice = "";
    $case_id_field = $config->getCustomFieldCaseId('id');
    if (!empty($values[$case_id_field])) {
      $pum_case_number = CRM_Travelcase_PumCaseNumberConfig::singleton();
      $invoice = $pum_case_number->getCaseNumberByCaseId($values[$case_id_field]);
    }
    
    $ti_config = CRM_Travelcase_InfoForTravelAgencyConfig::singleton();
    $sql = "SELECT * FROM `".$ti_config->getInfoForTravelAgencyCustomGroup('table_name')."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($entityID, 'Integer')));
    if ($dao->fetch()) {
      //update
      $sql = "UPDATE `".$ti_config->getInfoForTravelAgencyCustomGroup('table_name')."` SET `".$ti_config->getInvoiceInfoCustomField('column_name')."`  = %1 WHERE `id` = %2";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($invoice, 'String'),
        2 => array($dao->id, 'Integer')
      ));
    } else {
      $sql = "INSERT INTO `".$ti_config->getInfoForTravelAgencyCustomGroup('table_name')."` (`entity_id`, `".$ti_config->getInvoiceInfoCustomField('column_name')."`) VALUES (%1, %2)";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($entityID, 'Integer'),
        2 => array($invoice, 'String'),
      ));
    }
    
  }

}
