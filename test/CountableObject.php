<?php
/**
 * Sample Countable interface implementation created for use in unit tests.
 * 
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) June 9, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator  
 *            New BSD License
 */
namespace JimMoser\ValidatorTest;

use \Countable;

class CountableObject implements Countable
{
    protected $count;
    
    public function __construct($count = 0)
    {
        $this->setCount($count);
    }
    
    public function count()
    {
        return $this->count;
    }
    
    public function setCount($count = 0)
    {
        $this->count = (int) $count;        
    }
} 