@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav class="d-flex align-items-center justify-content-end">
            <div class="d-flex align-items-center gap-1">
                {{-- Page Numbers --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-muted">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"
                                      class="d-inline-flex align-items-center justify-content-center fw-semibold border"
                                      style="width: 36px; height: 36px; background-color: #00bcd4; color: white; border-color: #00bcd4; border-radius: 4px; font-size: 14px;">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button"
                                        wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"
                                        class="btn btn-sm d-inline-flex align-items-center justify-content-center border"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                        style="width: 36px; height: 36px; background-color: white; border-color: #dee2e6; color: #495057; border-radius: 4px; font-size: 14px; transition: all 0.2s;"
                                        onmouseover="this.style.backgroundColor='#f8f9fa';"
                                        onmouseout="this.style.backgroundColor='white';">
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
        </nav>
    @endif
</div>
