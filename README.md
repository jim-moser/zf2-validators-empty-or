#Installation

##Alternative 1: Installation with Composer

To install into an existing Zend Framework 2 installation that was installed 
using Composer:
1. Locate the `composer.json` file located in the directory containing the 
vendor directory and move into that directory.
	cd parent_path_of_vendor
2. Use composer to update the composer.json file and install the 
jim-moser/zf2-validators-empty-or package.
	composer require --dev jim-moser/zf2-validators-empty-or
This should first update the composer.json file and then install the package 
into the vendor/jim-moser/zf2-validators-empty-or directory and update the 
composer autoloading files (vendor/composer/autoload_classmap.php and/or 
autoload_psr4.php) such that the added validators should now be accessible from 
within your Zend Framework application.

###Configuring Zend\Validator\ValidatorPluginManager

If you would like to use the Zend\Validator\ValidatorPluginManager to obtain 
instances of the added validators from their names ("EmptyValidator", "OrChain", 
or "VerboseOrChain") then the application will need configuration to inform the 
ValidatorPluginManager of the new validators. The module's Module.php file will 
do this for you but the application needs to be made aware of the module to do 
so. This can be accomplished by adding the module name to the ['module'] element 
and the module name and path to the ['module_listener_options']['module_paths'] 
element of the array returned by the application's config/application.config.php 
file.
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

