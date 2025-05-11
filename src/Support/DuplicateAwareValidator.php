<?php

namespace CodedSultan\JobEngine\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;

class DuplicateAwareValidator
{
    /**
     * Create a validator instance that respects the allowDuplicates flag.
     *
     * @param array $data Input row.
     * @param array $rules Validation rules.
     * @param bool $allowDuplicates If true, disables unique constraints.
     * @return ValidatorContract
     */
    public static function make(array $data, array $rules, bool $allowDuplicates = false): ValidatorContract
    {
        $adjustedRules = self::adjustRulesForDuplicates($rules, $allowDuplicates);
        return Validator::make($data, $adjustedRules);
    }

    /**
     * Remove unique constraints when duplicates are allowed.
     */
    protected static function adjustRulesForDuplicates(array $rules, bool $allowDuplicates): array
    {
        if (! $allowDuplicates) {
            return $rules;
        }

        foreach ($rules as $field => &$rule) {
            $rule = is_string($rule) ? explode('|', $rule) : (array) $rule;

            $rule = array_filter($rule, function ($r) {
                return ! (is_string($r) && str_starts_with($r, 'unique:'));
            });
        }

        return $rules;
    }
}
