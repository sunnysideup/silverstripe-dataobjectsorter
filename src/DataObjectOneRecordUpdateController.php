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

    private static $fields_method = 'DosFields';

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
    ): string
    {
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
    ): string
    {
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
        $fields = new FieldList(
            new HiddenField('Table', 'Table', $this->SecureClassNameToBeUpdatedAsString()),
            new HiddenField('Record', 'Record', $this->SecureRecordIdToBeUpdated())
        );
        foreach ($formFields as $f) {
            $fields->push($f);
        }

        $form = new Form(
            $controller = $this,
            $name = 'OneRecordForm',
            $fields,
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

        return '
            <p>Your changes have been saved, please <a href="#" onclick="self.close(); return false;">close window</a>.</p>
            <script type="text/javascript">self.close();</script>';
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
