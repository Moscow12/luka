import './bootstrap';

// Import Bootstrap JS
import * as bootstrap from 'bootstrap';

// Import ApexCharts (used by the dashboard charts)
import ApexCharts from 'apexcharts';

// Import Quill (rich text editor used by tickets)
import Quill from 'quill';
import 'quill/dist/quill.snow.css';

// Workspace chat (presence, dock, conversation panes, ticket hover cards)
import './chat/index.js';

// Import Simplebar
import SimpleBar from 'simplebar';

// Import Flatpickr
import './dasher/flatpickr.js';

// Import Theme Switcher
import './dasher/theme-switcher.js';

// Import Sidebar Toggle
import './dasher/sidebar-toggle.js';

// Make Bootstrap available globally
window.bootstrap = bootstrap;

// Make ApexCharts available globally for inline page scripts
window.ApexCharts = ApexCharts;

// Make Quill available globally for the x-rich-editor component
window.Quill = Quill;
