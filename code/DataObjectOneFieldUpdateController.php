<?php
/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@todo:
 *  pagination
 *  use scaffolding method (of some sort) to get right field type
 * (and many other things)
 *
 *@package: dataobjectsorter
 *@description: allows you to quickly review and update one field for all records
 * e.g. update price for all products
 * URL is like this
 * dataobjectonefieldupdate/update/$Action/$ID/$OtherID
 * dataobjectonefieldupdate/[show]/[updatefield]/[tablename]/[fieldname]
 *
 **/

class DataObjectOneFieldUpdateController extends Controller{

    private static $page_size = 50;

    private static $field = null;

    private static $objects = null;

    private static $objects_without_field = null;

    public static function popup_link($ClassName, $FieldName, $where = '', $sort = '', $linkText = '', $titleField = "Title") {
        DataObjectSorterRequirements::popup_link_requirements();
        $obj = singleton($ClassName);
        $params = array();
        if($where) {
            $params["where"] = "where=".urlencode($where);
        }
        if($sort) {
            $params["sort"] = "sort=".urlencode($sort);
        }
        if($titleField){
            $params["titlefield"] = "titlefield=".urlencode($titleField);
        }
        if($obj->canEdit()) {
            $link = '/dataobjectonefieldupdate/show/'.$ClassName."/".$FieldName.'/?'.implode("&amp;", $params);
            if(!$linkText) {
                $linkText = 'click here to edit';
            }
            return '
                <a href="'.$link.'" class="modalPopUp" data-width="800" data-height="600" data-rel="window.open(\''.$link.'\', \'sortlistFor'.$ClassName.$FieldName.'\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left = 440,top = 200\'); return false;">'.$linkText.'</a>';
        }
    }

    private static $allowed_actions = array("updatefield", "show");

    function init() {
        //must set this first ...
        Config::inst()->update('SSViewer', 'theme_enabled', Config::inst()->get('DataObjectSorterRequirements', 'run_through_theme'));
        parent::init();
        // Only administrators can run this method
        if(!Permission::check("CMS_ACCESS_CMSMain")) {
            Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
            return;
        }
        DataObjectSorterRequirements::popup_requirements('onefield');
        $url = Director::absoluteURL("dataobjectonefieldupdate/updatefield/");
        Requirements::customScript("DataObjectOneFieldUpdateURL = '".$url."'");
    }


    function show() {
        return array();
    }

    function updatefield($request = null) {
        if(Permission::check("CMS_ACCESS_CMSMain")) {
            $updateMessage = "";
            $updateCount = 0;
            $table = $request->param("ID");
            $field = $request->param("OtherID");
            $ids = explode(",",$request->getVar("id"));
            $newValue = $request->getVar("value");
            if($memberID = Member::currentUserID() ) {
                if(class_exists($table) && count($ids) > 0 && ($newValue || $newValue == 0)) {
                    foreach($ids as $id) {
                        if(intval($id)) {
                            if($obj = $table::get()->byID($id)) {
                                if($obj->hasField($field)) {
                                    if($obj->canEdit()) {
                                        $obj->$field = $newValue;
                                        if($obj instanceOf SiteTree) {
                                            $obj->writeToStage("Stage");
                                            $obj->publish("Stage", "Live");
                                        }
                                        else {
                                            $obj->write();
                                        }
                                        if($obj->hasMethod("Title")) {
                                            $title = $obj->Title();
                                        }
                                        elseif($obj->hasMethod("getTitle")) {
                                            $title = $obj->getTitle();
                                        }
                                        elseif($title = $obj->Title) {
                                            //do nothing
                                        }
                                        elseif($title = $obj->Name) {
                                            //do nothing
                                        }
                                        else {
                                            $title = $obj->ID;
                                        }
                                        $updateCount++;
                                        $updateMessage .= "Record updated: <i class=\"fieldTitle\">$field</i>  for <i class=\"recordTitle\">".$title ."</i> updated to <i class=\"newValue\">".$newValue."</i><br />";
                                    }
                                }
                                else {
                                    user_error("field does not exist", E_USER_ERROR);
                                }
                            }
                            else {
                                user_error("could not find record: $table, $id ", E_USER_ERROR);
                            }
                        }
                    }
                    if($updateCount > 1) {
                        return "$updateCount records Updated";
                    }
                    else {
                        return $updateMessage;
                    }
                }
                else {
                    user_error("data object specified: '$table' or id count: '".count($ids)."' or newValue: '$newValue' is not valid", E_USER_ERROR);
                }
            }
            else {
                user_error("you need to be logged in to make the changes", E_USER_ERROR);
            }
        }
        else {
            user_error("sorry, you do not have access to this page", E_USER_ERROR);
        }
    }

    //used in template
    public function DataObjectsToBeUpdated() {
        if(!self::$objects) {
            $table = $this->SecureTableToBeUpdated();
            $field = $this->SecureFieldToBeUpdated();
            $where = '';
            if(isset($this->requestParams["where"]) && $this->requestParams["where"]) {
                $where = urldecode($this->requestParams["where"]);
            }
            $sort = '';
            if(isset($this->requestParams["sort"]) && $this->requestParams["sort"]) {
                $sort = urldecode($this->requestParams["sort"]);
            }
            $titleField = 'Title';
            if(isset($this->requestParams["titlefield"]) && $this->requestParams["titlefield"]) {
                $titleField = urldecode($this->requestParams["titlefield"]);
            }
            $start = 0;
            if(isset($this->requestParams["start"])) {
                $start = intval($this->requestParams["start"]);
            }

            if(isset($_GET["debug"])) {
                print_r("SELECT * FROM $table $where SORT BY $sort LIMIT $start, ". Config::inst()->get("DataObjectOneFieldUpdateController", "page_size"));
            }

            $dataList = $table::get()->where($where)->sort($sort);

            $objects = new PaginatedList($dataList, $this->request);
            $objects->setPageLength(Config::inst()->get("DataObjectOneFieldUpdateController", "page_size"));
            $arrayList = new ArrayList();
            if($objects->count()) {
                $testObject = $objects->first();
                if(!$testObject->canEdit()) {
                    Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
                    return;
                }
                foreach($objects as $obj) {
                    $obj->FormField = $obj->dbObject($field)->scaffoldFormField();
                    $obj->FormField->setName($obj->ClassName."/".$obj->ID);
                    //3.0TODO Check that I work vvv.
                    $obj->FormField->addExtraClass("updateField");
                    $obj->FieldToBeUpdatedValue = $obj->$field;
                    $obj->FormField->setValue($obj->$field);
                    if($obj->hasMethod($titleField)) {
                        $title = $obj->$titleField();
                    }
                    elseif($obj->hasMethod("get".$titleField)) {
                        $titleField = "get".$titleField;
                        $title = $obj->$titleField();
                    }
                    else {
                        $title = $obj->$titleField;
                    }
                    $arrayList->push(new ArrayData(array("FormField" => $obj->FormField, "MyTitle" => $title)));
                }
            }
            self::$objects = $arrayList;
            self::$objects_without_field = $objects;
        }

        return self::$objects;
    }

    function PaginatedListItems() {
        $this->DataObjectsToBeUpdated();
        return self::$objects_without_field;
    }

    protected function getFormField($obj, $fieldName) {
        if(!self::$field) {
            self::$field  = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
        }
        return self::$field;
    }

    protected function SecureFieldToBeUpdated() {
        $field = $this->getRequest()->param("OtherID");
        if($table = $this->SecureTableToBeUpdated()) {
            if($tableObject = $table::get()->First()) {
                if($tableObject->hasField($field)) {
                    return $field;
                }
                else {
                    user_error("$field does not exist on $table", E_USER_ERROR);
                }
            }
            else {
                user_error("there are no records in $table", E_USER_ERROR);
            }
        }
        else {
            user_error("there is no table specified", E_USER_ERROR);
        }
    }

    protected function SecureTableToBeUpdated() {
        $table = $this->getRequest()->param("ID");
        if(class_exists($table)) {
            return $table;
        }
        else {
            user_error("could not find record: $table", E_USER_ERROR);
        }
    }

    protected function HumanReadableTableName() {
        return singleton($this->SecureTableToBeUpdated())->plural_name();
    }

    public function Link($action = null) {
        $link = "dataobjectonefieldupdate/";
        if($action) {
            $link .= "$action/";
        }
        return $link;
    }

}
