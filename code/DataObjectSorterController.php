<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@description: allows you to sort dataobjects, you need to provide them in this way: http://www.mysite.com/dataobjectsorter/[dataobjectname]/
 *
 *
 *
 *@package: dataobjectsorter
 **/

class DataObjectSorterController extends Controller{

	/**
	 * standard SS variable
	 *
	 */
	private static $allowed_actions = array("sort", "startsort", "dodataobjectsort" );

	private static $sort_field = "";

	/**
	 * returns a link for sorting objects. You can use this in the CMS like this....
	 * <code>
	 * if(class_exists("DataObjectSorterController")) {
	 * 	$fields->addFieldToTab("Root.Position", new LiteralField("AdvertisementsSorter", DataObjectSorterController::popup_link("Advertisement", $filterField = "", $filterValue = "", $linkText = "sort ".Advertisement::$plural_name, $titleField = "FullTitle")));
	 * }
	 * else {
	 * 	$fields->addFieldToTab("Root.Position", new NumericField($name = "Sort", "Sort index number (the lower the number, the earlier it shows up"));
	 * }
	 * </code>
	 *
	 * @param String $className - DataObject Class Name you want to sort
	 * @param String | Int $filterField - Field you want to filter for OR ParentID number (i.e. you are sorting children of Parent with ID = $filterField)
	 * @param String $filterValue - filter field should be equal to this integer OR string. You can provide a list of IDs like this: 1,2,3,4 where the filterFiel is probably equal to ID or MyRelationID
	 * @param String $linkText - text to show on the link
	 * @param String $titleField - field to show in the sort list. This defaults to the DataObject method "getTitle", but you can use "name" or something like that.
	 * @return String
	 */
	public static function popup_link($className, $filterField = "", $filterValue = "", $linkText = "sort this list", $titleField = "") {
		Requirements::javascript("dataobjectsorter/javascript/jquery.simplemodal-1.4.4.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectmodalpopup.js");
		Requirements::themedCSS("dataobjectmodalpopup", "dataobjectsorter");
		$where = "";
		if($filterField) {
			$singletonObj = singleton($className);
			if($singletonObj->hasDatabaseField($filterField)) {
				$where = "\"$filterField\" = '$filterValue'";
			}
		}
		$objects = $className::get();
		if($where) {
			$objects = $objects->where($where);
		}
		$obj = $objects->first();
		if($obj && $obj->canEdit()) {
			$link = 'dataobjectsorter/sort/'.$className."/";
			if($filterField) {
				$link .= $filterField.'/';
			}
			if($filterValue) {
			 $link .= $filterValue.'/';
			}
			if($titleField) {
				$link .= $titleField.'/';
			}
			$link = Director::baseURL().$link;
			return '
			<a href="'.$link.'" class="modalPopUp" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$className.$filterField.$filterValue.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
		}
	}


	/**
	 * the standard action...
	 * no need to add anything here now
	 */
	function sort() {
		return array();
	}


	/**
	 * not sure why we have this here....
	 */
	function startsort() {
		return array();
	}


	/**
	 * runs the actual sorting...
	 */
	function dodataobjectsort($request) {
		$class = $request->param("ID");
		if($class) {
			if(class_exists($class)) {
				$obj = $class::get()->First();
				return $obj->dodataobjectsort();
			}
			else {
				user_error("$class does not exist", E_USER_WARNING);
			}
		}
		else {
			user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
		}
	}

	/**
	 * runs the actual sorting...
	 * @return Object - return dataobject set of items to be sorted
	 */
	public function Children() {
		$class = $this->request->param("ID");
		if($class) {
			if(class_exists($class)) {
				$where = '';
				$filterField = Convert::raw2sql($this->request->param("OtherID"));
				$filterValue = Convert::raw2sql($this->request->param("ThirdID"));
				$titleField = Convert::raw2sql($this->request->param("FourthID"));
				$objects = $class::get();
				if($filterField && $filterValue) {
					$filterValue = explode(",",$filterValue);
					$objects = $objects->filter(array($filterField => $filterValue));
				}
				elseif(is_numeric($filterField)) {
					$objects = $objects->filter(array("ParentID" => $filterField));
				}
				$singletonObj = singleton($class);
				$sortField = $singletonObj->SortFieldForDataObjectSorter();
				$objects = $objects->sort($sortField, "ASC");
				if($objects->count()) {
					foreach($objects as $obj) {
						if($titleField) {
							$method = "get".$titleField;
							if($obj->hasMethod($method)) {
								$obj->SortTitle = $obj->$method();
							}
							else {
								$method = $titleField;
								if($obj->hasMethod($method)) {
									$obj->SortTitle = $obj->$method();
								}
								else {
									$obj->SortTitle = $obj->$titleField;
								}
							}
						}
						else {
							$obj->SortTitle = $obj->getTitle();
						}
					}
					self::add_requirements($class);
					return $objects;
				}
				else {
					return null;
				}
			}
			else {
				user_error("$class does not exist", E_USER_WARNING);
			}
		}
		else {
			user_error("Please make sure to provide a class to sort e.g. http://www.sunnysideup.co.nz/dataobjectsorter/MyLongList - where MyLongList is the DataObject you want to sort.", E_USER_WARNING);
		}
	}

	/**
	 * adds
	 * @param String $className - name of the class being sorted
	 */
	function add_requirements($className) {
		//Requirements::javascript(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		//Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js");
		Requirements::javascript(Director::protocol()."ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.9.1.custom.min.js");
		Requirements::javascript("dataobjectsorter/javascript/dataobjectsorter.js");
		Requirements::themedCSS("dataobjectsorter", "dataobjectsorter");
		Requirements::customScript('var DataObjectSorterURL = "'.Director::absoluteURL("dataobjectsorter/dodataobjectsort/".$className."/").'";', 'initDataObjectSorter');
	}

	public function index(){
		$action = $this->request->param("Action");
		return $this->$action($this->request);
	}

	public function Link($action = null) {
		$link = "dataobjectsorter/";
		if($action) {
			$link .= "$action/";
		}
		return $link;
	}


}
