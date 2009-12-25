<?php


/**
*@author Nicolaas [at] sunnysideup.co.nz
*
**/

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START dataobjectsorter MODULE ----------------===================
// You can extend a DataObject OR SiteTree
//Object::add_extension('SiteTree', 'DataObjectSorterDOD');
//DataObjectSorterDOD::set_also_update_sort_field(true);
//DataObjectSorterDOD::set_do_not_add_alternative_sort_field(true);
Director::addRules(90, array(
	'dataobjectsorter//$Action/$ID/$OtherID' => 'DataObjectSorterController',
));
Director::addRules(90, array(
	'dataobjectonefieldupdate//$Action/$ID/$OtherID' => 'DataObjectOneFieldUpdateController',
));
//===================---------------- END dataobjectsorter MODULE ----------------===================

