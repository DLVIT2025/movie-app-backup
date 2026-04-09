// UI utilities: Toasts, Router, Theme

export const showToast = (message, type = 'info') => {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideInRight 0.3s ease-out reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

export const navigateTo = (sectionId) => {
    document.querySelectorAll('.page-section').forEach(sec => sec.classList.add('hidden'));
    document.getElementById(sectionId).classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if(link.dataset.target === sectionId) {
            link.classList.add('active');
        }
    });

    // Save breadcrumb state for back buttons
    if (sectionId === 'movies-section') {
        localStorage.setItem('lastMovieTarget', 'home');
    }
};

export const toggleTheme = () => {
    const body = document.body;
    body.classList.toggle('dark-mode');
    const isDark = body.classList.contains('dark-mode');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    
    document.getElementById('theme-toggle').innerHTML = isDark 
        ? '<i class="fas fa-sun"></i>' 
        : '<i class="fas fa-moon"></i>';
};

export const initUI = () => {
    // Theme setup
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        document.body.classList.remove('dark-mode');
        document.getElementById('theme-toggle').innerHTML = '<i class="fas fa-moon"></i>';
    } else {
        document.getElementById('theme-toggle').innerHTML = '<i class="fas fa-sun"></i>';
    }

    document.getElementById('theme-toggle').addEventListener('click', toggleTheme);

    // Navigation setup
    document.querySelectorAll('.nav-link, [data-target]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const target = link.dataset.target;
            if (target) {
                navigateTo(target);
            }
        });
    });

    // Login nav button: always show login form
    const loginNavBtn = document.getElementById('login-nav-btn');
    if (loginNavBtn) {
        loginNavBtn.addEventListener('click', () => {
            // Ensure login form is shown (not signup)
            document.getElementById('login-form-wrapper').classList.remove('hidden');
            document.getElementById('signup-form-wrapper').classList.add('hidden');
            navigateTo('auth-section');
        });
    }

    // Password toggles
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const input = e.currentTarget.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                e.currentTarget.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                e.currentTarget.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });

    // Auth tab toggles
    document.getElementById('go-to-signup').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('login-form-wrapper').classList.add('hidden');
        document.getElementById('signup-form-wrapper').classList.remove('hidden');
    });

    document.getElementById('go-to-login').addEventListener('click', (e) => {
        e.preventDefault();
        document.getElementById('signup-form-wrapper').classList.add('hidden');
        document.getElementById('login-form-wrapper').classList.remove('hidden');
    });

    // Showtime chips
    document.querySelectorAll('.showtime-chip:not(.theatre-chip):not(.member-chip)').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.showtime-chip:not(.theatre-chip):not(.member-chip)').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            // Unlock Members
            document.getElementById('member-selector').classList.remove('disabled-section');
        });
    });

    // Theatre chips
    document.querySelectorAll('.theatre-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.theatre-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            // Unlock Showtime
            document.getElementById('showtime-selector').classList.remove('disabled-section');
        });
    });

    // Member chips
    document.querySelectorAll('.member-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.member-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            // Unlock Book Btn
            const btn = document.getElementById('book-ticket-btn');
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
            }
        });
    });

    // Payment modal tabs
    document.querySelectorAll('.pay-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.pay-tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.pay-tab-content').forEach(c => c.classList.add('hidden'));
            document.getElementById(`pay-tab-${btn.dataset.tab}`).classList.remove('hidden');
        });
    });

    // Payment modal close
    const payClose = document.getElementById('payment-modal-close');
    if (payClose) payClose.addEventListener('click', () => {
        document.getElementById('payment-modal').classList.add('hidden');
    });

    // Confirm payment handler moved to booking.js to avoid dynamic import issues

    // Card number auto-format
    const cardNum = document.getElementById('pay-card-number');
    if (cardNum) {
        cardNum.addEventListener('input', () => {
            let v = cardNum.value.replace(/\D/g, '').substr(0, 16);
            cardNum.value = v.replace(/(\d{4})/g, '$1 ').trim();
        });
    }
    // Card expiry auto-format
    const cardExp = document.getElementById('pay-card-expiry');
    if (cardExp) {
        cardExp.addEventListener('input', () => {
            let v = cardExp.value.replace(/\D/g, '').substr(0, 4);
            if (v.length >= 3) v = v.substr(0, 2) + '/' + v.substr(2);
            cardExp.value = v;
        });
    }
};
