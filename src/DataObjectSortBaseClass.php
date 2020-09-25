<?php

namespace Sunnysideup\DataobjectSorter;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;

class DataObjectSortBaseClass extends Controller implements PermissionProvider
{
    /**
     * Permission for user management.
     *
     * @var string
     */
    const CAN_DO_STUFF = 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION';

    private static $url_handlers = [
        '$Action//$ID/$OtherID/$ThirdID/$FourthID/$FifthID' => 'handleAction',
    ];

    private static $allowed_actions = [
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    private static $field = '';

    public function providePermissions()
    {
        return [
            DataObjectSortBaseClass::CAN_DO_STUFF => [
                'name' => _t(
                    'DataObjectSortBaseClass.PERMISSION_MANAGE_USERS_DESCRIPTION',
                    'Quick updates and edits'
                ),
                'help' => _t(
                    'DataObjectSortBaseClass.PERMISSION_MANAGE_USERS_HELP',
                    'Allows for certain data to be sorted, edited, etc... This is around quick edits'
                ),
                'category' => _t('DataObjectSortBaseClass.PERMISSIONS_CATEGORY', 'Miscellaneous'),
                'sort' => 100,
            ],
        ];
    }

    public function init()
    {
        // Only administrators can run this method
        parent::init();
        if (! Permission::check('DATA_OBJECT_SORT_AND_EDIT_PERMISSION')) {
            return $this->permissionFailureStandard();
        }
    }

    public function show()
    {
        return $this->renderWith(ClassInfo::shortName($this));
    }

    /**
     * @return string
     */
    public function Link($action = null)
    {
        $link = $this->config()->get('url_segment') . '/';
        if ($action) {
            $link .= "${action}/";
        }
        return $link;
    }

    public function permissionFailureStandard()
    {
        return Security::permissionFailure($this, _t('Security.PERMFAILURE', ' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
    }

    /**
     * @return string
     */
    protected function SecureFieldToBeUpdated()
    {
        if (isset($_POST['Field'])) {
            return addslashes($_POST['Field']);
        }
        $field = $this->getRequest()->param('OtherID');
        if ($table = $this->SecureTableToBeUpdated()) {
            if ($tableObject = DataObject::get_one($table)) {
                if ($tableObject->hasDatabaseField($field)) {
                    return $field;
                }
                user_error("${field} does not exist on ${table}", E_USER_ERROR);
            } else {
                user_error("there are no records in ${table}", E_USER_ERROR);
            }
        } else {
            user_error('there is no table specified', E_USER_ERROR);
        }
    }

    /**
     * @return string
     */
    protected function SecureTableToBeUpdated()
    {
        if (isset($_POST['Table'])) {
            $table = addslashes($_POST['Table']);
        } else {
            $table = $this->getRequest()->param('ID');
        }
        if (class_exists($table)) {
            return $table;
        }
        user_error("could not find record: ${table}", E_USER_ERROR);
    }

    /**
     * @return int
     */
    protected function SecureRecordToBeUpdated()
    {
        if (isset($_POST['Record'])) {
            return intval($_POST['Record']);
        }
        if (isset($_GET['id'])) {
            $record = $_GET['id'];
            return intval($record);
        }
        return 0;
    }

    /**
     * @param  DataObject $obj       
     * @param  string $fieldName     
     * @return \SilverStripe\Forms\FormField
     */
    protected function getFormField($obj, $fieldName)
    {
        if (! self::$field) {
            self::$field = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
        }
        return self::$field;
    }

    /**
     * @return string
     */
    protected function HumanReadableTableName()
    {
        return singleton($this->SecureTableToBeUpdated())->plural_name();
    }
}
