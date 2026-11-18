<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Whitelist sanitiser for rich text coming from the Quill editor.
 */
class HtmlSanitizer
{
    protected const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'a', 'ul', 'ol', 'li',
        'h1', 'h2', 'h3', 'blockquote', 'pre', 'code', 'span',
    ];

    /** Attributes kept per tag ('*' = any allowed tag). target/rel are always set by us on links. */
    protected const ALLOWED_ATTRIBUTES = ['*' => ['class'], 'a' => ['href']];

    /** Only Quill's own formatting classes survive (no arbitrary/Bootstrap classes for UI redressing). */
    protected const CLASS_PATTERN = '/^ql-[a-z0-9-]{1,40}$/';

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) {
            return e(strip_tags($html));
        }

        static::cleanNode($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    /** Plain-text version, for snippets. */
    public static function toText(?string $html, int $limit = 0): string
    {
        $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '<br>'], ' ', (string) $html)))));

        return $limit > 0 ? \Illuminate\Support\Str::limit($text, $limit) : $text;
    }

    public static function isBlank(?string $html): bool
    {
        return static::toText($html) === '';
    }

    protected static function cleanNode(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                $tag = strtolower($child->tagName);

                if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
                    $node->removeChild($child);

                    continue;
                }

                static::cleanNode($child);

                if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Keep the text, drop the tag
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);

                    continue;
                }

                $allowed = array_merge(self::ALLOWED_ATTRIBUTES['*'], self::ALLOWED_ATTRIBUTES[$tag] ?? []);

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);

                    if (! in_array($name, $allowed, true)) {
                        $child->removeAttribute($attr->name);

                        continue;
                    }

                    if ($name === 'href') {
                        if (static::isSafeHref($attr->value)) {
                            $child->setAttribute('href', trim($attr->value));
                        } else {
                            $child->removeAttribute($attr->name);
                        }
                    }

                    if ($name === 'class') {
                        $classes = array_filter(preg_split('/\s+/', trim($attr->value)), fn ($c) => preg_match(self::CLASS_PATTERN, $c));
                        $classes === [] ? $child->removeAttribute('class') : $child->setAttribute('class', implode(' ', $classes));
                    }
                }

                if ($tag === 'a') {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer nofollow');
                }
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }

    /**
     * http(s), mailto, or a same-site path. Rejects javascript:/data: and other schemes,
     * protocol-relative "//host" and "/\host" (both leave the site), and control characters.
     */
    public static function isSafeHref(string $href): bool
    {
        $href = trim($href);

        if ($href === '' || preg_match('/[\x00-\x1F\x7F]/', $href)) {
            return false;
        }

        if (preg_match('#^/(?![/\\\\])#', $href)) {
            return true;
        }

        return (bool) preg_match('#^(https?://[^\s/\\\\]|mailto:[^\s])#i', $href);
    }
}
