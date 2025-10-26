<li class="nav-item">
    <!-- first link should be active unless you are on the same page -->
    <a class='nav-link {{ url()->current() === $href ? "active" : "" }}' href='{{ $href ?? "#" }}'>
        <div class="d-flex align-items-center gap-2 lh-1">
            <span>
                <i class="{{ $icon ?? 'ti ti-user-circle' }}"></i>
            </span>
            <span>{{ $name }}</span>
        </div>
    </a>
</li>