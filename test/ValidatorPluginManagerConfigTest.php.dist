<?php
namespace JimMoser\ValidatorTest;

use JimMoser\Validator\EmptyValidator;
use JimMoser\Validator\OrChain;
use JimMoser\Validator\VerboseOrChain;

/**
 * Unit testing to verify validator plugin manager is configured to obtain 
 * instances of added validators.
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser for source repository
 * @copyright Copyright (c) August 8, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2-validators-empty-or  
 *            New BSD License
 */
class ValidatorPluginManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $pluginManager;
    
    public function Setup()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->pluginManager = $serviceManager->get('ValidatorManager');
    }
    
    public function testGetValidatorsByName()
    {
        $emptyValidator = $this->pluginManager->get('EmptyValidator');
        $this->assertTrue($emptyValidator instanceof EmptyValidator);
        
        $orChain = $this->pluginManager->get('OrChain');
        $this->assertTrue($orChain instanceof OrChain);
        
        $verboseOrChain = $this->pluginManager->get('VerboseOrChain');
        $this->assertTrue($verboseOrChain instanceof VerboseOrChain);
    }
}