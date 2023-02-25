import './bootstrap';

import './layout/modals';

// Exercises
import './components/exercises/delete';
import './components/exercises/search';

// Routines
import routines from './components/routines/_index';
import './components/routines/exercises';

Alpine.data('routines', routines);
Alpine.start();
