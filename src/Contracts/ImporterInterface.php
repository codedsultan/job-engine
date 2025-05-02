<?php

namespace CodedSultan\JobEngine\Contracts;

interface ImporterInterface
{
    /**
     * Return validation rules for this model's import.
     */
    public function rules(): array;

    /**
     * Optionally transform data row before saving.
     */
    public function transform(array $row): array;
}
