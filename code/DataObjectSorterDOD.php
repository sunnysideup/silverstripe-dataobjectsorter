<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: adds dataobject sorting functionality
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataExtension {

	private static $db = array(
		'Sort' => 'Int'
	);

	private static $casting = array(
		'SortTitle' => 'Varchar'
	);

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
					$object = $baseDataClass::get()->byID($id);
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
						else {

						}
					}
					else {
						return _t("DataObjectSorter.NOACCESS", "You do not have access rights to make these changes.");
					}
				}
			}
			else {
				return _t("DataObjectSorter.ERROR2", "Error 2");
			}
		}
		else {
			return _t("DataObjectSorter.ERROR1", "Error 1");
		}
		return _t("DataObjectSorter.UPDATEDRECORDS", "Updated record(s)");
	}

	/**
	 *legacy function
	 **/
	function dataObjectSorterPopupLink($filterField = '', $filterValue = '') {
		return DataObjectSorterController::popup_link($this->owner->ClassName, $filterField, $filterValue, $linkText = "Sort ".$this->owner->plural_name());
	}

	public function updateCMSFields(FieldList $fields) {
		$fields->removeFieldFromTab("Root.Main", "Sort");
		$link = self::dataObjectSorterPopupLink();
		$fields->addFieldToTab("Root.Sort", new LiteralField("DataObjectSorterPopupLink", $link));
		return $fields;
	}

}


