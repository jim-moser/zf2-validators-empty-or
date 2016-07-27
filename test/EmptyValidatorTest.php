<?php
namespace JimMoser\ValidatorTest;

use JimMoser\Validator\EmptyValidator;

/**
 * Unit testing for Empty.
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) June 9, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator  
 *            New BSD License
 */
class EmptyValidatorTest extends \PHPUnit_Framework_TestCase
{
    protected $emptyValidator;
    
    public function Setup()
    {
        $this->emptyValidator = new EmptyValidator();
        
        // Reset message length for all classes inheriting from 
        // Zend\Validator\AbstractValidator in case the message length was 
        // changed in any tests.
        $this->emptyValidator->setMessageLength();
    }
    
    public function testConstructorTraversableOptions()
    {
        $iterator = new \ArrayObject(array(
                        'non_existing_option' => 'some other value',
                        'type' => 'integer',
                        'messageLength' => 9,
        ));
        $this->assertTrue($iterator instanceof \Traversable);
        $this->emptyValidator = new EmptyValidator($iterator);
        $messageLength = $this->emptyValidator->getMessageLength();
        $this->assertEquals(9, $messageLength);
        $this->integerTest();
    }
    
    public function testConstructorArrayOptions()
    {
        $iterator = new \ArrayObject(array(
                        'non_existing_option' => 'some other value',
                        'type' => 'integer',
                        'messageLength' => 9,
        ));
        $this->assertTrue($iterator instanceof \Traversable);
        $this->emptyValidator = new EmptyValidator($iterator);
        $messageLength = $this->emptyValidator->getMessageLength();
        $this->assertEquals(9, $messageLength);
        $this->integerTest();
        
        // Type is an array containing a non-integer value.
        $iterator = new \ArrayObject(array(
                        'non_existing_option' => 'some other value',
                        'type' => array('integer'),
                        'messageLength' => 9,
        ));
        $this->assertTrue($iterator instanceof \Traversable);
        $this->emptyValidator = new EmptyValidator($iterator);
        $messageLength = $this->emptyValidator->getMessageLength();
        $this->assertEquals(9, $messageLength);
        $this->integerTest();
    }
    
    public function testGetDefaultType()
    {
        $defaultType = $this->emptyValidator->getDefaultType();
        $this->assertEquals(489, $defaultType);    
    }
    
    public function testInvalidValue()
    {
        $fileResource = opendir('.');
        $result = $this->emptyValidator->isValid($fileResource);
        $this->assertFalse($result);
        $messages = $this->emptyValidator->getMessages();
        $referenceMessages = array(EmptyValidator::INVALID => 'Invalid type given. String, integer, float, ' .
            'boolean, array, or object expected.'
        );
        $this->assertEquals($referenceMessages, $messages);
    }
    
    /**
     * @expectedException Zend\Validator\Exception\InvalidArgumentException
     */
    public function testSetInvalidType()
    {
        $this->emptyValidator->setType(-5);
    }
    
    public function testDefaultEmptyTypes()
    {
        // Object. All objects are to be considered non-empty.
        $this->assertFalse($this->emptyValidator->
                                                isValid(new EmptyValidator()));
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
        
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
        
        $objectWithToString = new ObjectWithToString();
        
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
        
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
        
        // Space.
        $this->assertTrue($this->emptyValidator->isValid('   '));
        $this->assertFalse($this->emptyValidator->isValid('  a '));
        
        // Null.
        $this->assertTrue($this->emptyValidator->isValid(null));
        
        // Empty array.
        $this->assertTrue($this->emptyValidator->isValid(array()));
        
        // Non-empty array.s
        $this->assertFalse($this->emptyValidator->isValid(array('')));
        
        // Empty string.
        $this->assertTrue($this->emptyValidator->isValid(''));
        
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
        
        // Boolean false.
        $this->assertTrue($this->emptyValidator->isValid(false));
        
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
        
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
        
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
        
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
        
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testAllEmptyTypes()
    {
        $this->emptyValidator->setType(EmptyValidator::ALL);
        
        // Object.
        // This is strangely enough considered to empty because objects without
        // __toString method are to be considered empty.
        $this->assertTrue($this->emptyValidator->
                                                isValid(new EmptyValidator()));
        
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertTrue($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        // Objects without __toString method are to be considered empty.
        $countableObject->setCount(5);
        $this->assertTrue($this->emptyValidator->isValid($countableObject));

        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));

        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertTrue($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertTrue($this->emptyValidator->isValid('   '));
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertTrue($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertTrue($this->emptyValidator->isValid(array()));
        
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertTrue($this->emptyValidator->isValid(''));
        
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertTrue($this->emptyValidator->isValid(false));
        
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertTrue($this->emptyValidator->isValid(0));
        
        // String zero.
        $this->assertTrue($this->emptyValidator->isValid('0'));
        
        // Float zero.
        $this->assertTrue($this->emptyValidator->isValid(0.0));
        
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testNone()
    {
        $this->emptyValidator->setType(0);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    public function testBoolean()
    {
        $this->emptyValidator->setType(EmptyValidator::BOOLEAN);
        
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                                                isValid(new EmptyValidator()));
        
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
        
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
        
        $objectWithToString = new ObjectWithToString();
        
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
        
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
        
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
        
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
        
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
        
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
        
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
        
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
        
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
        
        // Boolean false.
        $this->assertTrue($this->emptyValidator->isValid(false));
        
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
        
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
        
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
        
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
        
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testInteger()
    {
        $this->emptyValidator->setType(EmptyValidator::INTEGER);
        $this->integerTest();
    }
    
    public function integerTest()
    {
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertTrue($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testFloat()
    {
        $this->emptyValidator->setType(EmptyValidator::FLOAT);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertTrue($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testString()
    {
        $this->emptyValidator->setType(EmptyValidator::STRING);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertTrue($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testZero()
    {
        $this->emptyValidator->setType(EmptyValidator::ZERO);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertTrue($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testEmptyArray()
    {
        $this->emptyValidator->setType(EmptyValidator::EMPTY_ARRAY);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertTrue($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testNull()
    {
        $this->emptyValidator->setType(EmptyValidator::NULL);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertTrue($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testPhp()
    {
        $this->emptyValidator->setType(EmptyValidator::PHP);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertTrue($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertTrue($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertTrue($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertTrue($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertTrue($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertTrue($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertTrue($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testSpace()
    {
        $this->emptyValidator->setType(EmptyValidator::SPACE);
    
        // Object. Any objects to be considered invalid.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertTrue($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testObject()
    {
        $this->emptyValidator->setType(EmptyValidator::OBJECT);
    
        // Object. Objects considered non-empty in general.
        $this->assertFalse($this->emptyValidator->
                                                isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testObjectString()
    {
        $this->emptyValidator->setType(EmptyValidator::OBJECT_STRING);
    
        // Object. Objects considered non-empty in general.
        // This is strangely enough considered to empty because objects without
        // __toString method are to be considered empty.
        $this->assertTrue($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        // Objects without __toString method are to be considered empty.
        $countableObject = new CountableObject(0);
        $this->assertTrue($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        // Objects without __toString method are to be considered empty.
        $countableObject->setCount(5);
        $this->assertTrue($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertTrue($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
    
    public function testObjectCount()
    {
        $this->emptyValidator->setType(EmptyValidator::OBJECT_COUNT);
    
        // Object. Objects considered non-empty in general.
        $this->assertFalse($this->emptyValidator->
                        isValid(new EmptyValidator()));
    
        // Object implementing \Countable with count = 0.
        $countableObject = new CountableObject(0);
        $this->assertTrue($this->emptyValidator->isValid($countableObject));
    
        // Object implementing \Countable with count > 0.
        $countableObject->setCount(5);
        $this->assertFalse($this->emptyValidator->isValid($countableObject));
    
        $objectWithToString = new ObjectWithToString();
    
        // Object with __toString method that returns non-empty string.
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Object with __toString method that returns empty string.
        $objectWithToString->setToString('');
        $this->assertFalse($this->emptyValidator->isValid($objectWithToString));
    
        // Space.
        $this->assertFalse($this->emptyValidator->isValid('   '));
    
        // Non-space string surrounded by spaces.
        $this->assertFalse($this->emptyValidator->isValid('  a '));
    
        // Null.
        $this->assertFalse($this->emptyValidator->isValid(null));
    
        // Empty array.
        $this->assertFalse($this->emptyValidator->isValid(array()));
    
        // Non-empty array.
        $this->assertFalse($this->emptyValidator->isValid(array('')));
    
        // Empty string.
        $this->assertFalse($this->emptyValidator->isValid(''));
    
        // Non-empty string.
        $this->assertFalse($this->emptyValidator->isValid('a'));
    
        // Boolean false.
        $this->assertFalse($this->emptyValidator->isValid(false));
    
        // Boolean true.
        $this->assertFalse($this->emptyValidator->isValid(true));
    
        // Integer zero.
        $this->assertFalse($this->emptyValidator->isValid(0));
    
        // String zero.
        $this->assertFalse($this->emptyValidator->isValid('0'));
    
        // Float zero.
        $this->assertFalse($this->emptyValidator->isValid(0.0));
    
        // String false.
        $this->assertFalse($this->emptyValidator->isValid('false'));
    }
}