<?php

class CRM_Travelcase_SponsorCodeConfig {
  
  protected static $_singleton;
  
  protected $sponsor_code_group;
  
  protected $sponsor_code_field;
  
  
  protected function __construct() {
    $this->sponsor_code_group = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'sponsor_code'));
    $this->sponsor_code_field = civicrm_api3('CustomField', 'getsingle', array('name' => 'sponsor_code', 'custom_group_id' => $this->sponsor_code_group['id']));
  }
  
  /**
   * @return CRM_Travelcase_SponsorCodeConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_SponsorCodeConfig();
    }
    return self::$_singleton;
  }
  
  public function getCustomFieldSponsorCode($key='id') {
    return $this->sponsor_code_field[$key];
  }
    
  public function getCustomGroupSponsorCode($key='id') {
    return $this->sponsor_code_group[$key];
  }
  
  public function getSponsorCodeByCaseId($case_id) {
    $return = "";
    $sql = "SELECT * FROM `".$this->getCustomGroupSponsorCode('table_name')."` WHERE `entity_id` = %1";
    $dao = CRM_Core_DAO::executeQuery($sql, array(1 => array($case_id, 'Integer')));
    if ($dao->fetch()) {
      $sponsor_code_field = $this->getCustomFieldSponsorCode('column_name');
      $return = $dao->$sponsor_code_field;
    }
    return $return;
  }
  
}