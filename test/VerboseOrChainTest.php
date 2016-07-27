<?php
namespace JimMoser\ValidatorTest;

use JimMoser\Validator\VerboseOrChain;
use JimMoser\Validator\EmptyValidator;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Between;
use Zend\Validator\LessThan;
use Zend\Validator\GreaterThan;
use Zend\Validator\ValidatorChain;
use Zend\Validator\ValidatorPluginManager;

/**
 * Unit test class for Validator\VerboseOrChain.
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) May 24, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator  
 *            New BSD License
 */
class VerboseOrChainTest extends \PHPUnit_Framework_TestCase
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
        $this->lt5_Or_Gt10_OrValidator = new VerboseOrChain();
        $this->lt5_Or_Gt10_OrValidator->attach($lessThanValidator)
                                      ->attach($moreThanValidator);

        // Need to reset static Zend\Validator\AbstractValidator::messageLength
        // property to its original value in case it is changed in any tests.
        $this->lt5_Or_Gt10_OrValidator->setMessageLength(-1);
        
        $this->referenceDefaultUnionMessage = ' or ';
        $this->lt5_Or_Gt10_ReferenceMessages = array(
            "The input is not less than '5'",
            &$this->referenceDefaultUnionMessage,
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
    
    public function testDefaultUnionMessage()
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
    
    public function testModifiedDefaultUnionMessage()
    {
        $defaultUnionMessage = $this->lt5_Or_Gt10_OrValidator->
                                            getDefaultUnionMessageTemplate();
        $this->assertEquals($this->referenceDefaultUnionMessage,
                            $defaultUnionMessage);
        
        $returnValue = $this->lt5_Or_Gt10_OrValidator->
                                    setDefaultUnionMessageTemplate('Neither');
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        $this->referenceDefaultUnionMessage = 'Neither';
        $defaultUnionMessage = $this->lt5_Or_Gt10_OrValidator->
                                            getDefaultUnionMessageTemplate();
        $this->assertEquals($this->referenceDefaultUnionMessage,
                            $defaultUnionMessage);
        
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
        
        // Test count variable substitution.
        $this->lt5_Or_Gt10_OrValidator->
        setDefaultUnionMessageTemplate('Count = %count%.');
        $this->referenceDefaultUnionMessage = 'Count = %count%.';
        $defaultUnionMessage = $this->lt5_Or_Gt10_OrValidator->
        getDefaultUnionMessageTemplate();
        $this->assertEquals($this->referenceDefaultUnionMessage,
                        $defaultUnionMessage);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->referenceDefaultUnionMessage = 'Count = 2.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Test message value substitution.
        $this->lt5_Or_Gt10_OrValidator->
                            setDefaultUnionMessageTemplate('Value = %value%.');
        $this->referenceDefaultUnionMessage = 'Value = %value%.';
        $defaultUnionMessage = $this->lt5_Or_Gt10_OrValidator->
                                            getDefaultUnionMessageTemplate();
        $this->assertEquals($this->referenceDefaultUnionMessage,
                            $defaultUnionMessage);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->referenceDefaultUnionMessage = 'Value = 7.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));

        // Test message obscured value substitution.
        $returnValue = $this->lt5_Or_Gt10_OrValidator->setValueObscured(true);
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->referenceDefaultUnionMessage = 'Value = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Test message translation.
        $translator = new MockTranslator();
        $returnValue = $this->lt5_Or_Gt10_OrValidator->
                                                    setTranslator($translator);
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->referenceDefaultUnionMessage = 'Valor = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Test limiting message length.
        $this->lt5_Or_Gt10_OrValidator->setMessageLength(15);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        foreach ($this->lt5_Or_Gt10_ReferenceMessages as
                                                $key => $referenceMessage) {
            if (strlen($referenceMessage) > 15) {
                $this->lt5_Or_Gt10_ReferenceMessages[$key] = 
                                    substr($referenceMessage, 0, 12) . '...';
            }
        }
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
    
    public function testPreMessage()
    {
        $preMessage = $this->lt5_Or_Gt10_OrValidator->getPreMessageTemplate();
        $this->assertEquals(null, $preMessage);
        
        $returnValue = $this->lt5_Or_Gt10_OrValidator->setPreMessageTemplate(
            'None of the following requirements were met.'
        );
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        array_unshift($this->lt5_Or_Gt10_ReferenceMessages,
                      'None of the following requirements were met.');
        $preMessage = $this->lt5_Or_Gt10_OrValidator->getPreMessageTemplate();
        $this->assertEquals('None of the following requirements were met.',
                            $preMessage);
    
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
        
        // Test limiting message length.
        $this->lt5_Or_Gt10_OrValidator->setMessageLength(15);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        foreach ($this->lt5_Or_Gt10_ReferenceMessages as
                        $key => $referenceMessage) {
            if (strlen($referenceMessage) > 15) {
                $this->lt5_Or_Gt10_ReferenceMessages[$key] =
                substr($referenceMessage, 0, 12) . '...';
            }
        }
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Test count variable substitution.
        $this->lt5_Or_Gt10_OrValidator->
                                    setPreMessageTemplate('Count = %count%.');
        $preMessageTemplate = $this->lt5_Or_Gt10_OrValidator->
                                                        getPreMessageTemplate();
        $this->assertEquals('Count = %count%.', $preMessageTemplate);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->lt5_Or_Gt10_ReferenceMessages[0] = 'Count = 2.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Test message value substitution.
        $this->lt5_Or_Gt10_OrValidator->
                                    setPreMessageTemplate('Value = %value%.');
        $preMessageTemplate = $this->lt5_Or_Gt10_OrValidator->
                                                        getPreMessageTemplate();
        $this->assertEquals('Value = %value%.', $preMessageTemplate);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->lt5_Or_Gt10_ReferenceMessages[0] = 'Value = 7.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));

        // Test message obscured value substitution.
        $this->lt5_Or_Gt10_OrValidator->setValueObscured(true);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->lt5_Or_Gt10_ReferenceMessages[0] = 'Value = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Test message translation.
        $translator = new MockTranslator();
        $this->lt5_Or_Gt10_OrValidator->setTranslator($translator);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $this->lt5_Or_Gt10_ReferenceMessages[0] = 'Valor = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
    
    public function testPostMessage()
    {
        $postMessage = $this->lt5_Or_Gt10_OrValidator->getPostMessageTemplate();
        $this->assertEquals(null, $postMessage);
        
        $returnValue = $this->lt5_Or_Gt10_OrValidator->
                setPostMessageTemplate('The requirements above were not met.');
        $this->assertEquals($this->lt5_Or_Gt10_OrValidator, $returnValue);
        array_push($this->lt5_Or_Gt10_ReferenceMessages,
                   'The requirements above were not met.');
        $postMessage = $this->lt5_Or_Gt10_OrValidator->getPostMessageTemplate();
        $this->assertEquals('The requirements above were not met.',
                            $postMessage);
    
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
        
        // Test limiting message length.
        $this->lt5_Or_Gt10_OrValidator->setMessageLength(15);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        foreach ($this->lt5_Or_Gt10_ReferenceMessages as
                        $key => $referenceMessage) {
            if (strlen($referenceMessage) > 15) {
                $this->lt5_Or_Gt10_ReferenceMessages[$key] =
                substr($referenceMessage, 0, 12) . '...';
            }
        }
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Test count variable substitution.
        $this->lt5_Or_Gt10_OrValidator->
                                    setPostMessageTemplate('Count = %count%.');
        $postMessageTemplate = $this->lt5_Or_Gt10_OrValidator->
                                                    getPostMessageTemplate();
        $this->assertEquals('Count = %count%.', $postMessageTemplate);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        array_pop($this->lt5_Or_Gt10_ReferenceMessages);
        $this->lt5_Or_Gt10_ReferenceMessages[] = 'Count = 2.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Test message value substitution.
        $this->lt5_Or_Gt10_OrValidator->
                                    setPostMessageTemplate('Value = %value%.');
        $postMessageTemplate = $this->lt5_Or_Gt10_OrValidator->
                                                    getPostMessageTemplate();
        $this->assertEquals('Value = %value%.', $postMessageTemplate);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        array_pop($this->lt5_Or_Gt10_ReferenceMessages);
        $this->lt5_Or_Gt10_ReferenceMessages[] = 'Value = 7.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Test message obscured value substitution.
        $this->lt5_Or_Gt10_OrValidator->setValueObscured(true);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        array_pop($this->lt5_Or_Gt10_ReferenceMessages);
        $this->lt5_Or_Gt10_ReferenceMessages[] = 'Value = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));

        // Test message translation.
        $translator = new MockTranslator();
        $this->lt5_Or_Gt10_OrValidator->setTranslator($translator);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        array_pop($this->lt5_Or_Gt10_ReferenceMessages);
        $this->lt5_Or_Gt10_ReferenceMessages[] = 'Valor = *.';
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
    
    /** 
     * Message value substitution for value that is object without _toString 
     * method.
     */
    public function testObjectValueWithoutToStringMethod()
    {
        $orValidator = new VerboseOrChain();
        $objectWithoutToStringMethod = new VerboseOrChain();
        $orValidator->setPreMessageTemplate('Value is %value%.');
        $this->assertFalse($orValidator->isValid($objectWithoutToStringMethod));
        $orValidatorReferenceMessages = array(
            'Value is ' . get_class($objectWithoutToStringMethod) . ' object.'
        );
        $messages = $orValidator->getMessages();
        $this->assertEquals($orValidatorReferenceMessages, 
                            array_values($messages));
    }
    
    /**
     * Message value substitution for value that is object with _toString
     * method.
     */
    public function testObjectValueWithToStringMethod()
    {
        $orValidator = new VerboseOrChain();
        $objectWithToStringMethod = new ObjectWithToString();
        $orValidator->setPreMessageTemplate('Value is %value%.');
        $this->assertFalse($orValidator->isValid($objectWithToStringMethod));
        $orValidatorReferenceMessages = array(
            'Value is ' . (string) $objectWithToStringMethod . '.'
        );
        $messages = $orValidator->getMessages();
        $this->assertEquals($orValidatorReferenceMessages,
                            array_values($messages));
    }
    
    /**
     * Message value substitution for array value.
     */
    public function testArrayValueMessageSubstitution()
    {
        $orValidator = new VerboseOrChain();
        $orValidator->setPreMessageTemplate('Value is %value%.');
        $arrayValue = array('first value', 'second value');
        $this->assertFalse($orValidator->isValid($arrayValue));
        $orValidatorReferenceMessages = array(
                        'Value is ' . var_export($arrayValue, true) . '.'
        );
        $messages = $orValidator->getMessages();
        $this->assertEquals($orValidatorReferenceMessages,
                            array_values($messages));
    }
    
    public function testConstructorOptions()
    {
        $testValidator= new VerboseOrChain(
            array(
                VerboseOrChain::DEFAULT_UNION_MESSAGE_TEMPLATE_KEY => 'construct or',
                VerboseOrChain::PRE_MESSAGE_TEMPLATE_KEY => 'construct pre-message',
                VerboseOrChain::POST_MESSAGE_TEMPLATE_KEY =>
                                                    'construct post-message',
            )
        );
        $defaultUnionMessage = $testValidator->getDefaultUnionMessageTemplate();
        $this->assertEquals('construct or', $defaultUnionMessage);
    
        $preMessage = $testValidator->getPreMessageTemplate();
        $this->assertEquals('construct pre-message', $preMessage);
    
        $postMessage = $testValidator->getPostMessageTemplate();
        $this->assertEquals('construct post-message', $postMessage);
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
            VerboseOrChain::DEFAULT_UNION_MESSAGE_TEMPLATE_KEY => 'construct or',
            VerboseOrChain::PRE_MESSAGE_TEMPLATE_KEY => 'construct pre-message',
            VerboseOrChain::POST_MESSAGE_TEMPLATE_KEY => 'construct post-message',
        ));
        $this->assertTrue($iterator instanceof \Traversable);
        $testValidator= new VerboseOrChain($iterator);
        $defaultUnionMessage = $testValidator->getDefaultUnionMessageTemplate();
        $this->assertEquals('construct or', $defaultUnionMessage);
    
        $preMessage = $testValidator->getPreMessageTemplate();
        $this->assertEquals('construct pre-message', $preMessage);
    
        $postMessage = $testValidator->getPostMessageTemplate();
        $this->assertEquals('construct post-message', $postMessage);
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
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
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
        
        // Specify leading message.
        $lessThanValidator = new LessThan(array('max' => 5,
                        'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                        'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $lt5_Or_Gt10_OrValidator->attach($lessThanValidator);
        $count = $lt5_Or_Gt10_OrValidator->attach($moreThanValidator,
                                                  true,
                                                  'also')
                                         ->count();
        $this->assertEquals(2, $count);
        
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
                        'also',
                        "The input is not greater than '10'",
        );
        
        $this->assertFalse($lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
        
        // Specify trailing message.
        $lessThanValidator = new LessThan(array('max' => 5,
                        'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                        'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $lt5_Or_Gt10_OrValidator->attach($lessThanValidator,
                                         true,
                                         null,
                                        'and');
        $count = $lt5_Or_Gt10_OrValidator->attach($moreThanValidator,
                                                  true,
                                                  'also')
                                         ->count();
        $this->assertEquals(2, $count);
        
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
                        'and',
                        "The input is not greater than '10'",
        );
        
        $this->assertFalse($lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($lt5_Or_Gt10_ReferenceMessages,
                        array_values($messages));
        
        // Verify leading and trailing messages only shown between.
        $lessThanValidator = new LessThan(array('max' => 5,
                        'inclusive' => false));
        $moreThanValidator = new GreaterThan(array('min' => 10,
                        'inclusive' => false));
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $lt5_Or_Gt10_OrValidator->attach($lessThanValidator,
                        true,
                        'not used leading message',
                        'and');
        $count = $lt5_Or_Gt10_OrValidator->attach($moreThanValidator,
                                                  true,
                                                  'also',
                                                  'not used trailing message')
                                          ->count();
        $this->assertEquals(2, $count);
        
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
                        'and',
                        "The input is not greater than '10'",
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
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $count = $lt5_Or_Gt10_OrValidator->attach($lessThanValidator)->
            prependValidator($moreThanValidator)->count();
        $this->assertEquals(2, $count);
        
        $referenceDefaultUnionMessage = ' or ';
        $lt5_Or_Gt10_ReferenceMessages = array(
            "The input is not greater than '10'",
            $referenceDefaultUnionMessage,
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
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $count = $lt5_Or_Gt10_OrValidator->
                    attachByName('LessThan', array('max' => 5))->
                    attachByName('GreaterThan', array('min' => 10))->
                    count();
        $this->assertEquals(2, $count);
        
        $referenceDefaultUnionMessage = ' or ';
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not less than '5'",
                        $referenceDefaultUnionMessage,
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
            'leading_message_template' => 'Some custom leading message',
        );
        $lt5_Or_Gt10_OrValidator->attachByName('LessThan', $options);
        $options = array(
            'min' => 9,
            'show_messages' => true,
            'trailing_message_template' => 'Some custom trailing message',
            'priority' => 2,
        );
        $lt5_Or_Gt10_OrValidator->attachByName('GreaterThan', $options);
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '9'",
                        'Some custom trailing message',
                        "The input is not less than '5'",
                        $referenceDefaultUnionMessage,
                        "The input is not greater than '10'",
                        'Some custom leading message',
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
        $lt5_Or_Gt10_OrValidator= new VerboseOrChain();
        $count = $lt5_Or_Gt10_OrValidator->
                    prependByName('LessThan', array('max' => 5))->
                    prependByName('GreaterThan', array('min' => 10))->
                    count();
        $this->assertEquals(2, $count);
    
        $referenceDefaultUnionMessage = ' or ';
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '10'",
                        $referenceDefaultUnionMessage,
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
                        'leading_message_template' => 
                                                'Some custom leading message',
                        
        );
        $lt5_Or_Gt10_OrValidator->prependByName('LessThan', $options);
        $options = array(
                        'min' => 9,
                        'show_messages' => true,
                        'trailing_message_template' => 
                                                'Some custom trailing message',
                        'non-existing_option' => 'not real',
        );
        $lt5_Or_Gt10_OrValidator->prependByName('GreaterThan', $options);
        $lt5_Or_Gt10_ReferenceMessages = array(
                        "The input is not greater than '9'",
                        'Some custom trailing message',
                        "The input is not less than '6'",
                        $referenceDefaultUnionMessage,
                        "The input is not greater than '10'",
                        $referenceDefaultUnionMessage,
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
        $newOrChain = new VerboseOrChain();
    
        $between6And8Validator = new Between(array('min' => 6,
                        'max' => 8,
                        'inclusive' => false));
        $newOrChain->attach($between6And8Validator, true, null, null, 2);
        array_unshift($this->lt5_Or_Gt10_ReferenceMessages,
                        "The input is not strictly between '6' and '8'",
                        $this->referenceDefaultUnionMessage);
    
        $between8And10Validator = new Between(array('min' => 8,
                        'max' => 10,
                        'inclusive' => false));
        $newOrChain->attach($between8And10Validator);
        array_push($this->lt5_Or_Gt10_ReferenceMessages,
                        $this->referenceDefaultUnionMessage,
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
        $this->testModifiedDefaultUnionMessage();
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
                      'Value must be empty.',
                      ' or ');
        $this->assertTrue($this->lt5_Or_Gt10_OrValidator->isValid(''));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEmpty($messages);
        $this->assertFalse($this->lt5_Or_Gt10_OrValidator->isValid(7));
        $messages = $this->lt5_Or_Gt10_OrValidator->getMessages();
        $this->assertEquals($this->lt5_Or_Gt10_ReferenceMessages,
                            array_values($messages));
    }
}