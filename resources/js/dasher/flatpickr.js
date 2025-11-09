// Flatpickr initialization for date pickers
import flatpickr from 'flatpickr';

// Initialize flatpickr on all elements with .flatpickr class
document.addEventListener('DOMContentLoaded', function() {
    initializeFlatpickr();
});

// Livewire hook to reinitialize after DOM updates
document.addEventListener('livewire:navigated', function() {
    initializeFlatpickr();
});

// For Livewire v3
if (typeof Livewire !== 'undefined') {
    Livewire.hook('morph.updated', ({ component, cleanup }) => {
        initializeFlatpickr();
    });
}

function initializeFlatpickr() {
    // Date picker
    const flatpickrElements = document.querySelectorAll('.flatpickr:not(.flatpickr-input)');
    if (flatpickrElements.length) {
        flatpickrElements.forEach(element => {
            flatpickr(element, {
                disableMobile: true,
                dateFormat: 'Y-m-d',
                allowInput: true,
            });
        });
    }

    // Time picker
    const timepickrElements = document.querySelectorAll('.timepickr:not(.flatpickr-input)');
    if (timepickrElements.length) {
        timepickrElements.forEach(element => {
            flatpickr(element, {
                enableTime: true,
                noCalendar: true,
                dateFormat: 'H:i',
                time_24hr: true,
                disableMobile: true,
            });
        });
    }

    // DateTime picker
    const datetimepickrElements = document.querySelectorAll('.datetimepickr:not(.flatpickr-input)');
    if (datetimepickrElements.length) {
        datetimepickrElements.forEach(element => {
            flatpickr(element, {
                enableTime: true,
                dateFormat: 'Y-m-d H:i',
                time_24hr: true,
                disableMobile: true,
            });
        });
    }
}

// Export for manual initialization if needed
window.initializeFlatpickr = initializeFlatpickr;
