<?php

namespace Sunnysideup\DataobjectSorter;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;

use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;

class DataObjectSortBaseClass extends Controller implements PermissionProvider
{
    /**
     * Permission for user management.
     *
     * @var string
     */
    public const CAN_DO_STUFF = 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION';

    protected $objectCache = [];

    private static $page_size = 1000;

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

    public function show()
    {
        return $this->renderWith(static::class);
    }

    /**
     * @param null|mixed $action
     *
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

    /**
     * @return HTTPResponse
     */
    public function permissionFailureStandard(?string $message = null)
    {
        if (! $message) {
            _t(
                'Security.PERMFAILURE',
                '
                    This page is secured and you need administrator rights to access it.
                    Enter your credentials below and we will send you right along.
                '
            );
        }

        return Security::permissionFailure($this, $message);
    }

    public function SecureFieldToBeUpdatedNice()
    {
        $field = $this->SecureFieldToBeUpdated();
        if ('' !== $field) {
            $labels = $this->SecureSingletonToBeUpdated()->FieldLabels();

            return $labels[$field] ?? $field;
        }
    }

    protected static function params_builder(array $array): array
    {
        // extract($array);
        // $params = [];
        // if ($where) {
        //     $params['where'] = $where;
        // }
        // if ($sort) {
        //     $params['sort'] =  $sort;
        // }
        // if ($titleField) {
        //     $params['titlefield'] = $titleField;
        // }
        // if ($filterField) {
        //     $params['filterField'] = $titleField;
        // }
        // if ($filterValue) {
        //     $params['filterValue'] = $filterValue;
        // }
        return $array;
    }

    protected function init()
    {
        // Only administrators can run this method
        parent::init();
        if (! Permission::check('DATA_OBJECT_SORT_AND_EDIT_PERMISSION')) {
            return $this->permissionFailureStandard();
        }
    }

    protected function SecureFieldToBeUpdated(): string
    {
        $obj = $this->SecureSingletonToBeUpdated();
        if ($obj) {
            if (isset($_POST['Field'])) {
                return addslashes($_POST['Field']);
            }
            $field = $this->getRequest()->param('OtherID');

            if ($obj->hasDatabaseField($field)) {
                return $field;
            }
            $className = $this->SecureClassNameToBeUpdated();
            user_error($field . ' does not exist on ' . $className, E_USER_ERROR);
        } else {
            user_error('there is no table specified', E_USER_ERROR);
        }

        return '';
    }

    protected function SecureSingletonToBeUpdated()
    {
        $className = $this->SecureClassNameToBeUpdated();
        if (class_exists($className)) {
            if (! isset($this->objectCache[$className])) {
                $this->objectCache[$className] = DataObject::get_one($className);
            }

            return $this->objectCache[$className];
        }
        user_error('there is no table / classname specified', E_USER_ERROR);
    }

    /**
     * returns a ClassName that is a real classname.
     * it may also return a table.
     */
    protected function SecureClassNameToBeUpdated(): string
    {
        $classNameString = $this->getRequest()->param('ID');
        $className = self::stringToClassName($classNameString);
        if (! class_exists($className) && class_exists($classNameString)) {
            $className = $classNameString;
        }
        if (class_exists($className)) {
            return $className;
        }
        user_error('Could not find className: ' . $className, E_USER_ERROR);

        return '';
    }

    protected function SecureClassNameToBeUpdatedAsString(): string
    {
        return self::classNameToString($this->SecureClassNameToBeUpdated());
    }

    protected function SecureRecordIdToBeUpdated(): int
    {
        if (isset($_POST['Record'])) {
            return (int) $_POST['Record'];
        }
        if (isset($_GET['id'])) {
            $record = $_GET['id'];

            return (int) $record;
        }

        $id = (int) $this->getRequest()->param('OtherID');

        return $id;
    }

    /**
     * @param DataObject $obj
     * @param string     $fieldName
     *
     * @return \SilverStripe\Forms\FormField
     */
    protected function getFormField($obj, $fieldName)
    {
        if (! self::$field) {
            self::$field = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
        }

        return self::$field;
    }

    protected function HumanReadableTableName(): string
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

    protected static function link_only_maker(string $controllerClassName, string $action, $params)
    {

        return Injector::inst()->get($controllerClassName)
            ->Link($action) . '?' . http_build_query($params);
    }

    protected static function link_html_maker(string $link, string $cssClasses, string $code, string $linkText): string
    {
        if ($link) {
            DataObjectSorterRequirements::popup_link_requirements();

            return '
                <a href="' . $link . '"
                    class="' . $cssClasses . '"
                    data-width="800"
                    data-height="600"
                    data-rel="window.open(\'' . $link . "', 'update" . $code . '\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left=20,top=20\'); return false;"
                >' . $linkText . '</a>';
        }

        return '';
    }

    /**
     * returns an HTTPResponse in case of an error and a DataObject if it can be edited.
     *
     * @return DataObject|HTTPResponse
     */
    protected function getRecordAndCheckPermissions()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $className = $this->SecureClassNameToBeUpdated();
        $recordId = $this->SecureRecordIdToBeUpdated();
        $obj = $className::get()->byID($recordId);
        if (! $obj) {
            user_error('record could not be found!', E_USER_ERROR);

            return $this->permissionFailureStandard('Could not find record, please login again.');
        }
        if (! $obj->canEdit()) {
            return $this->permissionFailureStandard();
        }

        return $obj;
    }

    protected function getRecords()
    {
        Versioned::set_reading_mode('Stage.Stage');
        $className = $this->SecureClassNameToBeUpdated();
        if ($className) {
            $filterField = (string) Convert::raw2sql(urldecode($this->request->getVar('filterField')));
            $filterValue = (string) Convert::raw2sql(urldecode($this->request->getVar('filterValue')));
            $where = (string) Convert::raw2sql(urldecode($this->request->getVar('where')));
            $sort = (string) Convert::raw2sql(urldecode($this->request->getVar('sort')));
            $objects = $class::get();
            if ($filterField && $filterValue) {
                $filterValue = explode(',', $filterValue);
                $objects = $objects->filter([$filterField => $filterValue]);
            } elseif (is_numeric($filterField)) {
                $objects = $objects->filter(['ParentID' => $filterField]);
            }
            if ($where) {
                $objects = $objects->where($where);
            }
            if ($sort) {
                $objects = $objects->sort($sort);
            }

            return $objects;
        }
    }

    protected function getRecordsPaginated(): PaginatedList
    {
        $records = new PaginatedList($this->getRecords(), $this->request);
        $records->setPageLength(Config::inst()->get(static::class, 'page_size'));

        return $records;
    }
}
