<?php

namespace App\Services\Chop;

use App\Models\chopitems;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Finds CHOP items whose names match or look like a given name, so users
 * don't add "Surgical Gloves" when "Gloves, surgical" already exists.
 */
class SimilarItemFinder
{
    public const THRESHOLD = 60;

    /**
     * Lowercase, drop punctuation, singularise each word: "Gloves (Surgical)" → "glove surgical".
     */
    public function normalize(string $name): string
    {
        $words = preg_split('/\s+/', trim(preg_replace('/[^a-z0-9]+/', ' ', Str::lower($name))), -1, PREG_SPLIT_NO_EMPTY);

        return implode(' ', array_map(fn ($word) => strlen($word) > 3 ? Str::singular($word) : $word, $words));
    }

    /** An existing item whose name is the same once normalised (active or not). */
    public function findDuplicate(string $name): ?chopitems
    {
        $normalized = $this->normalize($name);

        if ($normalized === '') {
            return null;
        }

        return chopitems::query()
            ->get(['id', 'name', 'is_active', 'category_id'])
            ->first(fn ($item) => $this->normalize($item->name) === $normalized);
    }

    /**
     * Active items that look like the name, best match first.
     *
     * @return Collection<int, chopitems> each with a `similarity` (0–100) attribute
     */
    public function similar(string $name, int $limit = 5): Collection
    {
        $normalized = $this->normalize($name);

        if (strlen(str_replace(' ', '', $normalized)) < 3) {
            return collect();
        }

        return chopitems::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->get(['id', 'name', 'unit', 'category_id'])
            ->map(function ($item) use ($normalized) {
                $item->similarity = $this->score($normalized, $this->normalize($item->name));

                return $item;
            })
            ->filter(fn ($item) => $item->similarity >= self::THRESHOLD)
            ->sortByDesc('similarity')
            ->take($limit)
            ->values();
    }

    /** Similarity of two normalised names, 0–100. */
    public function score(string $a, string $b): int
    {
        if ($a === '' || $b === '') {
            return 0;
        }

        if ($a === $b) {
            return 100;
        }

        $compactA = str_replace(' ', '', $a);
        $compactB = str_replace(' ', '', $b);

        if ($compactA === $compactB) {
            return 100;
        }

        // Same words in a different order: "glove surgical" vs "surgical glove"
        $wordsA = explode(' ', $a);
        $wordsB = explode(' ', $b);
        $sortedA = $wordsA;
        $sortedB = $wordsB;
        sort($sortedA);
        sort($sortedB);
        if ($sortedA === $sortedB) {
            return 95;
        }

        $scores = [];

        // One name contained in the other: "syringe" in "syringe 5ml"
        $shorter = strlen($compactA) <= strlen($compactB) ? $compactA : $compactB;
        $longer = $shorter === $compactA ? $compactB : $compactA;
        if (strlen($shorter) >= 4 && str_contains($longer, $shorter)) {
            $scores[] = 80;
        }

        // Character similarity catches typos: "parcetamol" vs "paracetamol"
        similar_text($compactA, $compactB, $percent);
        $scores[] = (int) round($percent);

        // Shared words, allowing a one-letter typo in longer words
        $matched = 0;
        foreach ($wordsA as $word) {
            foreach ($wordsB as $other) {
                if ($word === $other || (strlen($word) >= 5 && strlen($other) >= 5 && levenshtein($word, $other) <= 1)) {
                    $matched++;
                    break;
                }
            }
        }
        $scores[] = (int) round($matched / max(count($wordsA), count($wordsB)) * 90);

        return max($scores);
    }
}
