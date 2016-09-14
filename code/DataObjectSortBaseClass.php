<?php




class DataObjectSortBaseClass extends Controller
{


    function init() {
        // Only administrators can run this method
        parent::init();
        if( ! Permission::check("CMS_ACCESS_CMSMain")) {
            return $this->permissionFailureStandard();
        }
    }

    function show() {
        return array();
    }


    /**
     *
     * @return string
     */
    protected function SecureFieldToBeUpdated() {
        if(isset($_POST["Field"])) {
            return addslashes($_POST["Field"]);
        }
        $field = $this->getRequest()->param("OtherID");
        if($table = $this->SecureTableToBeUpdated()) {
            if($tableObject = $table::get()->First()) {
                if($tableObject->hasDatabaseField($field)) {
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

    /**
     *
     * @return string
     */
    protected function SecureTableToBeUpdated() {
        if(isset($_POST["Table"])) {
            $table = addslashes($_POST["Table"]);
        }
        else {
            $table = $this->getRequest()->param("ID");
        }
        if(class_exists($table)) {
            return $table;
        }
        else {
            user_error("could not find record: $table", E_USER_ERROR);
        }
    }


    /**
     *
     * @return int
     */
    protected function SecureRecordToBeUpdated() {
        if(isset($_POST["Record"])) {
            return intval($_POST["Record"]);
        }
        if(isset( $_GET["id"])) {
            $record = $_GET["id"];
            return intval($record);
        }
        return 0;
    }


    /**
     *
     *
     * @param  DataObject $obj       [description]
     * @param  string $fieldName     [description]
     * @return FormField
     */
    protected function getFormField($obj, $fieldName) {
        if(!self::$field) {
            self::$field  = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
        }
        return self::$field;
    }

    /**
     *
     * @return string
     */
    protected function HumanReadableTableName() {
        return singleton($this->SecureTableToBeUpdated())->plural_name();
    }

    /**
     *
     * @return string
     */
    public function Link($action = null) {
        $link = Config::inst()->get($this->ClassName, 'url_segment');
        if($action) {
            $link .= "$action/";
        }
        return $link;
    }

    function permissionFailureStandard()
    {
        return Security::permissionFailure($this, _t('Security.PERMFAILURE',' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
    }

}
