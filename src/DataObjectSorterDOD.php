<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;

/**
 * Class \Sunnysideup\DataObjectSorter\DataObjectSorterDOD
 *
 * @property \Sunnysideup\DataObjectSorter\DataObjectSorterDOD $owner
 * @property int $Sort
 */
class DataObjectSorterDOD extends DataExtension
{
    /**
     * @var string
     */
    private static $sort_field = 'Sort';

    /**
     * standard SS variable.
     */
    private static $db = [
        'Sort' => 'Int',
    ];

    /**
     * standard SS variable.
     */
    private static $casting = [
        'SortTitle' => 'Varchar',
    ];

    /**
     * action sort.
     *
     * @param array $data
     *
     * @return string
     */
    public function dodataobjectsort($data)
    {
        $sortField = $this->SortFieldForDataObjectSorter();
        $baseDataClass = ClassInfo::baseDataClass($this->getOwner()->ClassName);
        if ('' !== $baseDataClass) {
            if (is_array($data) && count($data)) {
                foreach ($data as $position => $id) {
                    $id = (int) $id;
                    $object = $baseDataClass::get_by_id($id);
                    //we add one because position 0 is not good.
                    $position = (int) $position + 1;
                    if ($object && $object->canEdit()) {
                        if ($position !== $object->{$sortField}) {
                            $object->{$sortField} = $position;
                            //hack for site tree
                            if ($object instanceof SiteTree) {
                                $object->writeToStage(Versioned::DRAFT);
                                $object->publishRecursive();
                            } else {
                                $object->write();
                            }
                        }

                        //do nothing
                    } else {
                        return _t('DataObjectSorter.NOACCESS', 'You do not have access rights to make these changes.');
                    }
                }
            } else {
                return _t('DataObjectSorter.ERROR2', 'Error 2');
            }
        } else {
            return _t('DataObjectSorter.ERROR1', 'Error 1');
        }

        return _t('DataObjectSorter.UPDATEDRECORDS', 'Updated record(s)');
    }

    /**
     * standard SS method.
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeFieldFromTab('Root.Main', $this->SortFieldForDataObjectSorter());
        if (! $this->owner instanceof SiteTree) {
            $link = $this->dataObjectSorterPopupLink();
            $fields->addFieldToTab('Root.Sort', new LiteralField('DataObjectSorterPopupLink', "<h2 class='dataObjectSorterDODLink'>" . $link . '</h2>'));
        }
    }

    /**
     * simplified method.
     *
     * @param string $filterField
     * @param string $filterValue
     * @param string $alternativeTitle
     *
     * @return string HTML
     */
    public function dataObjectSorterPopupLink($filterField = '', $filterValue = '', $alternativeTitle = '')
    {
        $linkText = $alternativeTitle ?: 'Sort ' . $this->getOwner()->plural_name();

        return DataObjectSorterController::popup_link($this->getOwner()->ClassName, $filterField, $filterValue, $linkText);
    }

    /**
     * returns field name for sorting.
     */
    public function SortFieldForDataObjectSorter(): string
    {
        $sortField = Config::inst()->get(DataObjectSorterDOD::class, 'sort_field');
        $field = 'Sort';
        if ($sortField && $this->getOwner()->hasDatabaseField($sortField)) {
            $field = $sortField;
        } elseif ($this->getOwner()->hasDatabaseField('AlternativeSortNumber')) {
            $field = 'AlternativeSortNumber';
        } elseif ($this->getOwner()->hasDatabaseField('Sort')) {
            $field = 'Sort';
        } elseif ($this->getOwner()->hasDatabaseField('SortNumber')) {
            $field = 'SortNumber';
        } else {
            user_error("No field Sort or AlternativeSortNumber (or {$sortField}) was found on data object: " . $this->getOwner()->ClassName, E_USER_WARNING);
        }

        return $field;
    }
}
