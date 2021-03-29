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
    public const CAN_DO_STUFF = 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION';

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

    protected function init()
    {
        // Only administrators can run this method
        parent::init();
        if (! Permission::check('DATA_OBJECT_SORT_AND_EDIT_PERMISSION')) {
            return $this->permissionFailureStandard();
        }
    }

    public function show()
    {
        return $this->renderWith(static::class);
    }

    /**
     * @return string
     */
    public function Link($action = null)
    {
        $link = $this->config()->get('url_segment') . '/';
        if ($action) {
            $link .= "{$action}/";
        }
        return $link;
    }

    public function permissionFailureStandard()
    {
        return Security::permissionFailure($this, _t('Security.PERMFAILURE', ' This page is secured and you need administrator rights to access it. Enter your credentials below and we will send you right along.'));
    }

    protected function SecureFieldToBeUpdated() : string
    {
        $obj = $this->SecureObjectToBeUpdated();
        if ($obj) {
            if (isset($_POST['Field'])) {
                return addslashes($_POST['Field']);
            } else {
                $field = $this->getRequest()->param('OtherID');
            }
            if ($obj->hasDatabaseField($field)) {
                return $field;
            }
            user_error("{$field} does not exist on {${$className}}", E_USER_ERROR);
        } else {
            user_error('there is no table specified', E_USER_ERROR);
        }
    }

    public function SecureFieldToBeUpdatedNice()
    {
        $field = $this->SecureFieldToBeUpdated();
        if($field !== '') {
            $labels = $this->SecureObjectToBeUpdated()->FieldLabels();
            return $labels[$field] ?? $field;
        }
    }

    protected $objectCache = [];

    protected function SecureObjectToBeUpdated()
    {
        $className = $this->SecureClassNameToBeUpdated();
        if ($className !== '') {
            if(! isset($this->objectCache[$className])) {
                $this->objectCache[$className] = DataObject::get_one($className);
            }
            return $this->objectCache[$className];
        }
        user_error('there is no table specified', E_USER_ERROR);
    }


    /**
     * @return string
     */
    protected function SecureClassNameToBeUpdated()
    {
        $classNameString = isset($_POST['Table']) ? addslashes($_POST['Table']) : $this->getRequest()->param('ID');
        $className = self::stringToClassName($classNameString);
        if (class_exists($className)) {
            return $className;
        }
        user_error("Could not find className: {${$className}}", E_USER_ERROR);
    }

    /**
     * @return string
     */
    protected function SecureClassNameToBeUpdatedAsString()
    {
        return self::classNameToString($this->SecureClassNameToBeUpdated());
    }

    /**
     * @return int
     */
    protected function SecureRecordToBeUpdated()
    {
        if (isset($_POST['Record'])) {
            return (int) $_POST['Record'];
        }
        if (isset($_GET['id'])) {
            $record = $_GET['id'];
            return (int) $record;
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
        return \Singleton($this->SecureClassNameToBeUpdated())->plural_name();
    }

    protected static function classNameToString(string $className): string
    {
        return str_replace('\\', '-', $className);
    }

    protected static function stringToClassName(string $className): string
    {
        return str_replace('-', '\\', $className);
    }
}
