<?php
/**
 * @description: allows you to sort dataobjects
 * @author: Nicolaas [at] sunnysideup.co.nz
 * @package: dataobjectsorter
 */
class DataObjectSorterField extends LiteralField{

	/**
	 * @var string $content
	 */
	protected $content;

	function __construct($name, $ClassName) {
		$objects = DataObject::get($ClassName);
		foreach($ClassName as $Obj) {
			$Obj->initDataObjectSorter();
			break;
		}
		$dos = new DataObjectSet();
		$dos->Children = $objects;
		$content = $this->customise($dos)->renderWith("DataObjectSorterField");
		parent::__construct($name, $content);
	}

}

?>