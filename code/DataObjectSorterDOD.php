<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: adds dataobject sorting functionality
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataObjectDecorator {

	static function set_also_update_sort_field($b) {user_error("This method has been depreciated. You can remove this notice by removing DataObjectSorterDOD::set_also_update_sort_field from your _config file.", E_USER_NOTICE);}

	static function set_do_not_add_alternative_sort_field($b) {user_error("This method has been depreciated. You can remove this notice by removing DataObjectSorterDOD::set_do_not_add_alternative_sort_field from your _config file. ", E_USER_NOTICE);}

	function extraStatics(){
		//this is not actually working because in dev/build, this statement is executed BEFORE the settting above is applied!
		//maybe add field in another way????
		return array(
			'db' =>   array(
				"Sort" => "Int"
			)
		);
	}



	function dodataobjectsort() {
		$i = 0;
		$extraSet = '';
		$extraWhere = '';
		$field = "Sort";
		$baseDataClass = ClassInfo::baseDataClass($this->owner->ClassName);
		if($baseDataClass) {
			if(isset ($_REQUEST["dos"])) {
				foreach ($_REQUEST['dos'] as $position => $id) {
					$id = intval($id);
					$object = DataObject::get_by_id($baseDataClass, $id);
					//we add one because position 0 is not good.
					$position = intval($position)+1;
					if($object && $object->canEdit()) {
						if($object->$field != $position) {
							$object->$field = $position;
							//hack for site tree
							if("SiteTree" == $baseDataClass) {
								$object->writeToStage('Stage');
								$object->Publish('Stage', 'Live');
								$object->Status = "Published";
							}
							else {
								$object->write();
							}
						}
					}
					else {
						return _t("DataObjectSorter.NOACCESS", "You do not have access rights to make these changes.");
					}

				}
			}
		}
		return _t("DataObjectSorter.UPDATEDRECORDS", "Updated record(s)");
	}

	/**
	 *legacy function
	 **/

	function dataObjectSorterPopupLink($filterField = '', $filterValue = '') {
		return DataObjectSorterController::popup_link($this->owner->ClassName, $filterField, $filterValue, $linkText = "Sort ".$this->owner->plural_name());
	}

	function updateCMSFields(&$fields) {
		$fields->removeFieldFromTab("Root.Main", "Sort");
		$link = self::dataObjectSorterPopupLink();
		$fields->addFieldToTab("Root.Sort", new LiteralField("DataObjectSorterPopupLink", $link));
	}

}


