# Silverstripe 6 Upgrade Guide

## Requirements

### ⚠️ BREAKING CHANGE: Composer Dependencies

Update your `composer.json`:

```diff
- "silverstripe/recipe-cms": "^4 || ^5"
+ "silverstripe/recipe-cms": "^6.0"
```

Run `composer update` after making this change.

---

## Namespace Changes

### ⚠️ BREAKING CHANGE: ArrayList and ArrayData Relocated

These classes have moved from ORM/View namespaces to a unified Model namespace:

```php
// Old (SS4/5)
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;

// New (SS6)
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\ArrayData;
```

**Files affected:**
- `src/DataObjectOneFieldUpdateController.php`
- `src/Fields/DataObjectSorterField.php`

### ⚠️ BREAKING CHANGE: PaginatedList Relocated

```php
// Old
use SilverStripe\ORM\PaginatedList;

// New
use SilverStripe\Model\List\PaginatedList;
```

**Files affected:**
- `src/DataObjectSortBaseClass.php`

---

## API Method Changes

### ⚠️ BREAKING CHANGE: FieldList::dataFields() Removed

Replace `dataFields()` with `getDataFields()`:

```php
// Old
$dataFields = $fieldList->dataFields();

// New
$dataFields = $fieldList->getDataFields();
```

**Files affected:**
- `src/Api/DataObjectOneFieldAddEditAllLink.php:23`

### ⚠️ BREAKING CHANGE: DataObject::get_one() Removed

Replace static `get_one()` calls with the modern DataList API:

```php
// Old
$object = DataObject::get_one($className);

// New
$object = $className::get()->setUseCache(true)->first();
```

**Files affected:**
- `src/DataObjectSortBaseClass.php:145`

**🚨 CRITICAL REVIEW REQUIRED:**

**The `setUseCache(true)` call may have different caching behaviour than the old `get_one()` method. Review whether caching is appropriate for your use case, especially if you expect data to change within the same request.**

---

## Type Safety Improvements

### FormField Type Checking

Strengthen type checking when working with form fields:

```php
// Old
if ($myFormField) {
    if ($myFormField->isReadonly()) {
        // ...
    }
}

// New
use SilverStripe\Forms\FormField;

if ($myFormField instanceof FormField) {
    if ($myFormField->isReadonly()) {
        // ...
    }
}
```

This prevents type errors when `dataFieldByName()` returns `null` or non-FormField values.

**Files affected:**
- `src/Extensions/DataObjectEditAnythingExtension.php:186`

---

## PHP 8 Attribute Usage

### Override Attribute Added

The `#[Override]` attribute has been added to methods that override parent/interface methods. This is a PHP 8.3+ feature that provides compile-time verification.

**No action required** unless you're extending these classes and need to maintain override compatibility.

**Files affected:**
- `src/DataObjectOneFieldOneRecordUpdateController.php`
- `src/DataObjectOneFieldUpdateController.php`
- `src/DataObjectOneRecordUpdateController.php`
- `src/DataObjectSortBaseClass.php`
- `src/DataObjectSorterController.php`

---

## Summary

Most changes are namespace updates for relocated framework classes. The two most significant breaking changes requiring code changes are:

1. Replace `DataObject::get_one()` → `ClassName::get()->first()`
2. Replace `dataFields()` → `getDataFields()`

Review cached query behaviour where `get_one()` has been replaced.