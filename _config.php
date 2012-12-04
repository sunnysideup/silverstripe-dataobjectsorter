<?php


/**
*@author Nicolaas [at] sunnysideup.co.nz
*
**/

//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START dataobjectsorter MODULE ----------------===================
//MUST SET

//MAY SET
// You can extend a DataObject OR SiteTree
//Object::add_extension('SiteTree', 'DataObjectSorterDOD');
//Object::add_extension('MyDataObject', 'DataObjectSorterDOD');
//DataObjectOneFieldUpdateController::set_page_size(10)
//===================---------------- END dataobjectsorter MODULE ----------------===================

Director::addRules(90, array(
	//sorter
	'dataobjectsorter/$Action/$ID/$OtherID/$ThirdID/$FourthID' => 'DataObjectSorterController',

	//one field
	'dataobjectonefieldupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneFieldUpdateController',

	//one field for one record
	'dataobjectonefieldonerecordupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneFieldOneRecordUpdateController',

	//one record
	'dataobjectonerecordupdate/$Action/$ID/$OtherID/$ThirdID' => 'DataObjectOneRecordUpdateController',
));
