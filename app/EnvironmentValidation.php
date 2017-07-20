<?php

namespace Ipunkt\LaravelIndexer;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Validator;

class EnvironmentValidation
{
    const GENERIC_VALIDATION_RULE_PREFIX = 'GENERIC_VALIDATION_RULE_';
    const INPUT_VALIDATION_RULE_PREFIX = 'INPUT_VALIDATION_RULE_';

    /**
     * validates attributes
     *
     * @param array $attributes
     * @throws ValidationException
     */
    public function validate(array $attributes)
    {
        $genericRules = $this->getGenericRules();

        if ($genericRules->count()) {
            /** @var \Illuminate\Contracts\Validation\Validator $validator */
            $validator = Validator::make($attributes, $genericRules->toArray());
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        $rules = $this->getInputValidationRules($attributes);
        if ($rules->count()) {
            /** @var \Illuminate\Contracts\Validation\Validator $validator */
            $validator = Validator::make($attributes, $rules->toArray());
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    /**
     * validates id
     *
     * @param string|int $id
     * @throws ValidationException
     */
    public function validateId($id)
    {
        $attributes = ['id' => $id];

        $genericRules = $this->getGenericRules()->filter(function ($value, $key) {
            return $key === 'id';
        });
        if ($genericRules->count()) {
            /** @var \Illuminate\Contracts\Validation\Validator $validator */
            $validator = Validator::make($attributes, $genericRules->toArray());
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        $rules = $this->getInputValidationRules($attributes);
        if ($rules->count()) {
            /** @var \Illuminate\Contracts\Validation\Validator $validator */
            $validator = Validator::make($attributes, $rules->toArray());
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    /**
     * returns all generic validation rules found as [field => rule]
     *
     * @return Collection
     */
    private function getGenericRules() : Collection
    {
        return collect($_ENV)->filter(function ($value, $key) {
            return starts_with($key, static::GENERIC_VALIDATION_RULE_PREFIX);
        })->mapWithKeys(function ($value, $key) {
            $newKey = Str::lower(Str::substr($key, Str::length(static::GENERIC_VALIDATION_RULE_PREFIX)));
            return [$newKey => $value];
        });
    }

    /**
     * returns input validation rules found as [field => rule]
     *
     * @param array $attributes
     * @return Collection
     */
    private function getInputValidationRules(array $attributes) : Collection
    {
        $rules = collect($attributes)->map(function ($value, $key) {
            $environmentalKey = sprintf(static::INPUT_VALIDATION_RULE_PREFIX . '%s', Str::upper($key));
            return env($environmentalKey, null);
        })->filter(function ($value, $key) {
            return $value !== null;
        });
        return $rules;
    }
}