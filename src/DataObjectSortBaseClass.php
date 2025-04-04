<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use Sunnysideup\DataObjectSorter\Api\DataObjectSorterRequirements;

/**
 * Class \Sunnysideup\DataObjectSorter\DataObjectSortBaseClass
 *
 */
class DataObjectSortBaseClass extends Controller implements PermissionProvider
{
    /**
     * Permission for user management.
     *
     * @var string
     */
    public const CAN_DO_STUFF = 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION';

    protected $objectCache = [];

    protected $fieldToBeUpdated = '';

    protected $classNameToBeUpdated = '';

    protected $singletonToBeUpdated;

    protected $record;

    protected $records;

    protected $recordID = 0;

    private static $page_size = 1000;

    private static $scaffold_form_method = 'getFrontEndFields';

    private static $url_handlers = [
        '$Action//$ID/$OtherID/$ThirdID/$FourthID/$FifthID' => 'handleAction',
    ];

    private static $allowed_actions = [
        'show' => DataObjectSortBaseClass::CAN_DO_STUFF,
    ];

    private static $field;

    private static $fields;

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
        if (! Permission::check(DataObjectSortBaseClass::CAN_DO_STUFF)) {
            return $this->permissionFailureStandard();
        }
    }

    protected function SecureFieldToBeUpdated(): string
    {
        if (! $this->fieldToBeUpdated) {
            $obj = $this->SecureSingletonToBeUpdated();
            if ($obj) {
                if (isset($_POST['Field'])) {
                    return addslashes($_POST['Field']);
                }

                $field = $this->getRequest()->param('OtherID');

                if ($obj->hasDatabaseField($field)) {
                    $this->fieldToBeUpdated = $field;

                    return $field;
                }

                $className = $this->SecureClassNameToBeUpdated();
                user_error($field . ' does not exist on ' . $className, E_USER_ERROR);
            } else {
                user_error('there is no class name specified', E_USER_ERROR);
            }
        }

        return $this->fieldToBeUpdated;
    }

    /**
     * @return null|DataObject
     */
    protected function SecureSingletonToBeUpdated()
    {
        if (! $this->singletonToBeUpdated) {
            $className = $this->SecureClassNameToBeUpdated();
            if (class_exists($className)) {
                if (! isset($this->objectCache[$className])) {
                    $this->objectCache[$className] = DataObject::get_one($className);
                }

                $this->singletonToBeUpdated = $this->objectCache[$className];
            } else {
                user_error('there is no table / classname specified', E_USER_ERROR);
            }
        }

        return $this->singletonToBeUpdated;
    }

    /**
     * returns a ClassName that is a real classname.
     * it may also return a table.
     */
    protected function SecureClassNameToBeUpdated(): string
    {
        if (! $this->classNameToBeUpdated) {
            $classNameString = $this->request->param('ID');
            if (! $classNameString) {
                $classNameString = $this->request->requestVar('Table');
            }

            if (! $classNameString) {
                $classNameString = $this->request->requestVar('ClassName');
            }

            if (! class_exists($classNameString)) {
                $classNameString = self::stringToClassName($classNameString);
            }

            if (class_exists($classNameString)) {
                $this->classNameToBeUpdated = $classNameString;
            } else {
                user_error('Could not find className: ' . $classNameString, E_USER_ERROR);
            }
        }

        return $this->classNameToBeUpdated;
    }

    protected function SecureClassNameToBeUpdatedAsString(): string
    {
        return self::classNameToString($this->SecureClassNameToBeUpdated());
    }

    protected function SecureRecordIdToBeUpdated(): int
    {
        if (! $this->recordID) {
            $this->recordID = (int) $this->request->requestVar('Record');
            if (! $this->recordID) {
                $this->recordID = (int) $this->request->requestVar('id');
            }

            if (! $this->recordID) {
                $this->recordID = (int) $this->getRequest()->param('OtherID');
            }
        }

        return $this->recordID;
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
            if ($obj->hasMethod('getFrontEndField')) {
                self::$field = $obj->getFrontEndField($fieldName);
            }
            if (! self::$field) {
                self::$field = $obj->dbObject($fieldName)->scaffoldFormField($obj->Title);
            }
        }

        // clone is crucial!
        return clone self::$field;
    }

    /**
     * @param DataObject $obj
     *
     * @return FieldList
     */
    protected function getFormFields($obj)
    {
        if (! self::$fields) {
            $method = $this->Config()->get('scaffold_form_method');
            if ($obj->hasMethod($method)) {
                //legacy!!!
                self::$fields = $obj->$method();
            }
            if (! self::$fields) {
                self::$fields = $obj->scaffoldFormFields();
            }
        }

        return self::$fields;
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
        $var = '';
        if ($link) {
            DataObjectSorterRequirements::popup_link_requirements();
            $linkClean = Convert::raw2att($link);
            $var = '
                <a href="' . $linkClean . '"
                    class="' . $cssClasses . '"
                    data-width="800"
                    data-height="600"
                    data-rel="window.open(\'' . $linkClean . "', 'update" . Convert::raw2att($code) . '\',\'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=0,resizable=1,width=600,height=600,left=20,top=20\'); return false;"
                ><span class="ui-button-text">' . $linkText . '</span></a>';
        }

        return $var;
    }

    protected static function button_maker(string $link, string $cssClasses, string $code, string $linkText): string
    {
        return '
        <div class="form-group field readonly">
            <label class="form__field-label"></label>
            <div class="form__field-holder">
                <p class="form-control-static readonly">
                    ' . self::link_html_maker($link, 'btn action btn-outline-primary ' . $cssClasses, $code, $linkText) . '
                </p>
            </div>
        </div>';
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
        $obj = $className::get_by_id($recordId);
        if (! $obj) {
            user_error('record could not be found!', E_USER_ERROR);

            return $this->permissionFailureStandard('Could not find record.');
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
            $filterField = (string) Convert::raw2sql(urldecode((string) $this->request->requestVar('filterField')));
            $filterValue = (string) Convert::raw2sql(urldecode((string) $this->request->requestVar('filterValue')));
            $where = $this->request->requestVar('where');
            $sort = $this->request->requestVar('sort');
            $where = Convert::raw2sql($where);
            $sort = Convert::raw2sql($sort);
            $objects = $className::get();
            if ($filterField && $filterValue) {
                $filterValue = explode(',', (string) $filterValue);
                $objects = $objects->filter([$filterField => $filterValue]);
            } elseif (is_numeric($filterField)) {
                $objects = $objects->filter(['ParentID' => $filterField]);
            }

            if ($where) {
                if (is_array($where)) {
                    $objects = $objects->filter($where);
                } else {
                    $objects = $objects->where($where);
                }
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


    protected function getTitleForObject($obj, ?string $titleField)
    {
        $titleField = $titleField ?: 'Title';
        $titleFieldWithoutGet = $this->removePrefix('get', $titleField);
        $titleFieldWithGet = 'get' . $titleFieldWithoutGet;
        $castedVariables = Config::inst()->get($obj->ClassName, 'casting');
        if ($titleField && $obj->hasDatabaseField($titleField)) {
            $title = $obj->{$titleField};
        } elseif (! empty($castedVariables[$titleFieldWithoutGet])) {
            if ($obj->hasMethod($titleFieldWithoutGet)) {
                $title = $obj->$titleFieldWithoutGet();
            } else {
                $title = $obj->$titleFieldWithGet();
            }
        } elseif ($obj->hasMethod('getTitle')) {
            $title = $obj->getTitle();
        } elseif ($obj->hasMethod('Title')) {
            $title = $obj->Title();
        }
        return $title;
    }

    protected function removePrefix(string $prefix, string $string): string
    {
        // Check if the string starts with the prefix
        if (strpos($string, $prefix) === 0) {
            // Remove the prefix by slicing the string
            return substr($string, strlen($prefix));
        }
        return $string; // Return the original string if prefix not at start
    }

    protected static function turnStateIntoFilterAndSort($className, mixed $filterOriginal, mixed $sortOriginal): array
    {
        $filterSortCache = self::getDecodedGridState($className);

        $filter = is_array($filterOriginal) ?
            array_merge($filterSortCache['filter'], $filterOriginal) : ($filterOriginal ?: $filterSortCache['filter']);
        $sort = is_array($sortOriginal) ?
            array_merge($filterSortCache['sort'], $sortOriginal) : ($sortOriginal ?: $filterSortCache['sort']);

        return compact('filter', 'sort');
    }

    protected static $filterSortCachePerClassName = [];


    protected static function getDecodedGridState(string $className): array
    {
        if (!isset($filterSortCachePerClassName[$className])) {
            $state = [];
            $filter = [];
            $sort = [];
            $getVars = Controller::curr()?->getRequest()?->getVars();
            if (!is_array($getVars)) {
                $getVars = [];
            }
            foreach ($getVars as $key => $value) {
                // Match keys that start with "gridState-" and end with "-0"
                if (preg_match('/^gridState-(.*)-0$/', $key, $matches)) {
                    $className = str_replace('-', '\\', $matches[1]); // Convert to namespace format

                    if (class_exists($className)) {
                        $jsonString = urldecode($value);
                        $decodedData = json_decode($jsonString, true);

                        if (is_array($decodedData)) {
                            $state[$className] = $decodedData;
                        }
                    }
                }
            }
            if (isset($state[$className])) {
                $state = $state[$className];
                if (isset($state['GridFieldFilterHeader']['Columns'])) {
                    $filter = $state['GridFieldFilterHeader']['Columns'];
                }
                if (isset($state['GridFieldSortableHeader']['SortColumn'])) {
                    $sort = $state['GridFieldSortableHeader']['SortColumn'];
                }
                $fields = DataObject::getSchema()->databaseFields($className);
                $fields['q'] = '';

                //todo - deal with q!
                foreach ($filter as $key => $value) {
                    if (!isset($fields[$key])) {
                        unset($filter[$key]);
                    } else {
                        $filter[$key . ':PartialMatch'] = Convert::raw2sql($value);
                        unset($filter[$key]);
                    }
                }
                if (!is_array($sort)) {
                    $sort = [$sort];
                }
                foreach ($sort as $key => $value) {
                    if (!isset($fields[$value])) {
                        unset($sort[$key]);
                    }
                    $sort[$value] = 'ASC';
                    unset($sort[$key]);
                }
            }
            $filterSortCachePerClassName[$className] = ['filter' => $filter, 'sort' => $sort];
        }
        return $filterSortCachePerClassName[$className];
    }

    protected function writeAndPublish($obj)
    {
        $isPublished = false;
        if ($obj->hasMethod('isPublished')) {
            $isPublished = $obj->isPublished();
            if ($obj->hasMethod('isModifiedOnDraft')) {
                if ($obj->isModifiedOnDraft()) {
                    $isPublished = false;
                }
            }
        }
        $obj->write();
        if ($isPublished) {
            $obj->publishRecursive();
        }
    }
}
