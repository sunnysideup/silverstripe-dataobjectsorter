<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: allows you to quickly review and update one field for all records
 * e.g. update price for all products
 *@todo: NOT COMPLETED YET!
 *@package: dataobjectsorter
 **/

class DataObjectOneFieldUpdate  extends Controller{

	function updatefield($request = null) {
		if(Permission::check("ADMIN")) {
			$table = $request->param("ID");
			$id = intval($request->param("OtherID"));
			$field = intval($request->getVar("f"));
			$newValue = intval($request->getVar("v"));
			if($memberID = Member::currentUserID() ) {
				if(class_exists($table) && $id && ($newValue || $newValue === 0)) {
					if($obj = DataObject::get_by_id($table, $id)) {
						if($obj->hasField($field)) {
							$obj->$field = $newValue;
							if($obj instanceOf SiteTree) {
								$obj->writeToStage();
								$obj->publish("Stage", "Live");
							}
							else {
								$obj->write();
							}
							return $obj->Name . ".$field quantity updated to ".$newValue;
						}
						else {
							user_error("field does not exist", E_ERROR);
						}
					}
					else {
						user_error("could not find record: $table, $id ", E_ERROR);
					}
				}
				else {
					user_error("data object specified: $table or id: $id or newValue: $newValue is not valid", E_ERROR);
				}
			}
			else {
				user_error("you need to be logged-in to make the changes", E_ERROR);
			}
		}
		else {
			user_error("sorry, you do not have access to this page", E_ERROR);
		}
	}


}