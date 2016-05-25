<?php

/**
 * @author nicolaas [at] sunnysideup.co.nz
 * @description: adds dataobject sorting functionality
 *
 * @package: dataobjectsorter
 **/

class DataObjectSorterDOD extends DataExtension {

	/**
	 *
	 * @var string
	 */ 
	private static $sort_field = "";

	/**
	 * standard SS variable
	 *
	 */ 
	private static $db = array(
		'Sort' => 'Int'
	);

	/**
	 * standard SS variable
	 *
	 */ 
	private static $casting = array(
		'SortTitle' => 'Varchar'
	);

	/**
	 * action sort
	 * @return string
	 */ 
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
							}
							else {
								$object->write();
							}
						}
						else {
							//do nothing
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
	 *
	 * standard SS method
	 * @param FieldList $fields
	 * @return FieldList
	 */ 
	public function updateCMSFields(FieldList $fields) {
		$fields->removeFieldFromTab("Root.Main", $this->SortFieldForDataObjectSorter());
		if(!$this->owner instanceof SiteTree) {
			$link = $this->dataObjectSorterPopupLink();
			$fields->addFieldToTab("Root.Sort", new LiteralField("DataObjectSorterPopupLink", "<h2 class='dataObjectSorterDODLink'>".$link."</h2>"));
		}
		return $fields;
	}

	/**
	 * simplified method
	 *
	 * @param string $filterField
	 * @param string $filterValue
	 * @param string $alternativeTitle
	 * 
	 * @return string HTML
	 **/
	function dataObjectSorterPopupLink($filterField = '', $filterValue = '', $alternativeTitle = '') {
		if($alternativeTitle) {
			$linkText = $alternativeTitle;
		}
		else {
			$linkText = "Sort ".$this->owner->plural_name();
		}
		return DataObjectSorterController::popup_link($this->owner->ClassName, $filterField, $filterValue, $linkText);
	}

	/**
	 * returns field name for sorting
	 * 
	 * @return string 
	 **/
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
		elseif($this->owner->hasField("SortNumber")) {
			$field = "SortNumber";
		}
		else {
			user_error("No field Sort or AlternativeSortNumber (or $sortField) was found on data object: ".$class, E_USER_WARNING);
		}
		return $field;
	}
}


