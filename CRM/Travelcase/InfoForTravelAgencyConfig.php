<?php

class CRM_Travelcase_InfoForTravelAgencyConfig {
  
  protected static $_singleton;
  
  protected $info_for_travel_agency;
  
  protected $invoice_info;
  
  
  protected function __construct() {
    $this->info_for_travel_agency = civicrm_api3('CustomGroup', 'getsingle', array('name' => 'info_for_travel_agency'));
    $this->invoice_info = civicrm_api3('CustomField', 'getsingle', array('name' => 'invoice_info', 'custom_group_id' => $this->info_for_travel_agency['id']));
  }
  
  /**
   * @return CRM_Travelcase_InfoForTravelAgencyConfig 
   */
  public static function singleton() {
    if (!self::$_singleton) {
      self::$_singleton = new CRM_Travelcase_InfoForTravelAgencyConfig();
    }
    return self::$_singleton;
  }
  
  public function getInfoForTravelAgencyCustomGroup($key='id') {
    return $this->info_for_travel_agency[$key];
  }
  
  public function getInvoiceInfoCustomField($key='id') {
    return $this->invoice_info[$key];
  }
  
}