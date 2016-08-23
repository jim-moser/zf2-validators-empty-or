#Overview

This package contains the JimMoser\EmptyValidator, JimMoser\OrChain, and 
JimMoser\VerboseOrChain validators for Zend Framework 2.

EmptyValidator is a validator that is valid for empty values. It 
can be considered the logical opposite of the Zend\Validator\NotEmpty validator.

The OrChain and VerboseOrChain validators are validator chains similar to 
Zend\Validator\ValidatorChain except that they join chained validators using a 
logical OR instead of a logical AND.

VerboseOrChain serves the same purpose as OrChain but provides added validation 
failure messages.

See the comments within the source code of the validators for more detailed 
information on each of them.

This is a base package with the minimal dependencies needed to provide the 
validators. Unit tests and a Module.php file for providing configuration to the 
Zend Framework validator plugin manager are provided in separate packages.

#Dependencies

This package depends directly only on zendframework/zend-validator and 
zendframework/zend-stdlib.

The zendframework/zend-validator package contains code with dependencies on code 
within the zendframework/zend-servicemanager and zendframework/zend-i18n 
packages but these dependencies are not listed in its' composer.json file. These 
dependencies need to be installed only if using the validator plugin manager 
(Zend/Validator/ValidatorPluginManager). If your application uses the validator 
plugin manager then you should either add these dependencies to your 
application's composer.json file or use the 
jim-moser/zf2-validators-empty-or-plugin package.

Beware that your application may use the validator plugin manager even if your 
code never calls it directly. For example, the JimMoser\OrChain and 
JimMoser\VerboseOrChain classes use the validator plugin manager to add 
validators by name. In the code below, the JimMoser\OrChain object uses a 
Zend\Validator\ValidatorPluginManager instance to create a 
Zend\Validator\NotEmpty validator instance from the string "NotEmpty" passed to 
the OrChain::attachByName method.

	$orChain = new \JimMoser\OrChain();
	$orChain->attachByName('NotEmpty');
		
#Related Packages

* jim-moser/zf2-validators-empty-or
* jim-moser/zf2-validators-empty-or-test
* jim-moser/zf2-validators-empty-or-plugin
* jim-moser/zf2-validators-empty-or-plugin-test
	
<dl>
	<dt>jim-moser/zf2-validators-empty-or</dt>
	<dd><p>Base package containing EmptyValidator, OrChain, and VerboseOrChain
		validators for Zendframework 2.</p>

		<p>This package has the fewest dependencies. Depends directly on 
		zendframework/zend-validator and zendframework/zend-stdlib.</p>
				
		<p>Does not include unit testing. The unit testing is available in the
		jim-moser/zf2-validators-empty-or-test package.</p>
		
		<p>Does not include the Module.php and configuration file used to 
		inform the validator plugin manager of the validators added by this 
		package. These files are provided by the 
		jim-moser/zf2-validators-empty-or-plugin package.</p>
	</dd>
	<dt>jim-moser/zf2-validators-empty-or-test</dt>
	<dd><p>Package containing unit tests for 
		jim-moser/zf2-validators-empty-or package.</p>
		
		<p>Depends directly on jim-moser/zf2-validators-empty-or, 
		zendframework/zend-servicemanager, and phpunit/phpunit.</p>
	</dd>
	<dt>jim-moser/zf2-validators-empty-or-plugin</dt>
	<dd>
		<p>This package adds a Module.php file and configuration file which are 
		used to add configuration for the Zend Framework 2 validator plugin 
		manager. This configuration allows the plugin manager to return 
		instances of the EmptyValidator, OrChain, and VerboseOrChain validators 
		given strings containing their names.</p>
		
		<p>Depends directly on jim-moser/zf2-validators-empty-or and 
		zendframework/zendframework (all of Zend Framework 2).</p>
	</dd>
	<dt>jim-moser/zf2-validators-empty-or-plugin-test</dt>
	<dd><p>Package containing framework integration tests for
		jim-moser/zf2-validators-empty-or-plugin package. The tests verify that 
		the added validators are available from the validator plugin manager.</p>
		
		<p>Depends directly on jim-moser/zf2-validators-empty-or-test and 
		jim-moser/zf2-validators-empty-or-plugin.</p>
	</dd>
</dl>

#Installation

##Alternative 1: Installation with Composer

1. For an existing Zend Framework installation, move into the parent of the 
	vendor directory. This directory should contain an existing composer.json 
	file. For a new installation, move into the directory you would like to 
	contain the vendor directory.
	
		$ cd <parent_path_of_vendor>	
	
2. Run the following command which will update the composer.json file, install 
	the zf2-validators-empty-or-plugin package and its dependencies into their 
	respective directories under the vendor directory, and update the 
	composer autoloading files.

		$ composer require jim-moser/zf2-validators-empty-or
	
##Alternative 2: Manual Installation to Vendor Directory

If you would like to install the packages manually and use a Module.php file to 
configure autoloading instead of using Composer to configure autoloading then 
use the jim-moser/zf2-validators-empty-or-plugin package instead of this 
package. Follow the installation instructions in the README.md file of that 
package.