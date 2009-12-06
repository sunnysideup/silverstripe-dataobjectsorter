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
		Requirements::javascript("dataobjectsorter/javascript/jquery-1.3.2.min.js");
		Requirements::block(THIRDPARTY_DIR."/jquery/jquery.js");
		Requirements::javascript("dataobjectsorter/javascript/jquery-ui-1.7.2.custom.min.js");
		Requirements::themedCSS("dataobjectsorter");
		$objects = DataObject::get($ClassName);
		$dos = new DataObjectSet();
		$dos->Children = $objects;
		$content = $this->customise($dos)->renderWith("DataObjectSorterField");
		parent::__construct($name, $content);
	}

}

