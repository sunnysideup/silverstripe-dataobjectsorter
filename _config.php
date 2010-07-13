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
//===================---------------- END dataobjectsorter MODULE ----------------===================

Director::addRules(90, array(
	//sorter
	'dataobjectsorter/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectSorterController',

	//one field
	'dataobjectonefieldupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneFieldUpdateController',

	//one field for one record
	'dataobjectonefieldonerecordupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneFieldOneRecordUpdateController',

	//one record
	'dataobjectonerecordupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneRecordUpdateController',
));
