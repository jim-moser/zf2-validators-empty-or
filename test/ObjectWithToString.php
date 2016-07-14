<?php
/**
 * Sample class with __toString method for use in unit testing.
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) June 9, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator
 *            New BSD License
 */
namespace JimMoser\ValidatorTest;

class ObjectWithToString
{
    protected $toString = 'good test value';
    
    public function __toString()
    {
        return $this->toString;
    }
    
    public function setToString($toString)
    {
        $this->toString = $toString;        
    }
} 