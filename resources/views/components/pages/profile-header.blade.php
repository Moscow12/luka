<div class="card card-lg overflow-hidden">
    <div class="pt-16 rounded-top position-relative"
        style="background: url({{ $cover ?? asset('assets/images/background/profile-cover.jpg') }}) no-repeat; background-size: cover">
        <div class="position-absolute top-0 end-0 m-4">
            {{ $actions ?? '' }}
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row gap-4">
            <div>
                <img src="{{ $avatar ?? asset('assets/images/avatar/avatar-1.jpg') }}" 
                     alt="Avatar" class="rounded-circle avatar avatar-xl" />
            </div>

            <div class="d-flex flex-column flex-lg-row justify-content-between w-100 gap-2">
                <div class="d-lg-flex flex-lg-column">
                    <h3 class="mb-0">{{ $employee->getFullName() }}</h3>
                    <div class="d-lg-flex align-items-center gap-2">
                        <span>{{ $employee->getAgeAttribute() }}</span>
                        <span class="text-secondary">{{ $employee->gender }}</span>
                        <span class="text-secondary">{{ $employee->email }}</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-10">
                    <div class="d-flex flex-column">
                        <span class="fw-semibold fs-5">{{ $followers ?? '0' }}</span>
                        <span class="text-secondary">Followers</span>
                    </div>
                    <div class="d-flex flex-column">
                        <span class="fw-semibold fs-5">{{ $following ?? '0' }}</span>
                        <span class="text-secondary">Following</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
