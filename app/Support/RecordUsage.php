<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Answers "is this record referenced by any other table?" from the database's own foreign keys,
 * so new tables are covered without listing them anywhere.
 *
 * Referencing rows that are soft-deleted still count: departments, designations and workstations
 * are hard-deleted, and their cascades would silently wipe those rows.
 */
class RecordUsage
{
    protected const CACHE_KEY = 'record-usage:foreign-keys';

    /**
     * References the system creates by itself and removes with the record, so they do not mean
     * "in use" (every department gets a chat channel automatically).
     */
    protected const IGNORED = [
        'chat_conversations.department_id',
    ];

    /** @var array<string, list<array{table:string, column:string}>>|null */
    protected static ?array $map = null;

    /**
     * Ids from $ids that are referenced somewhere. One query per referencing column.
     *
     * @param  iterable<string>  $ids
     * @return array<string, true> keyed by id for isset() checks in views
     */
    public static function usedIds(string $table, iterable $ids): array
    {
        $ids = collect($ids)->filter()->unique()->values()->all();

        if ($ids === []) {
            return [];
        }

        $used = [];

        foreach (static::referencesTo($table) as $reference) {
            $pending = array_values(array_diff($ids, array_keys($used)));

            if ($pending === []) {
                break;
            }

            DB::table($reference['table'])
                ->whereIn($reference['column'], $pending)
                ->distinct()
                ->pluck($reference['column'])
                ->each(function ($id) use (&$used) {
                    $used[(string) $id] = true;
                });
        }

        return $used;
    }

    public static function isInUse(Model $model): bool
    {
        return static::usedIds($model->getTable(), [(string) $model->getKey()]) !== [];
    }

    /**
     * Columns in other tables with a foreign key pointing at $table.
     *
     * @return list<array{table:string, column:string}>
     */
    public static function referencesTo(string $table): array
    {
        return static::map()[$table] ?? [];
    }

    /** Called after migrations run, since the set of foreign keys may have changed. */
    public static function flush(): void
    {
        static::$map = null;
        Cache::forget(static::CACHE_KEY);
    }

    /** @return array<string, list<array{table:string, column:string}>> */
    protected static function map(): array
    {
        return static::$map ??= Cache::remember(static::CACHE_KEY, now()->addDay(), function () {
            $map = [];

            // Current database only: with no schema MySQL lists every database on the server
            foreach (Schema::getTableListing(Schema::getCurrentSchemaListing(), schemaQualified: false) as $table) {
                foreach (Schema::getForeignKeys($table) as $foreignKey) {
                    // Single-column keys only; every key in this schema is one uuid column
                    if (count($foreignKey['columns']) !== 1 || in_array($table.'.'.$foreignKey['columns'][0], static::IGNORED, true)) {
                        continue;
                    }

                    $map[$foreignKey['foreign_table']][] = ['table' => $table, 'column' => $foreignKey['columns'][0]];
                }
            }

            return $map;
        });
    }
}
