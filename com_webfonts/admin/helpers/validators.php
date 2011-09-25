<?php

/* License: These generic validation classes are public domain */

class GenericValidationFacade {

    private $_coordinator;
    private $_validators = array();
    private $_hasValidated = false;

    public function addValidator($validator) {
        $this->_validators[] = $validator;
    }

    public function validate($obj = false) {
        $this->_coordinator = new GenericValidationCoordinator($obj);
        foreach ($this->_validators as $validator) {
            $validator->validate($this->_coordinator);
        }
        $this->_hasValidated = true;
        return $this->isValid();
    }

    public function isValid() {
        if (!$this->_hasValidated = true)
            return false;
        return (count($this->_coordinator->getErrors()) == 0);
    }

    public function getErrors() {
        if ($this->isValid())
            return false;
        return $this->_coordinator->getErrors();
    }

}

class GenericValidationCoordinator {

    private $_object;

    public function __construct($obj) {
        $this->_object = $obj;
    }

    private $_errors = array();

    public function addError($error) {
        $this->_errors[] = $error;
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function get($fieldname) {
        return $this->_object->$fieldname;
    }

}

class GenericValidator {

    private $_specification;
    private $_message;

    public function __construct($spec, $mess) {
        $this->_specification = $spec;
        $this->_message = $mess;
    }

    public function validate($coordinator) {
        if ($this->_specification->isSatisfiedBy($coordinator)) {
            return true;
        } else {
            $coordinator->addError($this->_message);
            return false;
        }
    }

}

class GenericFieldsCandidate {

  protected $_fields;

  public function __construct($fields){
    $this->_fields = $fields;
  }

  public function __get($field){
    return $this->_fields[$field];
  }

}

class GenericLinkedFieldValidator {

    private $_specification;
    private $_linked;

    public function __construct($spec, $mess, $linked) {
        $this->_specification = $spec;
        $this->_linked = $linked;
    }

    public function validate($coordinator) {
        $check = $this->_specification->isSatisfiedBy($coordinator);
        if ($check) {
            foreach ($this->_linked as $link) {
                $link->validate($coordinator);
            }
        }
    }

}

abstract class GenericSpecification {

    protected $_fieldName;

    public function __construct($fieldName) {
        $this->_fieldName = $fieldName;
    }

    public function getValidatedField() {
        return $this->_fieldName;
    }

    public function isSatisfiedBy($candidate) {

    }

}

class GenericValueCheckSpec extends GenericSpecification {

    protected $_fieldName = null;
    private $test = null;

    public function __construct($fieldName, $test) {
        $this->_fieldName = $fieldName;
        $this->_test = $test;
    }

    public function isSatisfiedBy($candidate) {
        return($candidate->get($this->_fieldName) === $this->_test);
    }

}

class GenericNotNullSpec extends GenericSpecification {

    public function isSatisfiedBy($candidate) {
        $result = $candidate->get($this->_fieldName);
        return empty($result) ? false : true;
    }

}

class GenericAlphaNumericSpec extends GenericSpecification {

    public function isSatisfiedBy($candidate) {
        $value = $candidate->get($this->_fieldName);
        return ctype_alnum($value);
    }

}

class GenericEmailSpec extends GenericSpecification {

    public function isSatisfiedBy($candidate) {
      if(filter_var($candidate->get($this->_fieldName), FILTER_VALIDATE_EMAIL)) return true;
      return false;
    }

}

class GenericUrlSpec extends GenericSpecification {

    public function isSatisfiedBy($candidate) {
      if(filter_var($candidate->get($this->_fieldName), FILTER_VALIDATE_URL)) return true;
      return false;
    }

}
