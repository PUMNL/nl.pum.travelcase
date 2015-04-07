<?php

/**
 * Collection of upgrade steps
 */
class CRM_Travelcase_Upgrader extends CRM_Travelcase_Upgrader_Base {

  protected $case_type_id;
  
  protected $activity_type;
  
  public function install() {
    $this->addCaseTypes();
    $this->addActivityTYpes();

    $this->executeCustomDataFile('xml/travelinformation.xml');
    $this->executeCustomDataFile('xml/travelcase.xml');
    $this->executeCustomDataFile('xml/info_for_travel_agency.xml');
    $this->executeCustomDataFile('xml/info_for_dsa.xml');
    $this->executeCustomDataFile('xml/travelcase_status.xml');
    $this->executeCustomDataFile('xml/pickup.xml');
  }
  
  public function upgrade_1001() {
    $this->removeCustomField('manually_count_days', 'Info_for_DSA');
    $this->removeCustomField('number_of_days', 'Info_for_DSA');
    $this->removeOptionGroup('travel_case_dsa_manually_count_days');
    $this->executeCustomDataFile('xml/info_for_travel_agency.xml');
    $this->executeCustomDataFile('xml/travelcase_status.xml');
    return true;
  }
  
  public function upgrade_1002() {
    $sql = "UPDATE `civicrm_custom_group` SET `collapse_display` = '0', `is_multiple` = '0' WHERE `name` = 'travel_data'";
    CRM_Core_DAO::executeQuery($sql);
    return true;
  }
  
  public function upgrade_1003() {
    $this->removeOptionValue('Pick Up Information Customer', $this->activity_type);
    $this->addActivityTYpes();    
    return true;
  }
  
  public function upgrade_1004() {
    $this->removeCustomField('Pickup', 'travel_data');
    $this->executeCustomDataFile('xml/travelinformation.xml');
    return true;
  }
  
  public function upgrade_1005() {
    $this->removeCustomField('sponsor_code', 'sponsor_code');
    return true;        
  }
  
  public function upgrade_1006() {
    $this->executeCustomDataFile('xml/sponsor_code.xml');
    return true;
  }
  
  public function upgrade_1007() {    
    $this->removeCustomField('pickup_name', 'travel_data');
    $this->removeCustomField('pickup_telephone', 'travel_data');
    $this->removeCustomField('accommodation_name', 'travel_data');
    $this->removeCustomField('accommodation_telephone', 'travel_data');
    return true;
  }
  
  public function upgrade_1008() {
    $this->executeCustomDataFile('xml/pickup.xml');
    return true;
  }
  
  public function upgrade_1009() {
    $this->executeCustomDataFile('xml/travelinformation.xml');
    return true;
  }
  
  public function upgrade_1010() {
    $this->removeCustomField('sponsor', 'sponsor_code');
    $custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'id', 'name' => 'sponsor_code'));
    civicrm_api3('CustomGroup', 'delete', array('id' => $custom_group_id));
    return true;
  }
  
  public function upgrade_1011() {
    $this->executeCustomDataFile('xml/travelcase_status.xml');
    return true;
  }
  
  public function upgrade_1012() {
    $this->updateCustomField('visa', 'travelcase_status', array('weight' => 1, 'is_active' => 1));
    $this->updateCustomField('invitation', 'travelcase_status', array('weight' => 2, 'is_active' => 1));
    $this->updateCustomField('ticket', 'travelcase_status', array('weight' => 3, 'is_active' => 1));
    $this->updateCustomField('pickup', 'travelcase_status', array('weight' => 4, 'is_active' => 1));
    $this->updateCustomField('accomodation', 'travelcase_status', array('weight' => 5, 'is_active' => 1));
    return true;
  }

    public function upgrade_1013() {
        $this->removeCustomField('visa', 'travelcase_status');
        $this->removeCustomField('invitation', 'travelcase_status');
        $this->removeCustomField('ticket', 'travelcase_status');
        $this->removeCustomField('pickup', 'travelcase_status');
        $this->removeCustomField('accomodation', 'travelcase_status');
        $this->removeCustomField('dsa', 'travelcase_status');
        return true;
    }

    public function upgrade_1014() {
        $this->executeCustomDataFile('xml/travelcase_status.xml');
        return true;
    }

  public function upgrade_1015() {
    $this->removeCustomField('traveler_agrees_with_travel', 'traveler_information');
    $gid = civicrm_api3('CustomGroup', 'getvalue', array('name' => 'traveler_information', 'return' => 'id'));
    civicrm_api3('CustomGroup', 'delete', array('id' => $gid));
    return true;
  }
  
  protected function addActivityTYpes() {
    if (empty($this->activity_type)) {
      $this->activity_type = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'activity_type'));
    }
    
    $this->addOptionValue('DSA', 'DSA', $this->activity_type, 0, 7);
    $this->addOptionValue('Letter of Invitation', 'Letter of Invitation', $this->activity_type, 0, 7);
    $this->addOptionValue('Visa documents from Expert', 'Visa documents from Expert', $this->activity_type, 0, 7);
    $this->addOptionValue('Visa Request', 'Visa Request', $this->activity_type, 0, 7);
    $this->addOptionValue('Pick Up Information', 'Pick Up Information', $this->activity_type, 1, 7);
  }
  
  protected function addCaseTypes() {
    if (empty($this->case_type_id)) {
      $this->case_type_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => 'case_type'));
    }
    
    $this->addOptionValue('TravelCase', 'Travel Case', $this->case_type_id, 1);
  }
  
  protected function addOptionValue($name, $label, $option_group_id,$is_reserved=0, $component_id=false) {
    try {
      $exist_id = civicrm_api3('OptionValue', 'getvalue', array('return' => 'id', 'name' => $name, 'option_group_id' => $option_group_id));
      $params['id'] = $exist_id;
    } catch (Exception $e) {
      //do nothing
    }
    
    $params['name'] = $name;
    $params['label'] = $label;
    $params['is_active'] = 1;
    $params['is_reserved'] = $is_reserved;
    $params['option_group_id'] = $option_group_id;
    if ($component_id) {
      $params['component_id'] = $component_id;
    }
    civicrm_api3('OptionValue','create', $params);
  }
  
  protected function removeOptionValue($name, $option_group_id) {
    try {
      $exist_id = civicrm_api3('OptionValue', 'getvalue', array('return' => 'id', 'name' => $name, 'option_group_id' => $option_group_id));
      civicrm_api3('OptionValue', 'delete', array('id' => $exist_id));
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
  }
  
  protected function removeOptionGroup($name) {
    try {
      $option_group_id = civicrm_api3('OptionGroup', 'getvalue', array('return' => 'id', 'name' => $name));
      
      $option_values = civicrm_api3('OptionValue', 'get', array('option_group_id' => $option_group_id));
      foreach($option_values as $option_value) {
        try {
          civicrm_api3('OptionValue', 'delete', array('id' => $option_value['id']));
        } catch (Exception $e) {
          //do nothing
        }
      }
      
      civicrm_api3('OptionGroup', 'delete', array('id' => $option_group_id));
      
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
  }
  
  protected function removeCustomField($field_name, $custom_group_name) {
    try {
      $custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'id', 'name' => $custom_group_name));
      $custom_field_id = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => $custom_group_id, 'name' => $field_name, 'return' => 'id'));
      
      civicrm_api3('CustomField', 'delete', array('id' => $custom_field_id));
      
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
  }
  
  protected function updateCustomField($field_name, $custom_group_name, $params) {
    try {
      $custom_group_id = civicrm_api3('CustomGroup', 'getvalue', array('return' => 'id', 'name' => $custom_group_name));
      $custom_field = civicrm_api3('CustomField', 'getsingle', array('custom_group_id' => $custom_group_id, 'name' => $field_name, 'return' => 'id'));
      
      $params = $custom_field + $params;
      
      civicrm_api3('CustomField', 'create', $params);
      
      return; //aleardy exist
    } catch (Exception $e) {
      //do nothing
    }
  }

}
