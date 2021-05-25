<?php

/**
 * Collection of upgrade steps
 */
class CRM_Travelcase_Upgrader extends CRM_Travelcase_Upgrader_Base {

  protected $case_type_id;

  protected $activity_type;

  public function install() {
    $this->addCaseTypes();
    $this->addActivityTypes();

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
    $this->addActivityTypes();
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

  /**
   * CRM_Travelcase_Upgrader::upgrade_1016()
   *
   * Add 'Pre travel check' to status tab on travel case
   *
   * @return boolean
   */
  public function upgrade_1016() {
    try{
      $cg_travelcase_status = civicrm_api('CustomGroup', 'getsingle', array('version' => 3, 'sequential' => 1, 'name' => 'travelcase_status'));
      $og_travelcase_status_options = civicrm_api('OptionGroup', 'getsingle', array('version' => 3, 'sequential' => 1, 'name' => 'travelcase_status_options'));

      if(empty($cg_travelcase_status['id']) || empty($og_travelcase_status_options['id'])){
        return FALSE;
      }

      $field_params = array(
        'version' => 3,
        'sequential' => 1,
        'custom_group_id' => $cg_travelcase_status['id'],
        'name' => 'pre_travel_check',
        'label' => 'Pre travel check',
        'data_type' => 'String',
        'html_type' => 'Select',
        'is_required' => 1,
        'is_searchable' => 1,
        'is_search_range' => 0,
        'weight' => 1,
        'is_active' => 1,
        'is_view' => 0,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'pre_travel_check',
        'option_group_id' => $og_travelcase_status_options['id']
      );
      $field = civicrm_api('CustomField', 'create', $field_params);

      foreach($field['values'] as $key=> $value){
        $currentOptionGroupId = $value['option_group_id'];

        // apply datafix
        // 1 - apply correct option_group_id to custom field
        $sql = "UPDATE civicrm_custom_field SET option_group_id = " . $og_travelcase_status_options['id'] . " WHERE id = " . $field['id'];
        $sqlResult = CRM_Core_DAO::executeQuery($sql);
        // 2 - remove unjust option group
        $sql = "DELETE FROM civicrm_option_group WHERE id = " . $currentOptionGroupId;
        $sqlResult = CRM_Core_DAO::executeQuery($sql);
      }

      if($field['is_error'] == 0){
        return TRUE;
      } else {
        return FALSE;
      }
    } catch(Exception $e){
      return FALSE;
    }
  }

  /**
   * CRM_Travelcase_Upgrader::upgrade_1017()
   *
   * Set default value of travel case status options to 'To be arranged'
   *
   * @return boolean
   */
  public function upgrade_1017() {
    $params_cg_travelcase_status = array(
      'version' => 3,
      'sequential' => 1,
      'custom_group_id' => 'travelcase_status',
    );
    $cg_travelcase_status = civicrm_api('CustomField', 'get', $params_cg_travelcase_status);

    //All default_values in travelcase_status should be on 'To be arranged'
    $params_to_be_arranged = array(
      'version' => 3,
      'sequential' => 1,
      'option_group_id' => 'travelcase_status_options',
      'label' => 'To be arranged',
    );
    $ov_to_be_arranged = civicrm_api('OptionValue', 'getsingle', $params_to_be_arranged);

    //set all default value of all status fields to 'To be arranged'
    foreach($cg_travelcase_status['values'] as $key => $value) {
      $sql = "UPDATE civicrm_custom_field SET default_value = %1 WHERE id = %2";
      CRM_Core_DAO::executeQuery($sql, array(1=>array((int)$ov_to_be_arranged['value'],'Integer'), 2=>array((int)$value['id'],'Integer')));
    }

    return TRUE;
  }

  protected function addActivityTypes() {
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
