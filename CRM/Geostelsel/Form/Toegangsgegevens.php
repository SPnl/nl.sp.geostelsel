<?php

class CRM_Geostelsel_Form_Toegangsgegevens extends CRM_Core_Form {

  protected $_id;

  protected $_cid;

  function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this, false);
    $this->_cid = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
  }

  public function buildQuickForm() {
    $this->add('hidden', 'cid', $this->_cid);
    $this->add('hidden', 'id', $this->_id);

    if ($this->_action & CRM_Core_Action::DELETE) {
      $this->addButtons(array(
          array(
            'type' => 'next',
            'name' => ts('Delete'),
            'isDefault' => TRUE,
          ),
          array(
            'type' => 'cancel',
            'name' => ts('Cancel'),
          ),
        )
      );
      return;
    }

    $this->add('select', 'link', ts('Link met vorige'), CRM_Geostelsel_BAO_Toegangsgegevens::getLinkOptions(), TRUE);
    $this->add('select', 'type', ts('Type'), CRM_Geostelsel_BAO_Toegangsgegevens::getTypeOptions(), TRUE);
    $this->add('select', 'toegang_tot_contacten_van', ts('Toegang tot contacten'), CRM_Geostelsel_BAO_Toegangsgegevens::getContactOptions(), false);
    $this->add('select', 'group_id', ts('Lid van groepen'), CRM_Geostelsel_BAO_Toegangsgegevens::getGroupOptions(), false);

    if ($this->_action == CRM_Core_Action::UPDATE) {
      $this->assign('aid', $this->_id);
    }

    $this->addButtons(array(
        array(
          'type' => 'next',
          'name' => ts('Save'),
          'isDefault' => TRUE,
        ),
        array(
          'type' => 'cancel',
          'name' => ts('Cancel'),
        ),
      )
    );

  }

  public function addRules() {
    if (!($this->_action & CRM_Core_Action::DELETE)) {
      $this->addFormRule(array(
        'CRM_Geostelsel_Form_Toegangsgegevens',
        'validateType'
      ));
    }
  }

  static function validateType($fields) {
    $errors = array();
    if ($fields['type'] == 'AfdelingMember') {
      if (empty($fields['toegang_tot_contacten_van'])) {
        $errors['toegang_tot_contacten_van'] = ts('Select a contact');
      }
    } elseif ($fields['type'] == 'GroupMember') {
      if (empty($fields['group_id'])) {
        $errors['group_id'] = ts('Select a group');
      }
    }
    if (empty($fields['link'])) {
      $errors['link'] = ts('Link met vorige is vereist');
    }

    if (count($errors)) {
      return $errors;
    }

    return true;
  }

  public function setDefaultValues() {
    $defaults = parent::setDefaultValues();
    if ($this->_id) {
      $values = CRM_Geostelsel_BAO_Toegangsgegevens::getValues($this->_id);
      $defaults['link'] = $values['link'];
      $defaults['type'] = $values['type'];
      $defaults['toegang_tot_contacten_van'] = $values['toegang_tot_contacten_van'];
      $defaults['group_id'] = $values['group_id'];
    }
    return $defaults;
  }

  public function postProcess() {
    $config = CRM_Geostelsel_Config_Toegang::singleton();
    if ($this->_action & CRM_Core_Action::DELETE) {
      CRM_Core_BAO_CustomValue::deleteCustomValue($this->_id, $config->getToegangCustomGroup('id'));
      parent::postProcess();
      return;
    }


    $cg = 'custom_';
    $id = ':-1';
    if ($this->_id) {
      $id = ':'.$this->_id;
    }
    $data['entity_id'] = $this->_submitValues['cid'];
    $data[$cg.$config->getLinkCustomField('id').$id] = $this->_submitValues['link'];
    if (empty($data[$cg.$config->getLinkCustomField('id').$id])) {
      $data[$cg.$config->getLinkCustomField('id').$id] = 'OR';
    }
    $data[$cg.$config->getTypeCustomField('id').$id] = $this->_submitValues['type'];
    if ($this->_submitValues['type'] == 'AfdelingMember') {
      $data[$cg . $config->getToegangAfdelingCustomField('id') . $id] = $this->_submitValues['toegang_tot_contacten_van'];
    } elseif ($this->_submitValues['type'] == 'GroupMember') {
      $data[$cg . $config->getToegangGroepCustomField('id') . $id] = $this->_submitValues['group_id'];
    }

    civicrm_api3('CustomValue', 'create', $data);

    //$redirectUrl = CRM_Utils_System::url('civicrm/civirule/contact/view', 'reset=1&cid='.$this->_submitValues['cid'], TRUE);
    //CRM_Utils_System::redirect($redirectUrl);
    parent::postProcess();
  }
}