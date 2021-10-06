<?php

namespace Sunnysideup\DataobjectSorter\Api;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBField;
use Sunnysideup\DataobjectSorter\DataObjectOneFieldUpdateController;

class DataObjectOneFieldAddEditAllLink
{
    public static function add_edit_links_to_checkboxes(string $className, FieldList $feldList, $whereArrayExceptions = [], $sortArrayExceptions = [])
    {
        if (! class_exists($className)) {
            user_error('Could not find ' . $className . ' as ClassName.');
        }
        $dataFields = $feldList->dataFields();
        foreach ($dataFields as $formField) {
            if ($formField instanceof CheckboxField) {
                $fieldName = $formField->getName();
                $where = $whereArrayExceptions[$fieldName] ?? '';
                $sort = $sortArrayExceptions[$fieldName] ?? '';
                $linkText = 'Edit All ';
                $titleField = 'Title';
                $link = DataObjectOneFieldUpdateController::popup_link(
                    $className,
                    $fieldName,
                    $where,
                    $sort,
                    $linkText,
                    $titleField,
                );
                $oldDescription = $formField->getDescription();
                $newDescriptionArray = array_filter(
                    [
                        $oldDescription,
                        $link,
                    ]
                );
                $newDescription = implode('<br />', $newDescriptionArray);
                $formField->setDescription(DBField::create_field('HTMLText', $newDescription));
            }
        }
    }
}
