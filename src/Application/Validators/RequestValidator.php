<?php

namespace App\Application\Validators;

use App\Application\Validators\Types\ArrayType;
use App\Application\Validators\Types\EnumType;
use App\Application\Validators\Types\NumberType;
use App\Application\Validators\Types\Required;
use App\Application\Validators\Types\StringType;
use App\Domain\Exceptions\ValidationException;
use ReflectionClass;

class RequestValidator
{
    /**
     * @throws ValidationException
     */
    public static function validate(array $data, string $dtoClass): array
    {
        $reflection = new ReflectionClass($dtoClass);
        $properties = $reflection->getProperties();

        $validatedData = [];
        $errors = [];

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            // Skip id and createdAt, they are managed internally
            if (in_array($propertyName, ['id', 'createdAt'])) {
                continue;
            }

            $attributes = $property->getAttributes();
            $required = false;

            foreach ($attributes as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if ($attributeInstance instanceof Required) {
                    $required = true;
                }

                if (!isset($data[$propertyName])) {
                    if ($required) {
                        $errors[$propertyName] = "Field '{$propertyName}' is required";
                    }
                    continue;
                }

                $value = $data[$propertyName];

                if ($attributeInstance instanceof StringType) {
                    if (!is_string($value)) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be a string";
                        continue;
                    }

                    if ($attributeInstance->minLength !== null && strlen($value) < $attributeInstance->minLength) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be at least {$attributeInstance->minLength} characters";
                        continue;
                    }

                    if ($attributeInstance->maxLength !== null && strlen($value) > $attributeInstance->maxLength) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be no more than {$attributeInstance->maxLength} characters";
                        continue;
                    }

                    $validatedData[$propertyName] = $value;
                }

                if ($attributeInstance instanceof NumberType) {
                    if (!is_numeric($value)) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be a number";
                        continue;
                    }

                    $numericValue = (float) $value;

                    if ($attributeInstance->min !== null && $numericValue < $attributeInstance->min) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be at least {$attributeInstance->min}";
                        continue;
                    }

                    if ($attributeInstance->max !== null && $numericValue > $attributeInstance->max) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be no more than {$attributeInstance->max}";
                        continue;
                    }

                    $validatedData[$propertyName] = $numericValue;
                }

                if ($attributeInstance instanceof EnumType) {
                    $enumClass = $attributeInstance->enumClass;
                    $validValues = $enumClass::values();

                    if (!in_array($value, $validValues)) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be one of: " . implode(', ', $validValues);
                        continue;
                    }

                    $validatedData[$propertyName] = $value;
                }

                if ($attributeInstance instanceof ArrayType) {
                    if (!is_array($value)) {
                        $errors[$propertyName] = "Field '{$propertyName}' must be an array";
                        continue;
                    }

                    $validatedData[$propertyName] = $value;
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $validatedData;
    }
}
