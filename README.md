nl.sp.geostelsel
================

This extension requires the org.civicoop.postcodenl extension

This extension implements feature for matching contacts to SP local branches, regions and provinces. 
It also includes the permission system to access contacts a person is allowed to be.

API Documentation
-----------------

This extension comes with the following API functions

- **Geostelsel.update** Updating the link between a contact and the afdeling based on the postcode
- **Geostelsel.getafdeling** Get te related afdelingen based on the postcode or the name
- **GemeenteLijst.update** Updates the lijst with gemeentes in CiviCRM

### Geostelsel.getafdeling

**Input paramaters:**

- _name_ The name or postcode. E.g. name=Ams or name=6716 or name=6716 RG

**Output**

array with the ID of the afdeling and the name of the afdeling