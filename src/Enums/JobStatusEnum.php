<?php

namespace CodedSultan\JobEngine\Enums;

enum JobStatusEnum: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Partial = 'partial';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Partial => 'Partial',
        };
    }
}
