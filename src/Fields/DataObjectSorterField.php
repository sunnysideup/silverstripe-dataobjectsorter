<?php
/**
 * @description: allows you to sort dataobjects
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @package: dataobjectsorter
 */
class DataObjectSorterField extends LiteralField
{

    /**
     * @var string $content
     */
    protected $content;

    public function __construct($name, $ClassName)
    {
        DataObjectSorterRequirements::popup_link_requirements();
        $objects = $ClassName::get();
        $arrayList = new ArrayList();
        $dos->Children = $objects;
        $content = $this->customise($arrayList)->renderWith("DataObjectSorterField");
        parent::__construct($name, $content);
    }
}
