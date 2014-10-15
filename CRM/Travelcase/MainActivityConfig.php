<?php

class CRM_Travelcase_MainActivityConfig {
  
  protected static $_singleton;
  
  protected $main_activity_info;
  
  protected $start_date;
  
  protected $end_date;
  
  
  
  protected function __construct() {
    $this->main_activity_info = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'main_activity_info'));
    $this->start_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'main_activity_start_date', 'custom_group_id' => $this->main_activity_info['id']));
    $this->end_date = civicrm_api3('CustomField', 'getsingle', array('name' => 'main_activity_end_date', 'custom_group_id' => $this->main_activity_info['id']));
  }
  
  /**
   * @return CRM_Travelcase_MainActivityConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_MainActivityConfig();
    }
    return self::$_singleton;
  }
  
  public function getCustomFieldStartDate($key='id') {
    return $this->start_date[$key];
  }
  
  public function getCustomFieldEndDate($key='id') {
    return $this->end_date[$key];
  }
  
  public function getCustomGroupMainActivityInfo($key='id') {
    return $this->main_activity_info[$key];
  }
  
}