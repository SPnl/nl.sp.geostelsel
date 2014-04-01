<?php

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Geostelsel_Form_AddGemeenteTargetRule extends CRM_Autorelationship_Form_AddTargetRule {
  
  function buildQuickForm() {

    // add form elements
    $this->add(
      'text', // field type
      'gemeente', // field name
      ts('Gemeente'), // field label
      array( // list of options
        'class' => 'huge ac_input',
        'autocomplete' => 'off',
      ), 
      true // is required
    );
    
    $this->addButtons(array(
      array(
        'type' => 'done',
        'name' => ts('Save'),
        'isDefault' => TRUE,
      ),
      array(
        'type' => 'cancel',
        'name' => ts('Cancel'),
      ),
    ));

    parent::buildQuickForm();
  }

  function postProcess() {
    //add gemeente to database
    $gemeente = $this->exportValue('gemeente');
    
    //check if gemeente already exist
    $exist = false;    
    $sql = "SELECT * FROM `civicrm_autorelationship_contact_gemeente` WHERE LOWER(`gemeente`) = LOWER(%1) AND `contact_id` = %2";
    $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($gemeente, 'String'),
        '2' => array($this->targetContactId, 'Integer')
    ));
    if ($dao->fetch()) {
      $exist = true;
    }
    
    if (!$exist) {
      $sql = "INSERT INTO `civicrm_autorelationship_contact_gemeente` (`gemeente`, `contact_id`) VALUES(%1, %2)";
      $dao = CRM_Core_DAO::executeQuery($sql, array(
        '1' => array($gemeente, 'String'),
        '2' => array($this->targetContactId, 'Integer')
        ),
        TRUE,
        'CRM_Autorelationship_DAO'
      );
      
      $this->new_entity_id = $dao->getInsterId();
    }
    
    parent::postProcess();
  }
  
}