<?php

class CRM_Travelcase_PumCaseNumberConfig {
  
  protected static $_singleton;
  
  protected $pum_case_number;
  
  protected $sequence;
  
  protected $type;
  
  protected $country;
  
  
  protected function __construct() {
    $this->pum_case_number = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'PUM_Case_number'));
    $this->sequence = civicrm_api3('CustomField', 'getsingle', array('name' => 'Case_sequence', 'custom_group_id' => $this->pum_case_number['id']));
    $this->type = civicrm_api3('CustomField', 'getsingle', array('name' => 'Case_type', 'custom_group_id' => $this->pum_case_number['id']));
    $this->country = civicrm_api3('CustomField', 'getsingle', array('name' => 'Case_country', 'custom_group_id' => $this->pum_case_number['id']));
  }
  
  /**
   * @return CRM_Travelcase_PumCaseNumberConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_PumCaseNumberConfig();
    }
    return self::$_singleton;
  }
  
  public function getCustomFieldSequence($key='id') {
    return $this->sequence[$key];
  }
  
  public function getCustomFieldType($key='id') {
    return $this->type[$key];
  }
  
  public function getCustomFieldCountry($key='id') {
    return $this->country[$key];
  }
  
  public function getCustomGroupPumCaseNumber($key='id') {
    return $this->pum_case_number[$key];
  }
  
  /**
   * Returns a case number in the format of seuqnce-type-country
   * E.g. 70069-A-ZA
   * 
   * @param type $case_id
   * @return string
   */
  public function getCaseNumberByCaseId($case_id) {
    $return = "";
    $sql = "SELECT * FROM `".$this->getCustomGroupPumCaseNumber('table_name')."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($case_id, 'Integer')));
    if ($dao->fetch()) {
      $seq_field = $this->getCustomFieldSequence('column_name');
      $type_field = $this->getCustomFieldType('column_name');
      $country_field = $this->getCustomFieldCountry('column_name');
      $return = $dao->$seq_field."-".$dao->$type_field."-".$dao->$country_field;      
    }
    return $return;
  }
  
}