<?php

namespace MB;

abstract class Form
{
    const TYPE_NUMBER = 0;
    const TYPE_NUMBER_INTEGER = 1;
    private $inputs;

    public function __construct()
    {
        $this->inputs = [];
    }

    public function addInput(string $name, int $type, bool $required, string $defaultValue = null): self
    {
        if (\array_key_exists($name, $this->inputs)) {
            throw new \DomainException("$name already exists in this form.");
        }
        $this->inputs[$name] = [
            'type' => $type,
            'required' => $required,
            'defaultValue' => $defaultValue,
        ];
        return $this;

    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function validate(array $queryFields): bool
    {
        foreach ($this->inputs as $name => $input) {
            // on vérifie que chaque input du form trouve son équivalent dans queryfields
            if (!\array_key_exists($name, $queryFields)) {
                return false;
            }

            $formInput = trim($queryFields[$name]);
            // on vérifie que les inputs obligatoires du form ont une value
            if ($formInput === '' && $input['required'] === true) {
                return false;
            }

            switch ($input['type']) {
                case self::TYPE_NUMBER:
                    if (!\is_numeric($formInput)) {
                        return false;
                    }
                    break;
                case self::TYPE_NUMBER_INTEGER:
                    if (!\is_numeric($formInput) || intval($formInput) != $formInput) {
                        return false;
                    }
                }
        }

        return true;
    }

}