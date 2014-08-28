<?php

class CRM_Geostelsel_GeoInfo_Data {
  
  protected $afdeling_contact_id = false;
  
  protected $regio_contact_id = false;
  
  protected $provincie_contact_id = false;
  
  public function __construct($afdelings_contact_id) {
    $this->afdeling_contact_id = $afdelings_contact_id;
    if ($this->afdeling_contact_id) {
      $this->regio_contact_id = CRM_Geostelsel_GeoInfo_Utils::getRegioByAfdeling($this->afdeling_contact_id);
    }
    if ($this->regio_contact_id) {
      $this->provincie_contact_id = CRM_Geostelsel_GeoInfo_Utils::getProvincieByRegio($this->regio_contact_id);
    }
  }
  
  public function getAfdelingsContactId() {
    return $this->afdeling_contact_id;
  }
  
  public function getRegioContactId() {
    return $this->regio_contact_id;
  }
  
  public function getProvincieContactId() {
    return $this->provincie_contact_id;
  }
  
}

