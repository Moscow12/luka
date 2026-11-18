{{-- Re-authentication modal for App\Livewire\Concerns\ConfirmsAdminPassword --}}
@if($showAdminPasswordModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:key="admin-password-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit="confirmAdminPassword">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title"><i class="fa-solid fa-user-shield me-2"></i>Confirm your password</h5>
                        <button type="button" class="btn-close" wire:click="cancelAdminPassword"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">This is a sensitive administrative change. Re-enter your password to continue.</p>
                        <input type="password" wire:model="adminPassword" autocomplete="current-password" autofocus
                               class="form-control @error('adminPassword') is-invalid @enderror" placeholder="Your password">
                        @error('adminPassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="cancelAdminPassword">Cancel</button>
                        <button type="submit" class="btn btn-warning" wire:loading.attr="disabled">
                            <i class="fa-solid fa-check me-1"></i>Confirm
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
