<?php
/**
 * Class for validator that accepts multiple validator objects and performs 
 * logical "OR" operation on them during validation such that validator is
 * valid if any validator it contains is valid.
 * 
 * This validator is often referred to as a chain or validator chain in this  
 * source code documentation because it contains multiple validators.
 * 
 * Because this validator can contain other validators it implements the public 
 * methods of the Zend\Validator\ValidatorChain class that are related to adding 
 * validators to the chain although some of the method signatures are different.
 * 
 * However, this class does not inherit from Zend\Validator\ValidatorChain 
 * because doing so would result in the Zend\Validator\ValidatorChain::merge 
 * method incorrectly handling arguments that are instances of this class. This 
 * means instances of this class cannot be used where a 
 * Zend\Validator\ValidatorChain instance is required by a type hint. If a 
 * Zend\Validator\ValidatorChain is required, simply add this validator to a 
 * Zend\Validator\ValidatorChain as would be done for any other validator.
 * 
 * Validation Failure Message Keys:
 * Upon validation failure, the validation failure messages from the validators 
 * within the chain are collected and each message re-assigned a new message key 
 * that is unique. This assignment of unique keys is done to prevent the 
 * elimination of messages with message keys that already exist within the map 
 * of messages.
 * 
 * In Zend\Validator\ValidatorChain, failure messages with keys that already  
 * exist within the message map are not added to the map. This is done to 
 * prevent duplication of failure messages that deliver the same message. This 
 * elimination of redundant failure message keys may be desireable in a chain 
 * that joins validators using a logical "AND" operation but is not desireable 
 * in a chain that joins validators in a logical "OR" operation. This is why the 
 * messages are assigned unique keys in this class.
 *
 * Show Messages Parameter:
 * When a validator object is added to this validator chain it can be specified
 * whether or not the added validator's validation failure messages are to be 
 * added to the failure messages for the chain upon failure.
 *
 * Validator Priority:
 * Validators may be assigned a priority when added to the chain. Validators 
 * with higher priority are validated first. Validators with the same priority 
 * are validated in the order they were added (FIFO). If no priority is 
 * specified then a validator is assigned the default priority.
 *   
 * This validator does not add any validation failure messages to the list 
 * gathered from the contained validators. See the JimMoser\Validator\VerboseOr 
 * class if you would like to add additional failure messages.
 * 
 * This class was written by Jim Moser. Much of the code is taken from or a 
 * modification of the code in Zend\Validator\ValidatorChain class version 
 * 2.5.1 of the Zend Framework (http://framework.zend.com/).
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2-validators-empty-or for source
 *            repository
 * @copyright Copyright (c) June 21, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2-validators-empty-or  
 *            New BSD License
 */

namespace JimMoser\Validator;

use Countable;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\PriorityQueue;
use Zend\Validator\AbstractValidator;
use Zend\Validator\ValidatorInterface;
use Zend\Validator\ValidatorPluginManager;

class OrChain extends AbstractValidator implements Countable
{
    /**
     * Default priority at which validators are added.
     */
    const DEFAULT_PRIORITY = 1;
    
    /**
     * @var ValidatorPluginManager
     */
    protected $plugins;

    /**
     * Validator chain.
     *
     * @var PriorityQueue
     */
    protected $validators;

    /**
     * Initializes validator chain.
     * 
     * * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        $this->validators = new PriorityQueue();
        
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        
        parent::__construct($options);
    }
    
    
    /**
     * Returns count of attached validators.
     *
     * @return int
     */
    public function count()
    {
        return count($this->validators);
    }

    /**
     * Gets plugin manager instance.
     *
     * @return ValidatorPluginManager
     */
    public function getPluginManager()
    {
        if (!$this->plugins) {
            $this->setPluginManager(new ValidatorPluginManager());
        }
        return $this->plugins;
    }

    /**
     * Sets plugin manager instance.
     *
     * @param  ValidatorPluginManager $plugins Plugin manager.
     * @return self
     */
    public function setPluginManager(ValidatorPluginManager $plugins)
    {
        $this->plugins = $plugins;
        return $this;
    }

    /**
     * Retrieves validator by name.
     *
     * @param  string     $name    Name of validator to return.
     * @param  null|array $options Options to pass to validator constructor (if 
     *                             not already instantiated).
     * @return ValidatorInterface
     */
    public function plugin($name, array $options = null)
    {
        $plugins = $this->getPluginManager();
        return $plugins->get($name, $options);
    }
    
    /**
     * Attaches validator to end of chain.
     *
     * @param ValidatorInterface $validator
     * @param boolean            $showMessages     Show messages for this   
     *                                             validator on failure.
     * @param int                $priority         Validator's priority.
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function attach(ValidatorInterface $validator,
                           $showMessages = true,
                           $priority = self::DEFAULT_PRIORITY)
    {
        $this->validators->insert(
            array(
                'instance' => $validator,
                'show_messages' => (bool)$showMessages,
            ),
            $priority
        );
        return $this;
    }
    
    /**
     * Adds validator to beginning of chain.
     *
     * @param ValidatorInterface $validator
     * @param boolean            $showMessages     Show messages for this   
     *                                             validator on failure.
     * @return self
     */
    public function prependValidator(ValidatorInterface $validator,
                                     $showMessages = true)
    {
        $priority = self::DEFAULT_PRIORITY;
        if (!$this->validators->isEmpty()) {
            $queue = $this->validators->getIterator();
            $queue->setExtractFlags(PriorityQueue::EXTR_PRIORITY);
            $extractedNode = $queue->extract();
            $priority = $extractedNode[0] + 1;
        }
    
        $this->validators->insert(
            array(
                'instance' => $validator,
                'show_messages' => (bool) $showMessages,
            ),
            $priority
        );
        return $this;
    }
    
    /**
     * Uses plugin manager to add validator by name.
     *     
     * @param string         $name
     * @param array          $options
     * @param boolean        $showMessages     Show messages for this validator  
     *                                         on failure.
     * @param int            $priority         Validator's priority.
     * @return self
     */
    public function attachByName($name,
                                 $options = array(),
                                 $showMessages = true,
                                 $priority = self::DEFAULT_PRIORITY)
    {
        $validator = $this->plugin($name, $options);
        if (isset($options['show_messages'])) {
            $showMessages = (bool) $options['show_messages'];
        }
        if (isset($options['priority'])) {
            $priority = (int) $options['priority'];
        }
        $this->attach($validator, $showMessages, $priority);
        return $this;
    }

    /**
     * Uses plugin manager to prepend validator by name.
     *
     * @param string         $name
     * @param array          $options
     * @param boolean        $showMessages     Show messages for this validator 
     *                                         on failure.
     * @return self
     */
    public function prependByName($name,
                                  $options = array(),
                                  $showMessages = true)
    {
        $validator = $this->plugin($name, $options);

        //To do. Investigate why Zend\Validator\ValidatorChain does not check 
        //for $options['break_on_failure'] in this method.
        if (isset($options['show_messages'])) {
            $showMessages = (bool) $options['show_messages'];
        }
        $this->prependValidator($validator, $showMessages);
        return $this;
    }
    
    /**
     * Returns true if $value is valid for any validator in chain.
     *
     * The validators are run in the order in which they appear in the priority 
     * queue. If any validator in the chain validates then the chain is 
     * considered valid and validation is not performed on any trailing 
     * validators. 
     *
     * @param  mixed $value
     * @param  mixed $context Extra "context" to provide validator.
     * @return bool
     */
    public function isValid($value, $context = null)
    {
        $allMessageData = array();
        $this->setValue($value);
        
        foreach ($this->validators as $element) {
            $validator = $element['instance'];
            
            // Chain is valid if any single validator in chain is valid.
            if ($validator->isValid($value, $context)) {
                $this->abstractOptions['messages'] = array();
                return true;
            }
            
            if ($element['show_messages']) {
                $messages = $validator->getMessages();
                if (!empty($messages)) {
                    
                    $allMessageData[] = array(
                        'messages' => $messages,
                    );
                }
            }
        }
        
        $this->abstractOptions['messages'] = $this->
                                            aggregateMessages($allMessageData);
        return false;
    }
    
    protected function aggregateMessages(array $allMessageData)
    {
        // Assign each message unique ID. Do not want messages with pre-existing 
        // keys eliminated in this class or by Zend\Validator\ValidatorChain.
        $chainUniqueId = uniqid();
        $messageIndex = 0;
        $finalMessages = array();
        
        foreach ($allMessageData as $validatorIndex => $validatorMessageData) {
            foreach ($validatorMessageData['messages'] as $messageKey =>
                                                                    $message) {
                $uniqueKey = $chainUniqueId . (string) $messageIndex;
                $finalMessages[$uniqueKey] = $message;
                $messageIndex++;
            }
        }
        
        return $finalMessages;
    }

    /**
     * Merges in logical "or" validator chain provided as argument.
     * 
     * Priorities of validators within the internal priority queues are 
     * maintained. 
     * 
     * Unfortunately this method accesses the 
     * OrChain::validators property which is protected. 
     * This means the type hint for the $validatorChain argument is restricted 
     * to this class. This was borrowed from Zend\Validator\ValidatorChain and 
     * is necessary to obtain the list of validators as a PriorityQueue so that 
     * the validators maintain their priority when merged in.
     * 
     * A better solution would be to have the getValidators method return a 
     * PriorityQueue instead of an array and have the merge method call the
     * public getValidators method instead of accessing the protected 
     * validators property. This was not done here in order to keep the API 
     * as close as possible to the API of the Zend\Validator\ValidatorChain 
     * class. 
     *
     * @param OrChain $validatorChain
     * @return self
     */
    public function merge(OrChain $validatorChain)
    {
        foreach ($validatorChain->validators->toArray(PriorityQueue::EXTR_BOTH)
                                                                    as $item) {
            $this->attach($item['data']['instance'],
                          $item['data']['show_messages'],
                          $item['priority']);
        }
        return $this;
    }

    /**
     * Returns array of validation failure messages.
     *
     * Note that unlike AbstractValidator, this method does not eliminate 
     * duplicate messages.
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->abstractOptions['messages'];
    }

    /**
     * Returns array of all validators.
     *
     * @return PriorityQueue
     */
    public function getValidators()
    {
        $allValidatorData = $this->validators->
                                            toArray(PriorityQueue::EXTR_DATA);
        foreach ($allValidatorData as $key => $validatorData) {
            $allValidatorData[$key] = $validatorData['instance'];
        }
        return $allValidatorData;
    }

    /**
     * Invokes chain as command.
     *
     * @param  mixed $value
     * @param  mixed $context Extra "context" to provide validator.
     * @return bool
     */
    //To do. Investigate why Zend\Validator\ValidatorChain does not have 
    //$context parameter for __invoke method.
    public function __invoke($value, $context = null)
    {
        return $this->isValid($value, $context);
    }

    /**
     * Performs deep copy.
     */
    public function __clone()
    {
        $this->validators = clone $this->validators;
        // Do not need to clone $this->value because $this->value gets converted
        // to string for insertion into messages when isValid method is 
        // executed.
    }
    
    /**
     * Prepares validator chain for serialization.
     *
     * The plugin manager ('plugins' property) cannot be serialized. On wakeup  
     * the property remains unset and the next invocation of getPluginManager() 
     * sets the default plugin manager instance (ValidatorPluginManager).
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = get_object_vars($this);
        unset($properties['plugins']);
        return array_keys($properties);
    }
}