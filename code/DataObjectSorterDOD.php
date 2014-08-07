<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: adds dataobject sorting functionality
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataExtension {

	private static $sort_field = "";

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
		$sortField = $this->SortFieldForDataObjectSorter();
		$baseDataClass = ClassInfo::baseDataClass($this->owner->ClassName);
		if($baseDataClass) {
			if(isset ($_REQUEST["dos"])) {
				foreach ($_REQUEST['dos'] as $position => $id) {
					$id = intval($id);
					$object = $baseDataClass::get()->byID($id);
					//we add one because position 0 is not good.
					$position = intval($position)+1;
					if($object && $object->canEdit()) {
						if($object->$sortField != $position) {
							$object->$sortField = $position;
							//hack for site tree
							if($object instanceof SiteTree) {
								$object->writeToStage('Stage');
								$object->Publish('Stage', 'Live');
								$object->Status = "Published";
								debug::log("Sorting $object->MenuTitle to $position on $sortField");
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

	public function updateCMSFields(FieldList $fields) {
		$fields->removeFieldFromTab("Root.Main", $this->SortFieldForDataObjectSorter());
		if(!$this->owner instanceof SiteTree) {
			$link = self::dataObjectSorterPopupLink();
			$fields->addFieldToTab("Root.Sort", new LiteralField("DataObjectSorterPopupLink", $link));
		}
		return $fields;
	}

	/**
	 * simplified method
	 * @return HTML
	 **/
	function dataObjectSorterPopupLink($filterField = '', $filterValue = '') {
		return DataObjectSorterController::popup_link($this->owner->ClassName, $filterField, $filterValue, $linkText = "Sort ".$this->owner->plural_name());
	}


	public function SortFieldForDataObjectSorter() {
		$sortField = Config::inst()->get("DataObjectSorterDOD", "sort_field");
		$field = "Sort";
		if($sortField && $this->owner->hasField($sortField)) {
			$field = $sortField;
		}
		elseif($this->owner->hasField("AlternativeSortNumber")) {
			$field = "AlternativeSortNumber";
		}
		elseif($this->owner->hasField("Sort")) {
			$field = "Sort";
		}
		else {
			user_error("No field Sort or AlternativeSortNumber (or $sortField) was found on data object: ".$class, E_USER_WARNING);
		}
		return $field;
	}
}


