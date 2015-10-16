Data Object Sorter
================================================================================

This module basically helps you to create quick links
to fast edit modes for records.  For example, in the CMS
you can add a link: click here to edit the prices for all products
in one go.  This link then creates a pop-up where you can edit all the
prices without ever having to press save or reload a record.

Similar, you can sort records and so on.

Allows for sorting of records and
editing of one field for all records
and one record and similar functions.
to create links.

Developer
-----------------------------------------------
Nicolaas [at] sunnysideup.co.nz


Requirements
-----------------------------------------------
see composer.json


Documentation
-----------------------------------------------
Please contact author for more details.

Any bug reports and/or feature requests will be
looked at in detail

We are also very happy to provide personalised support
for this module in exchange for a small donation.


*** EXAMPLE ***


```php
if(class_exists("DataObjectSorterController")) {
	$fields->addFieldToTab(
		"Root.Position",
		new LiteralField(
			"AdvertisementsSorter",
			DataObjectSorterController::popup_link(
				"Advertisement",
				$filterField = "",
				$filterValue = "",
				$linkText = "sort ".MyObject::$plural_name,
				$titleField = "FullTitle"
			)
		)
	);
else {
	$fields->addFieldToTab(
		"Root.Position",
		new NumericField(
			$name = "Sort",
			"Sort index number (the lower the number, the earlier it shows up)"
		)
	);
}

Edit one field for all records


```php

$link = DataObjectOneFieldUpdateController::popup_link(
	"SiteTree",
	"URLSegment",
	$where = "MetaTitle IS NULL OR MetaTitle = ''",
	$sort = ''
);
$fields->AddFieldToTab(
	"Root.Content.Check",
	new LiteralField("MyFixes", "Check Page Titles...".$link)
);

```




Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.

2. Review configs and add entries to mysite/_config/config.yml
(or similar) as necessary.
In the _config/ folder of this module
you can usually find some examples of config options (if any).

ADDING SORTER TO FRONT END:

3. add $this->initDataObjectSorter() to your code - e.g. to your
Page_Controller::init function.

4. review css and templates and see if you need to theme it
(rather than using the "unthemed" default provided.

5. add <% include DataObjectSorter %> to your template

There are a ton of other options for adding this module that still need
to be listed here. Also see documentation above.


Credits
-----------------------------------------------
Thanks a million to:
http://www.wil-linssen.com/extending-the-jquery-sortable-with-ajax-mysql/
for the inspiration

