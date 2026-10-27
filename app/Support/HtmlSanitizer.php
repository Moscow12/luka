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

    protected const ALLOWED_ATTRIBUTES = ['href', 'target', 'rel', 'class'];

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

                foreach (iterator_to_array($child->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $value = trim($attr->value);

                    if (! in_array($name, self::ALLOWED_ATTRIBUTES, true)
                        || ($name === 'href' && ! preg_match('#^(https?:|mailto:|/)#i', $value))) {
                        $child->removeAttribute($attr->name);
                    }
                }

                if ($tag === 'a') {
                    $child->setAttribute('target', '_blank');
                    $child->setAttribute('rel', 'noopener noreferrer');
                }
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }
        }
    }
}
