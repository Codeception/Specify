# Changelog

## 1.0

* BREAKING: PHPUnit 6 support
* BREAKING: **Removed configuration** section
* BREAKING: **Only properties marked with `@specify` annotation are cloned** in specify blocks.
* BREAKING: **Removed throws** parameter in specify blocks 
* Added `Codeception\Specify\ResultPrinter` to fix printing progress of specify blocks.

### Upgrade Plan

1. Update to PHP7+ PHPUnit 6+
2. Add to `phpunit.xml`: `printerClass="Codeception\Specify\ResultPrinter"`
3. If relied on property cloning, add `@specify` annotation for all properties which needs to be cloned for specify blocks
4. If you used `throws` parameter, consider using [AssertThrows](https://github.com/Codeception/AssertThrows) package.

#### 0.4.3

* Show example index on failure by @zszucs *2015-11-27*


#### 0.4.2

* Testing exception messages by @chrismichaels84 https://github.com/Codeception/Specify#exceptions

#### 0.4.0

* Fixes cloning properties in examples. Issue #6 *2014-10-15*
* Added global and local specify configs, for disabling cloning properties and changing cloning methods *2014-10-15*


#### 0.3.6 03/22/2014

* Cloning unclonnable items


#### 0.3.5 03/22/2014

* Updated to DeepCopy 1.1.0


#### 0.3.4 02/23/2014

* Added DeepCopy library to save/restore objects between specs
* Robo file for releases
