<?php

namespace CodedSultan\JobEngine\Contracts;

interface ExporterInterface
{
    /**
     * Optional transform per row.
     */
    public function transform(array $row): array;

    /**
     * (Optional) persist/export logic per row — for streaming cases.
     */
    public function store(array $row): void;
}
