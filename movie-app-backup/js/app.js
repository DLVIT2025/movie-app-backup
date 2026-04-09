import { initUI } from './ui.js';
import { initAuth } from './auth.js';
import { renderMovies, initMovieFilters } from './movies.js';
import { initVoice } from './voice.js';
import { initAdmin } from './admin.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize common UI patterns
    initUI();
    
    // Initialize Auth (Checks local storage and updates Navbar)
    initAuth();
    
    // Initialize Movie Catalog
    renderMovies();
    initMovieFilters();
    
    // Initialize Voice Commands
    initVoice();
    
    // Initialize Admin
    initAdmin();
    
    
    // City Selector
    initCitySelector();
    
    // Small logic to auto-route to Home
    document.querySelector('.nav-link[data-target="home-section"]').click();
});

// ==================== City Selector ====================
function initCitySelector() {
    const cityBtn = document.getElementById('city-btn');
    const cityDropdown = document.getElementById('city-dropdown');
    const citySearch = document.getElementById('city-search');
    const cityGrid = document.getElementById('city-grid');
    const selectedCityEl = document.getElementById('selected-city');
    if (!cityBtn || !cityDropdown) return;

    // Toggle dropdown
    cityBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        cityDropdown.classList.toggle('hidden');
        if (!cityDropdown.classList.contains('hidden') && citySearch) {
            setTimeout(() => citySearch.focus(), 100);
        }
    });

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (!cityDropdown.contains(e.target) && e.target !== cityBtn) {
            cityDropdown.classList.add('hidden');
        }
    });

    // City selection
    if (cityGrid) {
        cityGrid.addEventListener('click', (e) => {
            const item = e.target.closest('.city-item');
            if (!item) return;
            const city = item.dataset.city;
            
            // Update active state
            cityGrid.querySelectorAll('.city-item').forEach(c => c.classList.remove('active'));
            item.classList.add('active');
            
            // Update button text
            if (selectedCityEl) selectedCityEl.textContent = city;
            
            // Close dropdown
            cityDropdown.classList.add('hidden');
        });
    }

    // Search filtering
    if (citySearch && cityGrid) {
        citySearch.addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase();
            cityGrid.querySelectorAll('.city-item').forEach(item => {
                const name = item.dataset.city.toLowerCase();
                item.style.display = name.includes(q) ? '' : 'none';
            });
        });
    }
}
