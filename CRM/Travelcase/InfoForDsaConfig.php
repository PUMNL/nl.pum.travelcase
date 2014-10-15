<?php

class CRM_Travelcase_InfoForDsaConfig {
  
  protected static $_singleton;
  
  protected $info_for_dsa;
  
  protected $start_date;
  
  protected $end_date;
  
  protected $autofill;
  
  
  protected function __construct() {
    $this->info_for_dsa = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'Info_for_DSA'));
    $this->start_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'Start_date', 'custom_group_id' => $this->info_for_dsa['id']));
    $this->end_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'End_date', 'custom_group_id' => $this->info_for_dsa['id']));
    $this->autofill = civicrm_api3('CustomField', 'getsingle', array('name' => 'fill_from_linked_entity', 'custom_group_id' => $this->info_for_dsa['id']));
  }
  
  /**
   * @return CRM_Travelcase_InfoForDsaConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_InfoForDsaConfig();
    }
    return self::$_singleton;
  }
  
  public function getCustomFieldStartDate($key='id') {
    return $this->start_date[$key];
  }
  
  public function getCustomFieldEndDate($key='id') {
    return $this->end_date[$key];
  }
  
  public function getCustomFieldFillFromLinkedEntity($key='id') {
    return $this->autofill[$key];
  }
  
  public function getCustomGroupInfoForDsa($key='id') {
    return $this->info_for_dsa[$key];
  }
  
}