<?php

namespace App\Support;

use App\Enums\AttendanceEventType;
use App\Enums\AttendanceStatus;
use App\Models\AttendanceDay;
use App\Models\AttendanceEvent;

class AttendanceResult
{
    public function __construct(
        public bool $accepted,
        public AttendanceEvent $event,
        public ?AttendanceDay $day,
        public ?AttendanceStatus $status,
        public array $flags,
        public ?string $reason,
        public string $message,
        public bool $canRequestReview,
    ) {}

    public static function make(
        bool $accepted,
        AttendanceEvent $event,
        ?AttendanceDay $day = null,
        ?AttendanceStatus $status = null,
        array $flags = [],
        ?string $reason = null,
        string $message = '',
        ?bool $canRequestReview = null,
    ): self {
        return new self(
            accepted: $accepted,
            event: $event,
            day: $day,
            status: $status,
            flags: $flags,
            reason: $reason,
            message: $message,
            canRequestReview: $canRequestReview ?? (! $accepted && $event->type === AttendanceEventType::CheckIn),
        );
    }
}
