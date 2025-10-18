<div class="row">
    <div class="col-lg-12 col-md-12 col-12">
        <div class="mb-8 d-md-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-3 h2">{{ $title }}</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        @foreach($breadcrumbs as $crumb)
                            @if(!$loop->last)
                                <li class="breadcrumb-item">
                                    <a href="{{ $crumb['url'] ?? '#' }}">{{ $crumb['label'] }}</a>
                                </li>
                            @else
                                <li class="breadcrumb-item active" aria-current="page">
                                    {{ $crumb['label'] }}
                                </li>
                            @endif
                        @endforeach
                    </ol>
                </nav>
            </div>

            {{-- Optional Right-side Slot (buttons, actions, etc.) --}}
            <div>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
