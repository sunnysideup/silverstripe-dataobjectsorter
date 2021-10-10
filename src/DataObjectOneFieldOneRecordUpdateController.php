<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use Sunnysideup\DataObjectSorter\Api\DataObjectSorterRequirements;

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one field in one record
 */
class DataObjectOneFieldOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = [
        'onefieldform' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'save' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectonefieldonerecordupdate';

    /**
     * get a link.
     *
     */
    public static function popup_link_only(string $className, string $fieldName, int $recordID): string
    {
        return self::link_only_maker(
            DataObjectOneFieldOneRecordUpdateController::class,
            'show/' . self::classNameToString($className) . '/' . $fieldName,
            ['id' => $recordID]
        );
    }

    /**
     * get a link.
     *
     */
    public static function popup_link(
        string $className,
        string $fieldName,
        int $recordID,
        ?string $linkText = 'click here to edit'
    ): string {
        $link = self::popup_link_only($className, $fieldName, $recordID);

        return self::link_html_maker(
            $link,
            'modalPopUp modal-popup',
            'oneFieldOneRecord' . self::classNameToString($className) . $fieldName . $recordID,
            $linkText
        );
    }

    /**
     * create a nice button.
     *
     */
    public static function button_link(
        string $className,
        string $fieldName,
        int $recordID,
        ?string $linkText = 'click here to edit'
    ): string {
        $link = self::popup_link_only($className, $fieldName, $recordID);

        return self::button_maker(
            $link,
            'modalPopUp modal-popup',
            'oneFieldOneRecord' . self::classNameToString($className) . $fieldName . $recordID,
            $linkText
        );
    }

    public function onefieldform()
    {
        $obj = $this->getRecordAndCheckPermissions();
        if ($obj instanceof HTTPResponse) {
            return $obj;
        }
        $field = $this->SecureFieldToBeUpdated();
        $FormField = $this->getFormField($obj, $field);
        $FormField->setValue($obj->{$field});

        return new Form(
            $controller = $this,
            $name = 'OneFieldForm',
            $fields = new FieldList(
                $FormField,
                new HiddenField('Table', 'Table', self::classNameToString($this->SecureClassNameToBeUpdatedAsString())),
                new HiddenField('Field', 'Field', $field),
                new HiddenField('Record', 'Record', ($this->SecureRecordIdToBeUpdated()))
            ),
            $actions = new FieldList(new FormAction('save', 'save and close'))
        );
    }

    public function save($data, $form)
    {
        $obj = $this->getRecordAndCheckPermissions();
        if ($obj instanceof HTTPResponse) {
            return $obj;
        }
        $field = $this->SecureFieldToBeUpdated();
        $obj->{$field} = $data[$field];
        $obj->write();

        return '
            <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
            <script type="text/javascript">self.close();</script>';
    }

    protected function init()
    {
        //must set this first ...
        DataObjectSorterRequirements::theme_fix();
        // Only administrators can run this method
        parent::init();
        DataObjectSorterRequirements::popup_requirements('onefieldonerecord');
        DataObjectSorterRequirements::url_variable(
            DataObjectOneFieldOneRecordUpdateController::class,
            'DataObjectOneFieldOneRecordUpdateURL'
        );
    }
}
