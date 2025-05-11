<?php

namespace CodedSultan\JobEngine\Support;

class RowIdentifierGenerator
{
    /**
     * Generate a unique hash for a row.
     *
     * @param array $row The row data.
     * @param int|string|null $index The index of the row (used when allowDuplicates is true).
     * @param bool $allowDuplicates If true, include index in hash to differentiate identical rows.
     */
    public static function from(array $row, int|string|null $index = null, bool $allowDuplicates = false): string
    {
        // Always sort keys for consistent hashing
        ksort($row);

        $base = md5(json_encode($row));

        // Add index only if explicitly allowing duplicates
        return $allowDuplicates && $index !== null
            ? $base . "_$index"
            : $base;

    }
}
