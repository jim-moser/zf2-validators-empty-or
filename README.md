#Installation

##Alternative 1: Installation with Composer

To install into an existing Zend Framework 2 installation that was installed 
using Composer:

1. Locate the `composer.json` file located in the directory containing the 
vendor directory and move into that directory.

	cd parent_path_of_vendor
	
2. Use composer to update the composer.json file and install the 
jim-moser/zf2-validators-empty-or package.

	composer require jim-moser/zf2-validators-empty-or
	
This should first update the composer.json file and then install the package 
into the vendor/jim-moser/zf2-validators-empty-or directory and update the 
composer autoloading files (vendor/composer/autoload_classmap.php and/or 
autoload_psr4.php) such that the added validators should now be accessible from 
within your Zend Framework application.

###Configuring Zend\Validator\ValidatorPluginManager

If you would like to use the Zend\Validator\ValidatorPluginManager to obtain 
instances of the added validators from their names ("EmptyValidator", "OrChain", 
or "VerboseOrChain") within a Zend Framework application then the application 
will need configuration to inform the ValidatorPluginManager of the new 
validators. The packages's Module.php file can do this for you but the 
application needs to be made aware of the module to do so. This can be 
accomplished by adding the module name to the ['module'] element and the module 
name and path to the ['module_listener_options']['module_paths'] element of the 
array returned by the application's config/application.config.php file.

	<?php
	return array(
		'modules' => array(
			'Application',
			'JimMoser\Validator',	//Add this line.
			...
		),
		'module_listener_options' => array(
			'module_paths' => array(
				'JimMoser\Validator' => './vendor/jim-moser/zf2-validators-empty-or'	//Add this line.
			),
		),
	);
	
See the [validator plugin manager](#plugin_manager_note) section below.  

##Alternative 2: Manual Installation to Vendor Directory

Use git clone or other method to copy files from git repository at 
https://github.com/jim-moser/zf2-validators-empty-or to the 
vendor/jim-moser/zf2-validators-empty-or directory.

###Add Module To Application Configuration To Setup Autoloading

Since Composer is not being used to handle autoloading configuration another 
method of registering the new classes with the autoloader needs to be used. The 
package's Module.php file provides the necessary autoloading information but 
the module manager needs to be made aware of the module's installation in order 
to access this file. This can be accomplished by adding the module name to the 
['module'] element and the module name and path to the 
['module_listener_options']['module_paths'] element of the array returned by the 
application's config/application.config.php file.

	<?php
	return array(
		'modules' => array(
			'Application',
			'JimMoser\Validator',	//Add this line.
			...
		),
		'module_listener_options' => array(
			'module_paths' => array(
				'JimMoser\Validator' => './vendor/jim-moser/zf2-validators-empty-or'	//Add this line.
			),
		),
	);
	
#<a name=plugin_manager_note></a>Using Zend/Validator/ValidatorPluginManager

The Module.php file adds configuration (see config/module.config.php) to allow 
the Zend\Validator\ValidatorPluginManager to obtain instances of the 
EmptyValidator, OrChain, or VerboseOrChain validators from their names. The 
ValidatorPluginManager must be obtained from the service manager for it to 
receive this configuration.

Note that some classes such as Zend/Validator/ValidatorChain by default will 
directly instantiate new Zend/Validator/ValidatorPluginManager instances using 
the keyword "new" instead of obtaining an instance from the service manager. 
ValidatorPluginManager instances created directly with the new keyword will not 
receive the application configuration during construction and thus will not be 
aware of the validators from this package.

#Unit Testing

##Composer Setup

The development dependencies need to be installed and the autoloading of 
development classes (primarily unit testing classes) needs to be setup before 
unit tests can be run. Composer can do both of these tasks using information 
from the "require-dev" and "autoload-dev" keys of the composer.json file in the 
jim-moser/zf2-validators-empty-or package. However, Composer will only handle 
these development tasks for the root composer.json file that it inspects. 
Composer ignores the "require-dev" and "autoload-dev" keys in composer.json 
files that are below the root composer.json file. 

Using the command

	$ composer require jim-moser/zf2-validators-empty-or
	
as specified earlier to use Composer to install the zf2-validators-empty-or 
package creates an application level composer.json file that is used as the root 
composer.json file. This means the zf2-validators-empty-or composer.json file is 
not the root composer.json file when composer is called with this command. In 
order to get Composer to install the development dependencies and setup the 
autoloading of testing classes we need the data from the "require-dev" and 
"autoload-dev" keys of the zf2-validators-empty-or package composer.json file to
appear in the root composer.json file.

I recommend doing this by copying the data from these keys from the package's 
composer.json to the application's composer.json as follows:
  1. Manually copy the package's development dependencies under the 
	"require-dev" key from the package's composer.json file to the 
	application's composer.json file.
  2. Manually copy the "autoload-dev" from the package's composer.json to 
	the application's composer.json. Then change the value from "/test" to 
	"vendor/jim-moser/zf2-validators-empty-or/test".
		
The application's composer.json file should look as follows if the only package 
within the application is the zf2-validators-empty-or package.

	{
	    "require": {
	        "jim-moser/zf2-validators-empty-or":	"1.*",
	    },
	    "repositories": 
	    [
	    	{
	    	    "type": "vcs",
	    	    "url": "https://github.com/jim-moser/zf2-validators-empty-or"
	    	}
	    ],
	    "require-dev": {
	    	"phpunit/phpunit":					"~4",
	    	"zendframework/zendframework": 		"2.*",
	    },
	    "autoload-dev": {
	        "psr-4": {
	            "JimMoser\\ValidatorTest\\": "vendor/jim-moser/zf2-validators-empty-or/test"
	        }
	    }
	}
	
## Phpunit.xml

Before running unit testing the file phpunit.xml.dist should be copied or moved 
to phpunit.xml. Then PHPUnit can be run from the package's directory.

	$ cd <vendor_directory>/jim-moser/zf2-validators-empty-or
	$ cp phpunit.xml.dist phpunit.xml
	$ php ../../phpunit/phpunit/

## Validator Plugin Manager Configuration Testing

The file test/ValidatorPluginManagerConfigTest.php.dist is an optional test to 
verify that the validator plugin manager configuration provided by Module.php 
was properly added to the validator plugin manager. To run this test two things 
need to be done.
  1. test/ValidatorPluginManagerConfigTest.php.dist needs to be copied or moved to 
test/ValidatorPluginManagerConfigTest.php.
  2. phpunit.xml.plugin.dist needs to be copied or moved to phpunit.xml.