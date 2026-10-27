<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use Illuminate\Support\Str;

/**
 * Markdown → safe HTML for chat, with HRP ticket references turned into hover-card chips.
 */
class MessageRenderer
{
    public const TICKET_PATTERN = '/\bHRP-\d{4}-\d{5}\b/';

    public static function countTicketRefs(?string $body): int
    {
        return preg_match_all(self::TICKET_PATTERN, (string) $body);
    }

    public function render(ChatMessage $message): string
    {
        if ($message->trashed()) {
            return '';
        }

        if ($message->type !== 'text') {
            return e((string) $message->body);
        }

        return $this->toHtml((string) $message->body);
    }

    public function toHtml(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'max_nesting_level' => 10,
            'renderer' => ['soft_break' => "<br>\n"],
        ]); // GitHub-flavoured: autolinks, strikethrough and lists included

        return $this->decorate($html);
    }

    /** Messages of 1–3 emoji only are shown large. */
    public static function isEmojiOnly(?string $body): bool
    {
        $body = trim((string) $body);

        return $body !== ''
            && mb_strlen($body) <= 24
            && preg_match('/^(?:\p{Extended_Pictographic}|\p{Emoji_Component}|\x{200D}|\x{FE0F}|\s){1,24}$/u', $body)
            && preg_match_all('/\p{Extended_Pictographic}/u', $body) <= 3;
    }

    /** Links open in a new tab; ticket numbers outside links/code become chips. */
    protected function decorate(string $html): string
    {
        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="__root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $doc->getElementById('__root');
        if (! $root) {
            return $html;
        }

        foreach ($root->getElementsByTagName('a') as $a) {
            $a->setAttribute('target', '_blank');
            $a->setAttribute('rel', 'noopener noreferrer');
        }

        $this->linkTickets($root, $doc);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    protected function linkTickets(DOMNode $node, DOMDocument $doc): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (! in_array(strtolower($child->tagName), ['a', 'code', 'pre'], true)) {
                    $this->linkTickets($child, $doc);
                }

                continue;
            }

            if (! $child instanceof DOMText || ! preg_match(self::TICKET_PATTERN, $child->data)) {
                continue;
            }

            $parts = preg_split('/(\bHRP-\d{4}-\d{5}\b)/', $child->data, -1, PREG_SPLIT_DELIM_CAPTURE);
            foreach ($parts as $part) {
                if (preg_match(self::TICKET_PATTERN, $part)) {
                    $chip = $doc->createElement('a');
                    $chip->setAttribute('href', route('chat.ticket.open', $part));
                    $chip->setAttribute('class', 'chat-ticket-ref');
                    $chip->setAttribute('data-ticket', $part);
                    $chip->setAttribute('target', '_blank');
                    $chip->appendChild($doc->createTextNode($part));
                    $node->insertBefore($chip, $child);
                } elseif ($part !== '') {
                    $node->insertBefore($doc->createTextNode($part), $child);
                }
            }
            $node->removeChild($child);
        }
    }
}
