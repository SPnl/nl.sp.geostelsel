<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:GemeentesLijst.Update',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Call GemeentesLijst.Update API',
      'description' => 'Update de gemeente lijst met waardes uit de postcode tabel',
      'run_frequency' => 'Daily',
      'api_entity' => 'GemeentesLijst',
      'api_action' => 'Update',
      'parameters' => '',
    ),
  ),
);