<?php

namespace App\Services\Engineering;

use App\Models\Engineering\Sequence;
use App\Support\Engineering\Eng;
use Illuminate\Support\Facades\DB;

/**
 * Server-generated document numbers: gapless per year and never reused.
 * Call inside the transaction that creates the document, so a rollback returns the number.
 */
class NumberSequence
{
    // series => digits
    protected const SERIES = [
        'MRV' => 5,
        'PPM' => 5,
        'OX' => 5,
        'JC' => 4,
        'SR' => 4,
        'RCA' => 4,
    ];

    /** e.g. next('MRV') => "MRV/26/00412" */
    public function next(string $series): string
    {
        $year = (int) Eng::now()->format('y');

        return DB::transaction(function () use ($series, $year) {
            Sequence::firstOrCreate(['series' => $series, 'year' => $year]);

            $sequence = Sequence::where(['series' => $series, 'year' => $year])->lockForUpdate()->first();
            $sequence->increment('last_number');

            return sprintf('%s/%02d/%0'.self::SERIES[$series].'d', $series, $year, $sequence->last_number);
        });
    }
}
