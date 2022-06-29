<?php

namespace Sunnysideup\DataObjectSorter;

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use Sunnysideup\DataObjectSorter\Api\DataObjectSorterRequirements;

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@package: dataobjectsorter
 *@description: allows you to edit one record
 */
class DataObjectOneRecordUpdateController extends DataObjectSortBaseClass
{
    private static $allowed_actions = [
        'onerecordform' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'show' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
        'save' => 'DATA_OBJECT_SORT_AND_EDIT_PERMISSION',
    ];

    private static $fields_method = 'getFrontEndFields';

    /**
     * make sure to also change in routes if you change this link.
     *
     * @var string
     */
    private static $url_segment = 'dataobjectonerecordupdate';

    public static function popup_link_only(string $className, int $recordID): string
    {
        return self::link_only_maker(
            DataObjectOneRecordUpdateController::class,
            'show/' . self::classNameToString($className),
            ['id' => $recordID]
        );
    }

    public static function popup_link(
        string $className,
        int $recordID,
        ?string $linkText = 'click here to edit'
    ): string {
        $link = DataObjectOneRecordUpdateController::popup_link_only($className, $recordID);

        return self::link_html_maker(
            $link,
            'modalPopUp modal-popup',
            'oneRecord' . self::classNameToString($className) . $recordID,
            $linkText
        );
    }

    public static function button_link(
        string $className,
        int $recordID,
        ?string $linkText = 'click here to edit'
    ): string {
        $link = DataObjectOneRecordUpdateController::popup_link_only($className, $recordID);

        return self::button_maker(
            $link,
            'modalPopUp modal-popup',
            'oneRecord' . self::classNameToString($className) . $recordID,
            $linkText
        );
    }

    public function onerecordform()
    {
        $obj = $this->getRecordAndCheckPermissions();
        if ($obj instanceof HTTPResponse) {
            return $obj;
        }

        $formFields = $this->getFormFields($obj);
        if (! $formFields) {
            user_error('Form Fields could not be Found', E_USER_ERROR);
        }

        $formFields->push(new HiddenField('Table', 'Table', $this->SecureClassNameToBeUpdatedAsString()));
        $formFields->push(new HiddenField('Record', 'Record', $this->SecureRecordIdToBeUpdated()));

        $form = new Form(
            $controller = $this,
            $name = 'OneRecordForm',
            $formFields,
            $actions = new FieldList(new FormAction('save', 'save and close'))
        );
        $form->loadDataFrom($obj);

        return $form;
    }

    public function save($data, $form)
    {
        $obj = $this->getRecordAndCheckPermissions();
        if ($obj instanceof HTTPResponse) {
            return $obj;
        }

        $form->saveInto($obj);
        $obj->write();

        return '<script>window.parent.jQuery.modal.close(true)</script>';
    }

    public function show()
    {
        $obj = $this->getRecordAndCheckPermissions();
        if ($obj instanceof HTTPResponse) {
            return $obj;
        }

        return parent::show();
    }

    protected function init()
    {
        //must set this first.
        DataObjectSorterRequirements::theme_fix(DataObjectSorterRequirements::class);
        parent::init();
        if (! Director::is_ajax()) {
            DataObjectSorterRequirements::popup_requirements('onerecord');
            DataObjectSorterRequirements::url_variable(
                DataObjectOneRecordUpdateController::class,
                'DataObjectOneRecordUpdateURL'
            );
        }
    }
}
