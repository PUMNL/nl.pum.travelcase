<?php

class CRM_Travelcase_Utils_AddPumCaseNumberToInvoice {

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
      $sql = "INSERT INTO `".$ti_config->getInfoForTravelAgencyCustomGroup('table_name')."` (`".$ti_config->getInvoiceInfoCustomField('column_name')."`) VALUES (%1)";
      CRM_Core_DAO::executeQuery($sql, array(
        1 => array($invoice, 'String'),
      ));
    }
    
  }

}
