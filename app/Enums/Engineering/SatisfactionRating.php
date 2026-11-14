<?php

namespace App\Enums\Engineering;

/**
 * Requester satisfaction with a finished job. A job with no rating scores 0% ("no score").
 */
enum SatisfactionRating: string
{
    case PoorService = 'poor_service';
    case BelowExpectation = 'below_expectation';
    case MetExpectation = 'met_expectation';
    case AboveExpectation = 'above_expectation';
    case Excellent = 'excellent';

    public function label(): string
    {
        return match ($this) {
            self::PoorService => 'Poor service',
            self::BelowExpectation => 'Below expectation',
            self::MetExpectation => 'Met expectation',
            self::AboveExpectation => 'Above expectation',
            self::Excellent => 'Excellent service',
        };
    }

    public function score(): int
    {
        return match ($this) {
            self::PoorService => 30,
            self::BelowExpectation => 50,
            self::MetExpectation => 75,
            self::AboveExpectation => 85,
            self::Excellent => 100,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PoorService => 'bg-danger',
            self::BelowExpectation => 'bg-warning text-dark',
            self::MetExpectation => 'bg-info',
            self::AboveExpectation, self::Excellent => 'bg-success',
        };
    }

    /** The two lowest ratings must be explained. */
    public function requiresComment(): bool
    {
        return in_array($this, [self::PoorService, self::BelowExpectation], true);
    }
}
