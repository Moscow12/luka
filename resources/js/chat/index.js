import 'emoji-picker-element';
import '../../css/chat.css';
import { registerLauncher } from './launcher';
import { registerPane } from './pane';
import { registerPresence } from './presence';
import { registerTicketCards } from './ticket-cards';

// Alpine is started by Livewire; register our components and stores before it does
document.addEventListener('alpine:init', () => {
    registerPresence(window.Alpine);
    registerPane(window.Alpine);
    registerLauncher(window.Alpine);
});

registerTicketCards();
