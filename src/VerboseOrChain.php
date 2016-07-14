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
 * Union Messages:
 * Upon validation failure, this class inserts additional "union" messages 
 * between the sets of failure messages from each validator that is to have its 
 * failure messages added to the chain's failure messages upon failure.
 * 
 * The default union message is the message that is inserted between the 
 * validation failure messages of adjacent validators unless overridden by the 
 * prior validator's trailing message or the following validator's leading 
 * message. If the default union message is an empty string ('') or null, then 
 * there should be no messages inserted unless overridden by a validator's 
 * leading or trailing message.     
 *
 * A validator's leading (union) message overrides the default union message  
 * that would otherwise be placed just prior to the validator's failure 
 * messages. Use null to defer to the default union message. Use an empty string 
 * ('') to specify there should be no union message immediately prior to the 
 * validators failure messages.
 * 
 * A validator's trailing (union) message overrides both the following 
 * validator's leading message and the default union message that would 
 * otherwise be placed just after the validator's failure messages. Use null to 
 * defer to the following validator's leading message or the default union 
 * message. Use an empty string ('') to specify there should be no union message 
 * added between the validator's failure messages and the failure messages of 
 * any following validators.
 * 
 * Note that union messages, whether they are a default union message, a 
 * leading message, or a trailing message, are only added BETWEEN the failure 
 * messages of adjacent validators. No union message is added prior to the 
 * failure messages of the first validator to have it messages shown, or after 
 * the failure message of the last validator to have its messages shown, 
 * regardless of what is specified for leading, trailing, or default union 
 * messages.
 * 
 * Pre-Message:
 * The pre-message is a single validation failure message to be placed before 
 * all the other validation failure messages from this validator chain. This 
 * message is added upon failure regardless of whether any of the individual 
 * validators in the chain generated any validation failure messages. Specify 
 * null to indicate there should be no pre-message. There is no pre-message by 
 * default.
 * 
 * Post-Message:
 * The post-message is a single validation failure message to be placed after 
 * all the other validation failure messages from this validator chain. This 
 * message is added upon failure regardless of whether any of the individual 
 * validators in the chain generated any validation failure messages. Specify 
 * null to indicate there should be no post-message. There is no post-message by 
 * default.
 * 
 * This validation class extends Zend\Validator\AbstractValidator and supports 
 * setting the maximum message length, message value substitution, message 
 * variable substitution, message translation, and obscuring values. However, 
 * these features are only applied to the pre-message, post-message, and union 
 * messages. The messages from contained validators are passed through intact 
 * (with the exception that the message keys are changed to unique identifiers). 
 * If a contained validator's messages are to have any of these features applied 
 * simply configure the contained validator directly with the appropriate 
 * options.
 * 
 * The count of validators added to the chain is the only variable available for 
 * message variable substitution. Use %count% within the message templates to 
 * reference it.
 * 
 * Validation Failure Message Order Summary:
 *     Pre-message
 *     First validator's messages
 *                            / Prior validator's trailing message
 *     Union message: Either <  Next validator's leading message
 *                            \ Default union message
 *     Second validator's messages
 *     ...
 *     ...
 *     Post-message
 * 
 * This class was written by Jim Moser. Much of the code is taken from or a 
 * modification of the code in Zend\Validator\ValidatorChain class version 
 * 2.5.1 of the Zend Framework (http://framework.zend.com/).
 *
 * @author    Jim Moser <jmoser@epicride.info>
 * @link      http://github.com/jim-moser/zf2/validator for source repository
 * @copyright Copyright (c) May 3, 2016 Jim Moser
 * @license   LICENSE.txt at http://github.com/jim-moser/zf2/validator  
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

class VerboseOrChain extends AbstractValidator implements Countable
{
    /**
     * Default priority at which validators are added.
     */
    const DEFAULT_PRIORITY = 1;
    
    /**
     * Message template keys.
     */
    const DEFAULT_UNION_MESSAGE_TEMPLATE_KEY = 'DefaultUnionMessageTemplate';
    const PRE_MESSAGE_TEMPLATE_KEY = 'PreMessageTemplate';
    const POST_MESSAGE_TEMPLATE_KEY = 'PostMessageTemplate';
    
    /**
     * Default validation failure message template definitions.
     * 
     * DEFAULT_UNION_MESSAGE_TEMPLATE_KEY =>  Default validation failure message  
     *     to be placed between failure messages of adjacent validators.
     * PRE_MESSAGE_TEMPLATE_KEY => Validation failure message placed before all 
     *     other validation failure messages from this validator.
     * POST_MESSAGE_TEMPLATE_KEY => Validation failure message placed after all  
     *     other validation failure messages from this validator.  
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::DEFAULT_UNION_MESSAGE_TEMPLATE_KEY => ' or ',
        self::PRE_MESSAGE_TEMPLATE_KEY => null,
        self::POST_MESSAGE_TEMPLATE_KEY => null,
    );
    
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
     * Returns template of default message used to join validation failure 
     * messages of adjacent validators.
     * 
     * @return string
     */
    public function getDefaultUnionMessageTemplate()
    {
        return $this->abstractOptions['messageTemplates']
            [self::DEFAULT_UNION_MESSAGE_TEMPLATE_KEY];
    }
    
    /**
     * Sets template of default message used to join validation failure messages 
     * of adjacent validators.
     * 
     * Use an empty string ('') to specify no message should be added between 
     * the failure messages of adjacent validators.
     * 
     * @param string $template
     * @return self
     */
    public function setDefaultUnionMessageTemplate($template)
    {
        $this->abstractOptions['messageTemplates']
            [self::DEFAULT_UNION_MESSAGE_TEMPLATE_KEY] = (string) $template;
        return $this;
    }
    
    /**
     * Returns template of validation failure message to be inserted at 
     * beginning of validation failure message map.
     * 
     * Returns null if no message was specified.
     *
     * @return string|null
     */
    public function getPreMessageTemplate()
    {
        return $this->abstractOptions['messageTemplates']
            [self::PRE_MESSAGE_TEMPLATE_KEY];
    }
    
    /**
     * Sets template of validation failure message to be inserted at beginning 
     * of validation failure message map. 
     *
     * Use null to specify no message should be inserted.
     *
     * @param string|null $template
     * @return self
     */
    public function setPreMessageTemplate($template = null)
    {
        if (null !== $template) {
            $template = (string) $template;
        }
        $this->abstractOptions['messageTemplates']
                              [self::PRE_MESSAGE_TEMPLATE_KEY] = $template;
        return $this;
    }
    
    /**
     * Returns template of validation failure message to be inserted at end of 
     * validation failure message map.
     *
     * Returns null if no message was specified.
     *
     * @return string|null
     */
    public function getPostMessageTemplate()
    {
        return $this->abstractOptions['messageTemplates']
            [self::POST_MESSAGE_TEMPLATE_KEY];
    }
    
    /**
     * Sets template of validation failure message to be inserted at end of 
     * validation failure message map.
     *
     * Use null to specify no message should be inserted.
     *
     * @param string|null $postMessage
     * @return self
     */
    public function setPostMessageTemplate($postMessage = null)
    {
        if (null !== $postMessage) {
            $postMessage = (string) $postMessage;
        }
        $this->abstractOptions['messageTemplates']
                              [self::POST_MESSAGE_TEMPLATE_KEY] = $postMessage;
        return $this;
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
     * @param string|null        $leadingTemplate  Validator's leading message 
     *                                             template.
     * @param string|null        $trailingTemplate Validator's trailing message 
     *                                             template.
     * @param int                $priority         Validator's priority.
     * @throws Exception\InvalidArgumentException
     * @return self
     */
    public function attach(ValidatorInterface $validator,
                           $showMessages = true,
                           $leadingTemplate = null,
                           $trailingTemplate = null,
                           $priority = self::DEFAULT_PRIORITY)
    {
        $this->validators->insert(
            array(
                'instance' => $validator,
                'show_messages' => (bool)$showMessages,
                'leading_message_template' => $leadingTemplate,
                'trailing_message_template' => $trailingTemplate,
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
     * @param string|null        $leadingTemplate  Validator's leading message 
     *                                             template.
     * @param string|null        $trailingTemplate Validator's trailing message 
     *                                             template.
     * @return self
     */
    public function prependValidator(ValidatorInterface $validator,
                                     $showMessages = true,
                                     $leadingTemplate = null,
                                     $trailingTemplate = null)
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
                'leading_message_template' => $leadingTemplate,
                'trailing_message_template' => $trailingTemplate,
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
     * @param string|null    $leadingTemplate  Validator's leading message 
     *                                         template.
     * @param string|null    $trailingTemplate Validator's trailing message 
     *                                         template.
     * @param int            $priority         Validator's priority.
     * @return self
     */
    public function attachByName($name,
                                 $options = array(),
                                 $showMessages = true,
                                 $leadingTemplate = null,
                                 $trailingTemplate = null,
                                 $priority = self::DEFAULT_PRIORITY)
    {
        $validator = $this->plugin($name, $options);
        if (isset($options['show_messages'])) {
            $showMessages = (bool) $options['show_messages'];
        }
        if (isset($options['leading_message_template'])) {
            $leadingTemplate = (string) $options['leading_message_template'];
        }
        if (isset($options['trailing_message_template'])) {
            $trailingTemplate = (string) $options['trailing_message_template'];
        }
        if (isset($options['priority'])) {
            $priority = (int) $options['priority'];
        }
        $this->attach($validator,
                      $showMessages,
                      $leadingTemplate,
                      $trailingTemplate,
                      $priority);
        return $this;
    }

    /**
     * Uses plugin manager to prepend validator by name.
     *
     * @param string         $name
     * @param array          $options
     * @param boolean        $showMessages     Show messages for this validator 
     *                                         on failure.
     * @param string|null    $leadingTemplate  Validator's leading message 
     *                                         template.
     * @param string|null    $trailingTemplate Validator's trailing message
     *                                         template.
     * @return self
     */
    public function prependByName($name,
                                  $options = array(),
                                  $showMessages = true,
                                  $leadingTemplate = null,
                                  $trailingTemplate = null)
    {
        $validator = $this->plugin($name, $options);

        //To do. Investigate why Zend\Validator\ValidatorChain does not check 
        //for $options['break_on_failure'] in this method.
        if (isset($options['show_messages'])) {
            $showMessages = (bool) $options['show_messages'];
        }
        if (isset($options['leading_message_template'])) {
            $leadingTemplate = (string) $options['leading_message_template'];
        }
        if (isset($options['trailing_message_template'])) {
            $trailingTemplate = (string) $options['trailing_message_template'];
        }
        $this->prependValidator($validator,
                                $showMessages,
                                $leadingTemplate,
                                $trailingTemplate);
        return $this;
    }
    
    /**
     * Constructs and returns validation failure message for specified message 
     * template and value.
     *
     * This is used in place of AbstractValidator::createMessage() since leading 
     * and trailing union messages are not stored with a message key under 
     * abstractOptions['messageTemplates'].
     * 
     * If a translator is available and a translation exists for $messageKey,
     * the translation will be used.
     *
     * @param  string              $messageTemplate
     * @param  string|array|object $value
     * @return string
     */
    protected function createMessageFromTemplate($messageTemplate, $value)
    {
        // AbstractValidator::translateMessage does not use first argument.
        $message = $this->translateMessage('dummyValue',
                                           (string) $messageTemplate);
    
        if (is_object($value) &&
                        !in_array('__toString', get_class_methods($value))
        ) {
            $value = get_class($value) . ' object';
        } elseif (is_array($value)) {
            $value = var_export($value, 1);
        } else {
            $value = (string) $value;
        }
    
        if ($this->isValueObscured()) {
            $value = str_repeat('*', strlen($value));
        }
    
        $message = str_replace('%value%', (string) $value, $message);
        $message = str_replace('%count%', (string) $this->count(), $message);
    
        $length = self::getMessageLength();
        if (($length > -1) && (strlen($message) > $length)) {
            $message = substr($message, 0, ($length - 3)) . '...';
        }
    
        return $message;
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
                        'leading_message_template' =>
                                        $element['leading_message_template'],
                        'trailing_message_template' => 
                                        $element['trailing_message_template'],
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
        
        $preMessageTemplate = $this->getPreMessageTemplate();
        if (null !== $preMessageTemplate) {
            $preMessage = $this->createMessageFromTemplate($preMessageTemplate,
                                                           $this->value);
            $uniqueKey = $chainUniqueId . (string) $messageIndex;
            $finalMessages[$uniqueKey] = $preMessage;
            $messageIndex++;
        }
        
        foreach ($allMessageData as $validatorIndex => $validatorMessageData) {
            foreach ($validatorMessageData['messages'] as $messageKey =>
                                                                    $message) {
                $uniqueKey = $chainUniqueId . (string) $messageIndex;
                $finalMessages[$uniqueKey] = $message;
                $messageIndex++;
            }
        
            // Add union message if there are following validators with
            // messages.
            if (array_key_exists($validatorIndex + 1, $allMessageData)) {
        
                // If not null, current validator's trailing message overrides
                // next validator's leading message which overrides default 
                // union message.
                $unionMessageTemplate = 
                            $validatorMessageData['trailing_message_template'];
                if (null === $unionMessageTemplate) {
                    $nextValidatorMessageData =
                                        $allMessageData[$validatorIndex + 1];
                    $unionMessageTemplate =
                        $nextValidatorMessageData['leading_message_template'];
                    if (null === $unionMessageTemplate) {
                        $unionMessageTemplate = $this->
                                            getDefaultUnionMessageTemplate();
                    }
                }
        
                // Add union message if template not null and not empty string.
                if (null !== $unionMessageTemplate &&
                    '' !== $unionMessageTemplate) {
                    $uniqueKey = $chainUniqueId . (string) $messageIndex;
                    $unionMessage = $this->createMessageFromTemplate(
                                                        $unionMessageTemplate,
                                                        $this->value
                                                    );
                    $finalMessages[$uniqueKey] = $unionMessage;
                    $messageIndex++;
                }
            }
        }
        
        $postMessageTemplate = $this->getPostMessageTemplate();
        if (null !== $postMessageTemplate) {
            $postMessage = $this->createMessageFromTemplate($postMessageTemplate,
                            $this->value);
            $uniqueKey = $chainUniqueId . (string) $messageIndex;
            $finalMessages[$uniqueKey] = $postMessage;
        }
        
        return $finalMessages;
    }

    /**
     * Merges in logical "or" validator chain provided as argument.
     * 
     * Priorities of validators within the internal priority queues are 
     * maintained. 
     * 
     * Unfortunately this method accesses the OrValidatorChain::validators 
     * property which is protected. This means the type hint for the 
     * $validatorChain argument is restricted to this class. This was borrowed 
     * from Zend\Validator\ValidatorChain and is necessary to obtain the list of 
     * validators as a PriorityQueue so that the validators maintain their 
     * priority when merged in.
     * 
     * A better solution would be to have the getValidators method return a 
     * PriorityQueue instead of an array and have the merge method call the
     * public getValidators method instead of accessing the protected 
     * validators property. This was not done here in order to keep the API 
     * as close as possible to the API of the Zend\Validator\ValidatorChain 
     * class. 
     *
     * @param OrValidatorChain $validatorChain
     * @return self
     */
    public function merge(VerboseOrChain $validatorChain)
    {
        foreach ($validatorChain->validators->toArray(PriorityQueue::EXTR_BOTH)
                                                                    as $item) {
            $this->attach($item['data']['instance'],
                          $item['data']['show_messages'],
                          $item['data']['leading_message_template'],
                          $item['data']['trailing_message_template'],
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
        $allValidatorData = $this->validators->toArray(PriorityQueue::EXTR_DATA);
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