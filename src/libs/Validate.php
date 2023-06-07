<?php
namespace Tiny\Libs;

/**
* Rules
* - min
* - max
* - matches
* - alpha
* - string
* - alphanumeric
* - regex
* - number
* - email
* - unique
* - enum
* - equals

* - array
* - greater
* - lesser
*/
class Validate
{
    private bool $passed = false;
    private array $errors = [];
    private $uniqueCallback;
    private array $messageSet = [];

    function __construct($source = [], $ruleSet = [], $messageSet = [])
    {
        $this->uniqueCallback = function () {
            return false;
        };

        $this->check($source, $ruleSet, $messageSet);
    }

    public function check($source, $ruleSet = [], $messageSet = [])
    {

        $this->messageSet = $messageSet;

        foreach ($ruleSet as $item => $rules) {
            foreach ($rules as $rule => $rule_value) {
                $value = isset($source[$item]) && is_string($source[$item]) ? trim($source[$item]) : $source[$item];
                if ($rule === 'message') {
                    continue;
                }

                if ($rule === 'required' && $rule_value) {
                    if (empty($value)) {

                        $customMessage = $this->checkMessage($item, 'required') ? $this->getMessage($item, 'required') : 'Required field.';
                        $this->addError($item, $customMessage);
                    }
                } else if (!empty($value)) {
                    switch ($rule) {
                        case 'min':
                            $this->minimumRule($value, $rule_value, $item, $rule);
                            break;
                        case 'max':
                            $this->maximumRule($value, $rule_value, $item, $rule);
                            break;
                        case 'matches':
                            $this->matchRule($value, $rule_value, $item, $rule, $source);
                            break;
                        case 'alpha':
                            if (!(bool) preg_match('/^[\pL\pM]+$/u', $value)) {
                                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Can only contain alphabets.";
                                $this->addError($item, $customMessage);
                            }
                            break;
                        case 'string':
                        case 'alphanumeric':
                            if ((bool) preg_match('/[^0-9a-z _\.,\(\)\'\"-]+/iu', $value)) {
                                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Can not contain special characters.";
                                $this->addError($item, $customMessage);
                            }
                            break;
                        case 'regex':
                            $this->regexRule($value, $rule_value, $item, $rule);
                            break;
                        case 'number':
                            $this->numberRule($value, $rule_value, $item, $rule);
                            break;
                        case 'email':
                            $this->emailRule($value, $rule_value, $item, $rule);
                            break;
                        case 'unique':
                            $this->uniqueRule($value, $rule_value ?? $this->uniqueCallback, $item, $rule);
                            break;
                        case 'enum':
                            $this->enumRule($value, $rule_value, $item, $rule);
                            break;
                        case 'equals':
                            $this->equalsRule($value, $rule_value, $item, $rule);
                            break;
                            // case 'file':
                            // 	$check = $this->_db->get($rule_value, [$item, '=', $value]);
                            // 	if($check->count()){
                            // 		$this->addError($item, "Field already exist in the database");
                            // 	}
                            // break;
                        default:
                            $this->addError($item, "Validation rule ('{$rule}') doesn't exist");
                    }
                }
            }
        }
        if (empty($this->errors)) {
            $this->passed = true;
        }
        return $this;
    }

    private function minimumRule($value, $rule_value, $item, $rule) {
        if (is_string($value)){
            if (strlen($value) < $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Must be a minimum of {$rule_value} characters.";
                $this->addError($item, $customMessage);
            }
        } else if (is_array($value)) {
            if (count($value) < $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Array must have a minimum of {$rule_value} elements.";
                $this->addError($item, $customMessage);
            }
        } else if (is_numeric($value)) {
            if ($value < $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Number must be a minimum of {$rule_value}.";
                $this->addError($item, $customMessage);
            }
        }
    }

    private function maximumRule($value, $rule_value, $item, $rule) {
        
        if (is_string($value)){
            if (strlen($value) > $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "String must be a maximum of {$rule_value} characters.";
                $this->addError($item, $customMessage);
            }
        } else if (is_array($value)) {
            if (count($value) > $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Array must have a maximum of {$rule_value} elements.";
                $this->addError($item, $customMessage);
            }
        } else if (is_numeric($value)) {
            if ($value > $rule_value) {
                $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Number must be a maximum of {$rule_value}.";
                $this->addError($item, $customMessage);
            }
        }
    }

    private function matchRule($value, $rule_value, $item, $rule, $source) {
        if ($value != $source[$rule_value]) {
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "" . ucwords($rule_value) . " must match " . str_replace('_', ' ', $item) . ".";
            $this->addError($item, $customMessage);
        }
    }

    private function regexRule($value, $rule_value, $item, $rule) {
        if (!(bool) preg_match($rule_value, $value)) {
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Failed to match pattern.";
            $this->addError($item, $customMessage);
        }
    }
    
    private function numberRule($value, $rule_value, $item, $rule) {
        if (!is_numeric($value)) {
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Value is not a number.";
            $this->addError($item, $customMessage);
        }
    }

    private function emailRule($value, $rule_value, $item, $rule) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Must be an email";
            $this->addError($item, $customMessage);
        }
    }

    private function uniqueRule($value, $rule_value, $item, $rule) {
        if (!call_user_func($rule_value, $value, $item, $value)) {
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Invalid value";
            $this->addError($item, $customMessage);
        }
    }
    
    private function enumRule($value, $rule_value, $item, $rule) {
        if (!in_array($value, $rule_value)){
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Invalid value";
            $this->addError($item, $customMessage);
        }
    }

    private function equalsRule($value, $rule_value, $item, $rule) {
        if ($value != $rule_value){
            $customMessage = $this->checkMessage($item, $rule) ? $this->getMessage($item, $rule) : "Invalid value";
            $this->addError($item, $customMessage);
        }
    }

    private function checkMessage($fieldName, $rule): bool
    {
        if (!count($this->messageSet)) {
            return false;
        }

        if (!array_key_exists($fieldName, $this->messageSet)) {
            return false;
        }

        if (!array_key_exists($rule, $this->messageSet[$fieldName])) {
            return false;
        }

        return true;
    }

    private function getMessage($fieldName, $rule): string
    {
        return $this->messageSet[$fieldName][$rule];
    }

    public function unique($callback)
    {
        $this->uniqueCallback = $callback;
    }

    private function addError($field, $error)
    {
        $this->errors[$field][] = $error;
    }

    public function errors()
    {
        return $this->errors;
    }
    
    public function isValid(): bool
    {
        return $this->passed;
    }
}