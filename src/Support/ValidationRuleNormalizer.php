<?php

namespace CodedSultan\JobEngine\Support;

class ValidationRuleNormalizer
{
    /**
     * Normalize rules by removing 'unique:' constraints if allowDuplicates is true.
     *
     * @param array $rules Original rules array
     * @param bool $allowDuplicates Whether to remove uniqueness rules
     * @return array Normalized rules
     */
    public static function normalize(array $rules, bool $allowDuplicates = false): array
    {
        if (! $allowDuplicates) {
            return $rules;
        }

        $normalized = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules)
                ? explode('|', $fieldRules)
                : (array) $fieldRules;

            $fieldRules = array_filter($fieldRules, function ($rule) {
                return ! (is_string($rule) && str_starts_with($rule, 'unique:'));
            });

            $normalized[$field] = $fieldRules;
        }

        return $normalized;
    }
}
