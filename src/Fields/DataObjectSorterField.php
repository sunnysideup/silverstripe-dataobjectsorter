<?php

namespace Sunnysideup\DataobjectSorter\Fields;

use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\ArrayList;
use Sunnysideup\DataobjectSorter\Api\DataObjectSorterRequirements;

/**
 * @description: allows you to sort dataobjects
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @package: dataobjectsorter
 */
class DataObjectSorterField extends LiteralField
{
    /**
     * @var string
     */
    protected $content;

    public function __construct($name, $className)
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $objects = $className::get();
        $arrayList = new ArrayList();
        $dos->Children = $objects;
        $content = $this->customise($arrayList)->renderWith(DataObjectSorterField::class);
        parent::__construct($name, $content);
    }
}
