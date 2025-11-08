<div>
    <x-pages.breadcrumn title="Staff Details"
        :breadcrumbs="[
        ['label' => 'Home', 'url' => route('dashboard')],
        ['label' => 'Staff List', 'url' => route('hr.stafflist')],
        ['label' => 'Staff Salary']
        ]">
        <a href="{{ route('hr.stafflist') }}" class="btn btn-sm btn-primary">Back to List</a>
    </x-pages.breadcrumn>
    <!-- row -->
    <x-pages.empheader :age="$age" :gender="$gender" :email="$email" :getFullName="$getfullname" :editUrl="$editUrl" :employee_id="$employee_id" :photo="$photo" />

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-6">
        <div class="col-xl-4 col-lg-12 d-flex flex-column gap-6">
            <x-pages.card title="Set Salary Allowances and Deductions">
                @if($contacts->first())
                <form wire:submit.prevent="saveSalary">
                    @csrf
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="mb-3">
                                <x-forms.input type="number" name="salary" label="Base Salary" placeholder="Enter Salary" step="0.01" required />
                            </div>

                            <h5 class="mb-3 mt-4">Allowances</h5>
                            <div class="bg-light p-4 rounded-3 mb-4">
                                @forelse ($allowances as $allowance)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="selectedAllowances" value="{{ $allowance->id }}" id="allowance_{{ $allowance->id }}">
                                            <label class="form-check-label" for="allowance_{{ $allowance->id }}">
                                                {{ $allowance->name }}
                                            </label>
                                        </div>
                                        <span class="badge bg-success">
                                            {{ number_format($allowance->allowance_value, 2) }}{{ $allowance->type === 'percentage' ? '%' : '' }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No active allowances available</p>
                                @endforelse
                            </div>

                            <h5 class="mb-3">Deductions</h5>
                            <div class="bg-light p-4 rounded-3 mb-4">
                                @forelse ($deductions as $deduction)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="selectedDeductions" value="{{ $deduction->id }}" id="deduction_{{ $deduction->id }}">
                                            <label class="form-check-label" for="deduction_{{ $deduction->id }}">
                                                {{ $deduction->name }}
                                            </label>
                                        </div>
                                        <span class="badge bg-danger">
                                            {{ number_format($deduction->deduction_value, 2) }}{{ $deduction->type === 'percentage' ? '%' : '' }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">No active deductions available</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-save"></i> SAVE SALARY
                        </button>
                    </div>
                </form>
                @else
                    <div class="alert alert-warning" role="alert">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        No active contract found for this employee. Please create a contract first.
                    </div>
                @endif
            </x-pages.card>
        </div>

        <div class="col-xl-4 col-lg-6 d-flex flex-column gap-6">
            <x-pages.card title="Total Deductions">
                @if($activeContract && $activeContract->contractDeductions->where('is_active', true)->count() > 0)
                    <div class="text-center py-4">
                        <h2 class="text-danger mb-3">{{ number_format($totalDeductions, 2) }}</h2>
                        <p class="text-muted mb-4">Total Deductions Amount</p>
                    </div>
                    <div class="list-group">
                        @foreach($activeContract->contractDeductions->where('is_active', true) as $contractDeduction)
                            @php
                                $deduction = $contractDeduction->deduction;
                                $amount = $contractDeduction->amount_override ?? (
                                    $deduction->type === 'percentage'
                                        ? ($salary * $deduction->deduction_value / 100)
                                        : $deduction->deduction_value
                                );
                            @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $deduction->name ?? 'N/A' }}</span>
                                <span class="badge bg-danger rounded-pill">{{ number_format($amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No deductions set yet</p>
                    </div>
                @endif
            </x-pages.card>
        </div>

        <div class="col-xl-4 col-lg-6 d-flex flex-column gap-6">
            <x-pages.card title="Total Allowances">
                @if($activeContract && $activeContract->contractAllowances->where('is_active', true)->count() > 0)
                    <div class="text-center py-4">
                        <h2 class="text-success mb-3">{{ number_format($totalAllowances, 2) }}</h2>
                        <p class="text-muted mb-4">Total Allowances Amount</p>
                    </div>
                    <div class="list-group">
                        @foreach($activeContract->contractAllowances->where('is_active', true) as $contractAllowance)
                            @php
                                $allowance = $contractAllowance->allowance;
                                $amount = $contractAllowance->amount_override ?? (
                                    $allowance->type === 'percentage'
                                        ? ($salary * $allowance->allowance_value / 100)
                                        : $allowance->allowance_value
                                );
                            @endphp
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $allowance->name ?? 'N/A' }}</span>
                                <span class="badge bg-success rounded-pill">{{ number_format($amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fa-solid fa-money-bill-wave fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No allowances set yet</p>
                    </div>
                @endif
            </x-pages.card>
        </div>
    </div>
</div>