<?php
/**
 * Validator that is valid only if value is empty.
 * 
 * Use the setType method or the constructor to specify the "types" of values 
 * considered to be empty values.
 * 
 * The isValid method of this class is intended to return the boolean opposite
 * of the result that would be returned by the Laminas\Validator\NotEmpty::isValid 
 * method assuming the objects of both classes are configured with identical 
 * empty types. There are two exceptions to this. The first is that both this 
 * class and NotEmpty will return false if the value being validated is neither 
 * a null value, a string, an integer, a float, a boolean, an array, or an 
 * object. The second exception is that if the value is an object but no object 
 * types were specified as empty types then both this class and NotEmpty will  
 * return false.
 * 
 * Be careful specifying multiple object types for the empty types as this will 
 * result in a value being considered empty if it matches any one of the object 
 * types even though it might not be considered empty according one of the other 
 * object types.
 * 
 * This class was written by Jim Moser. Much of the code was adapted from the 
 * Laminas\Validator\NotEmpty class version 2.5.1 of the Zend Framework 
 * (http://framework.zend.com/).
 * 
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2-validators-empty-or for source 
 *            repository
 * @copyright Copyright (c) June 9, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2-validators-empty-or  
 *            New BSD License
 */

namespace JimMoser\Validator;

use Traversable;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception;

class EmptyValidator extends AbstractValidator
{
    const BOOLEAN       = 0x001;
    const INTEGER       = 0x002;
    const FLOAT         = 0x004;
    const STRING        = 0x008;
    const ZERO          = 0x010;
    const EMPTY_ARRAY   = 0x020;
    const NULL          = 0x040;
    const PHP           = 0x07F;
    const SPACE         = 0x080;
    const OBJECT        = 0x100;
    const OBJECT_STRING = 0x200;
    const OBJECT_COUNT  = 0x400;
    const ALL           = 0x7FF;

    const INVALID  = 'notEmptyInvalid';
    const IS_NOT_EMPTY = 'isEmpty';

    protected $constants = array(
        self::BOOLEAN       => 'boolean',
        self::INTEGER       => 'integer',
        self::FLOAT         => 'float',
        self::STRING        => 'string',
        self::ZERO          => 'zero',
        self::EMPTY_ARRAY   => 'array',
        self::NULL          => 'null',
        self::PHP           => 'php',
        self::SPACE         => 'space',
        self::OBJECT        => 'object',
        self::OBJECT_STRING => 'objectstring',
        self::OBJECT_COUNT  => 'objectcount',
        self::ALL           => 'all',
    );

    /**
     * Default empty types. Total of values = 489.
     *
     * @var array
     */
    protected $defaultType = array(
        self::OBJECT,
        self::SPACE,
        self::NULL,
        self::EMPTY_ARRAY,
        self::STRING,
        self::BOOLEAN
    );

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::IS_NOT_EMPTY => 'Value must be empty.',
        self::INVALID  => 'Invalid type given. String, integer, float, ' .
            'boolean, array, or object expected.',
    );

    /**
     * Options for this validator
     *
     * @var array
     */
    protected $options = array();

    /**
     * Constructor.
     *
     * @param  array|Traversable|int $options OPTIONAL
     */
    public function __construct($options = null)
    {
        $this->setType($this->defaultType);

        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            $options = func_get_args();
            $temp    = array();
            if (!empty($options)) {
                $temp['type'] = array_shift($options);
            }

            $options = $temp;
        }

        parent::__construct($options);
    }

    /**
     * Returns the set types
     *
     * @return array
     */
    public function getType()
    {
        return $this->options['type'];
    }

    /**
     * @return int
     */
    public function getDefaultType()
    {
        return $this->calculateTypeValue($this->defaultType);
    }

    /**
     * @param array|int|string $type
     * @return int
     */
    protected function calculateTypeValue($type)
    {
        if (is_array($type)) {
            $detected = 0;
            foreach ($type as $value) {
                if (is_int($value)) {
                    $detected |= $value;
                } elseif (in_array($value, $this->constants)) {
                    $detected |= array_search($value, $this->constants);
                }
            }

            $type = $detected;
        } elseif (is_string($type) && in_array($type, $this->constants)) {
            $type = array_search($type, $this->constants);
        }

        return $type;
    }

    /**
     * Sets types of values that are to be considered empty.
     *
     * @param  int|array|null $type
     * @throws Exception\InvalidArgumentException
     * @return NotEmpty
     */
    public function setType($type = null)
    {
        $type = $this->calculateTypeValue($type);

        if (!is_int($type) || ($type < 0) || ($type > self::ALL)) {
            throw new Exception\InvalidArgumentException('Unknown type');
        }

        $this->options['type'] = $type;

        return $this;
    }

    /**
     * Returns true only if $value is empty value.
     *
     * @param  mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        if ($value !== null && !is_string($value) && !is_int($value) && 
            !is_float($value) && !is_bool($value) && !is_array($value) &&
            !is_object($value)
        ) {
            $this->error(self::INVALID);
            return false;
        }
        
        $type = $this->getType();
        $this->setValue($value);
        $object  = false;

        // OBJECT_COUNT (countable object)
        if ($type & self::OBJECT_COUNT) {
            $object = true;

            if (is_object($value) && ($value instanceof \Countable) &&
                (count($value) === 0)) {
                return true;
            }
        }

        // OBJECT_STRING (object's toString)
        if ($type & self::OBJECT_STRING) {
            $object = true;

            if ((is_object($value) && (!method_exists($value, '__toString'))) ||
                (is_object($value) && (method_exists($value, '__toString')) &&
                (((string) $value) === ""))) {
                return true;
            }
        }

        // OBJECT (object)
        if ($type & self::OBJECT) {
            // Fall through. Objects are always considered not empty.
        } elseif ($object === false) {
            // If object not allowed but object given then return false.
            if (is_object($value)) {
                $this->error(self::INVALID);
                return false;
            }
        }

        // SPACE ('   ')
        if ($type & self::SPACE) {
            if (is_string($value) && (preg_match('/^\s+$/s', $value))) {
                return true;
            }
        }

        // NULL (null)
        if ($type & self::NULL) {
            if ($value === null) {
                return true;
            }
        }

        // EMPTY_ARRAY (array())
        if ($type & self::EMPTY_ARRAY) {
            if (is_array($value) && ($value == array())) {
                return true;
            }
        }

        // ZERO ('0')
        if ($type & self::ZERO) {
            if (is_string($value) && ($value == '0')) {
                return true;
            }
        }

        // STRING ('')
        if ($type & self::STRING) {
            if (is_string($value) && ($value == '')) {
                return true;
            }
        }

        // FLOAT (0.0)
        if ($type & self::FLOAT) {
            if (is_float($value) && ($value == 0.0)) {
                return true;
            }
        }

        // INTEGER (0)
        if ($type & self::INTEGER) {
            if (is_int($value) && ($value == 0)) {
                return true;
            }
        }

        // BOOLEAN (false)
        if ($type & self::BOOLEAN) {
            if (is_bool($value) && ($value == false)) {
                return true;
            }
        }

        $this->error(self::IS_NOT_EMPTY);
        return false;
    }
}