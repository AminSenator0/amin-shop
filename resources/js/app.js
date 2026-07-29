import './bootstrap';
import './home';
import './product-gallery';
import './product-image-manager';
import './single-image-upload';
import { applyThemePalette, deriveThemePalette } from './theme-palette';

import Alpine from 'alpinejs';
import { initDeleteConfirm } from './delete-confirm';

window.Alpine = Alpine;
window.deriveThemePalette = deriveThemePalette;
window.applyThemePalette = applyThemePalette;

initDeleteConfirm();

Alpine.start();

import('./admin-nav')
    .then(({ initAdminNavSearch }) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAdminNavSearch);
        } else {
            initAdminNavSearch();
        }
    })
    .catch((error) => {
        console.error('Failed to initialize admin nav search:', error);
    });

import('./persian-inputs')
    .then(({ initPersianInputs }) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initPersianInputs);
        } else {
            initPersianInputs();
        }
    })
    .catch((error) => {
        console.error('Failed to initialize Persian inputs:', error);
    });
