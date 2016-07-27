<?php
namespace JimMoser\ValidatorTest;

use JimMoser\Validator\OrChain;
use JimMoser\Validator\EmptyValidator;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Between;
use Zend\Validator\LessThan;
use Zend\Validator\GreaterThan;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorPluginManager;

/**
 * Unit test class for OrChain.
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) June 21, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator  
 *            New BSD License
 */
class OrChainTest extends \PHPUnit_Framework_TestCase
{
    protected $lt5_Or_Gt10_OrValidator;
    protected $referenceDefaultUnionMessage;
    protected $lt5_Or_Gt10_ReferenceMessages;
    
    public function Setup()
    {
        $lessThanValidator = new LessThan(array('max' => 5,
                                                'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                                                'inclusive' => false));
        $this->lt5_Or_Gt10_OrValidator = new OrChain();
        $this->lt5_Or_Gt10_OrValidator->attach($lessThanValidator)
                                      ->attach($moreThanValidator);

        // Need to reset static Zend\Validator\AbstractValidator::messageLength
        // property to its original value in case it is changed in any tests.
        $this->lt5_Or_Gt10_OrValidator->setMessageLength(-1);
        
        $this->lt5_Or_Gt10_ReferenceMessages = array(
            "The input is not less than '5'",
            "The input is not greater than '10'"
        );
    }
    
    /*
    public function testZendInputFilter()
    {
        $validatorChain = new ValidatorChain();
        $validatorChain->attach($this->lt5_Or_Gt10_OrValidator);
        $input = new Input();
        $input->setName('number')
              ->setRequired(true)
              ->setAllowEmpty(true)
              ->setContinueIfEmpty(true)
              ->setValidatorChain($validatorChain);
        $this->lessThan5GreaterThan10 = new InputFilter();
        $this->lessThan5GreaterThan10->add($input);
        
        $this->lessThan5GreaterThan10->setData(array('number' => 2));
        $this->assertTrue($this->lessThan5GreaterThan10->isValid());
        $messages = $this->lessThan5GreaterThan10->getMessages();
        $this->assertEmpty($messages);
         
        $this->lessThan5GreaterThan10->setData(array('number' => 7));
        $this->assertFalse($this->lessThan5GreaterThan10->isValid());
        $allMessages = $this->lessThan5GreaterThan10->getMessages();
        $messages = $allMessages['number'];
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
         
        $this->lessThan5GreaterThan10->setData(array('number' => 12));
        $this->assertTrue($this->lessThan5GreaterThan10->isValid());
        $messages = $this->lessThan5GreaterThan10->getMessages();
        $this->assertEmpty($messages);
    }
    */
    
    public function testIsValid()
    {
         $this->assertTrue($this->lt5_Or_Gt10_OrValidator->isValid(2));
         $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
         $this->assertEmpty($messages);
         
         $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
         $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
         $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                             array_values($messages));
         
         $this->assertTrue($this->lt5_Or_Gt10_OrValidator->isValid(12));
         $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
         $this->assertEmpty($messages);
    }
    
    public function testConstructorOptions()
    {
        // messageLength is not used by OrChain but does set the maximum message 
        // length for all validators inheriting from 
        // Zend\Validator\AbstractValidator.
        $testValidator = new OrChain(
            array(
                'non_existing_option' => 'some value',
                'messageLength' => 8,
            )
        );
        
        $messageLength = $testValidator->getMessageLength();
        $this->assertEquals(8, $messageLength);
        $options = $testValidator->getOptions();
        $referenceOptions = array(
            'messages'             => array(),
            'messageTemplates'     => array(),
            'messageVariables'     => array(),
            'translator'           => null,
            'translatorTextDomain' => null,
            'translatorEnabled'    => true,
            'valueObscured'        => false,
            'non_existing_option' => 'some value',
        );
        $this->assertEquals($referenceOptions, $options);
    }

    /**
     * Test traversable options.
     * 
     * This is a coverage driven test.
     * 
     */
    public function testConstructorIteratorOptions()
    {
        $iterator = new \ArrayObject(array(
            'non_existing_option' => 'some other value',
            'messageLength' => 9,
        ));
        $this->assertTrue($iterator instanceof \Traversable);
        $testValidator= new OrChain($iterator);
        $messageLength = $testValidator->getMessageLength();
        $this->assertEquals(9, $messageLength);
        $options = $testValidator->getOptions();
        $referenceOptions = array(
                        'messages'             => array(),
                        'messageTemplates'     => array(),
                        'messageVariables'     => array(),
                        'translator'           => null,
                        'translatorTextDomain' => null,
                        'translatorEnabled'    => true,
                        'valueObscured'        => false,
                        'non_existing_option' => 'some other value',
        );
        $this->assertEquals($referenceOptions, $options);
    }
    
    public function testChainMethods()
    {
        // Chain methods are attach, prependValidator, attachByName, 
        // prependByName, count, getPluginManager, setPluginManager, plugin,
        // merge, and getValidators.
        //
        // count, getPluginManager, setPluginManager, plugin, and getValidators
        // are tested here.
        $validatorCount = $this->lt5_Or_Gt10_OrValidator->count();
        $this->assertEquals(2, $validatorCount);
        
        $pluginManager = $this->lt5_Or_Gt10_OrValidator->getPluginManager();
        $this->assertEquals('Zend\Validator\ValidatorPluginManager',
                            get_class($pluginManager));
        $pluginManager = new ValidatorPluginManager();
        $returnValue = $this->lt5_Or_Gt10_OrValidator->
                                            setPluginManager($pluginManager);
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        $returnedPluginManager = $this->lt5_Or_Gt10_OrValidator->
                                                            getPluginManager();
        $this->assertEquals($pluginManager, $returnedPluginManager);
        
        $lessThanValidator = $this->lt5_Or_Gt10_OrValidator->plugin(
            'LessThan',
            array('max' => 4)
        );
        $this->assertTrue($lessThanValidator InstanceOf LessThan);
        
        $validators = $this->lt5_Or_Gt10_OrValidator->getValidators();
        $this->assertTrue($validators[0] instanceof LessThan);
    }
    
    public function testAttachWithVariousArguments()
    {
        // $showMessages = false.
        $lessThanValidator = new LessThan(array('max' => 5,
                                                'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                                                   'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new OrChain();
        $lt5_Or_Gt10_OrValidator->attach($lessThanValidator);
        $count = $lt5_Or_Gt10_OrValidator->attach($lessThanValidator, false)
                                         ->count();
        $this->assertEquals(2, $count);
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
        );
        $this->assertFalse($lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Reverse priority.
        $lessThanValidator = new LessThan(array('max' => 5,
                        'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                        'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new OrChain();
        $lt5_Or_Gt10_OrValidator->attach($lessThanValidator);
        // priority = 2.
        $count = $lt5_Or_Gt10_OrValidator->attach($moreThanValidator, true, 2)
                                         ->count();
        $this->assertEquals(2, $count);
        $lt5_Or_Gt10_ReferenceMessages = array(
            "The input is not greater than '10'",
            "The input is not less than '5'",
        );
        $this->assertFalse($lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
        
    public function testPrepend()
    {
        $lessThanValidator = new LessThan(array('max' => 5,
                        'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                        'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new OrChain();
        $count = $lt5_Or_Gt10_OrValidator->attach($lessThanValidator)->
            prependValidator($moreThanValidator)->count();
        $this->assertEquals(2, $count);
        
        $lt5_Or_Gt10_ReferenceMessages = array(
            "The input is not greater than '10'",
            "The input is not less than '5'",
        );
        
        $result = $lt5_Or_Gt10_OrValidator->isValid(7);
        $this->assertFalse($result);
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }

    public function testAttachByName()
    {
        $lt5_Or_Gt10_OrValidator= new OrChain();
        $count = $lt5_Or_Gt10_OrValidator->
                    attachByName('LessThan', array('max' => 5))->
                    attachByName('GreaterThan', array('min' => 10))->
                    count();
        $this->assertEquals(2, $count);
        
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
                        "The input is not greater than '10'",
        );
        
        $result = $lt5_Or_Gt10_OrValidator->isValid(7);
        $this->assertFalse($result);
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Pass arguments through $options parameter.
        $options = array(
            'max' => 6,
            'show_messages' => true,
        );
        $lt5_Or_Gt10_OrValidator->attachByName('LessThan', $options);
        $options = array(
            'min' => 9,
            'show_messages' => true,
            'priority' => 2,
        );
        $lt5_Or_Gt10_OrValidator->attachByName('GreaterThan', $options);
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '9'",
                        "The input is not less than '5'",
                        "The input is not greater than '10'",
                        "The input is not less than '6'",
        );
        
        $result = $lt5_Or_Gt10_OrValidator->isValid(7);
        $this->assertFalse($result);
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
    
    public function testPrependByName()
    {
        $lt5_Or_Gt10_OrValidator= new OrChain();
        $count = $lt5_Or_Gt10_OrValidator->
                    prependByName('LessThan', array('max' => 5))->
                    prependByName('GreaterThan', array('min' => 10))->
                    count();
        $this->assertEquals(2, $count);
    
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '10'",
                        "The input is not less than '5'",
        );
    
        $result = $lt5_Or_Gt10_OrValidator->isValid(7);
        $this->assertFalse($result);
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Pass arguments through $options parameter.
        $options = array(
                        'max' => 6,
                        'show_messages' => true,
        );
        $lt5_Or_Gt10_OrValidator->prependByName('LessThan', $options);
        $options = array(
                        'min' => 9,
                        'show_messages' => true,
                        'non-existing_option' => 'not real',
        );
        $lt5_Or_Gt10_OrValidator->prependByName('GreaterThan', $options);
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '9'",
                        "The input is not less than '6'",
                        "The input is not greater than '10'",
                        "The input is not less than '5'",
        );
        
        $result = $lt5_Or_Gt10_OrValidator->isValid(7);
        $this->assertFalse($result);
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
                        
    }

    public function testMerge()
    {
        $newOrChain = new OrChain();
    
        $between6And8Validator = new Between(array('min' => 6,
                        'max' => 8,
                        'inclusive' => false));
        $newOrChain->attach($between6And8Validator, true, 2);
        array_unshift($this->lt5_Or_Gt10_ReferenceMessages,
                        "The input is not strictly between '6' and '8'");
    
        $between8And10Validator = new Between(array('min' => 8,
                        'max' => 10,
                        'inclusive' => false));
        $newOrChain->attach($between8And10Validator);
        array_push($this->lt5_Or_Gt10_ReferenceMessages,
                        "The input is not strictly between '8' and '10'");
    
        $result = $this->lt5_Or_Gt10_OrValidator->merge($newOrChain);
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $result);
    
        $this->assertTrue($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(6));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
    }

    public function testInvoke()
    {
        $validator = $this->lt5_Or_Gt10_OrValidator;
        $this->assertTrue($validator(4));
        $this->assertFalse($validator(7));
    }
    
    public function testClone()
    {
        $clonedOrValidator = clone $this->lt5_Or_Gt10_OrValidator;
        $this->assertEquals(2, $this->lt5_Or_Gt10_OrValidator->count());
        $this->assertEquals(2, $clonedOrValidator->count());

        $lessThan2Validator = new LessThan(array('max' => 2,
                                                 'inclusive' => false));
        $clonedOrValidator->attach($lessThan2Validator);
        $this->assertEquals(2, $this->lt5_Or_Gt10_OrValidator->count());
        $this->assertEquals(3, $clonedOrValidator->count());
    }

    public function testSleep()
    {
        $serializedValidator = serialize($this->lt5_Or_Gt10_OrValidator);
        $this->lt5_Or_Gt10_OrValidator = unserialize($serializedValidator);
        $this->testIsValid();
        $count = $this->lt5_Or_Gt10_OrValidator->
                    attachByName('LessThan', array('max' => 4))->
                    count();
        $this->assertEquals(3, $count);
    }
    
    public function testEmptyValidator()
    {
        $emptyValidator = new EmptyValidator('string');
        $this->lt5_Or_Gt10_OrValidator->prependValidator($emptyValidator);
        array_unshift($this->lt5_Or_Gt10_ReferenceMessages,
                      'Value must be empty.');
        $this->assertTrue($this->lt5_Or_Gt10_OrValidator->isValid(''));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEmpty($messages);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
}