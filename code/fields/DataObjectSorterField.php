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
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.7.2.custom.min.js");
		$objects = $ClassName::get();
		$arrayList = new ArrayList();
		$dos->Children = $objects;
		$content = $this->customise($arrayList)->renderWith("DataObjectSorterField");
		parent::__construct($name, $content);
	}

}

