import { showToast, navigateTo } from './ui.js';

// Basic state
let currentUser = null;

export const initAuth = () => {
    // Check session
    const sessionUserStr = localStorage.getItem('ct_session_user');
    if (sessionUserStr) {
        try {
            const user = JSON.parse(sessionUserStr);
            if (user && user.email) {
                loginSession(user);
            }
        } catch(e) {
            localStorage.removeItem('ct_session_user');
        }
    }

    // Forms
    const loginForm = document.getElementById('login-form');
    const signupForm = document.getElementById('signup-form');
    const logoutBtn = document.getElementById('logout-btn');
    const forgotPwLink = document.getElementById('forgot-password-link');

    if(loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value.trim().toLowerCase();
            const password = document.getElementById('login-password').value;
            
            // Email regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                return showToast('Please enter a valid email address.', 'error');
            }

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Success
                    loginSession(result.user);
                    showToast(`Welcome back, ${result.user.name}!`, 'success');
                    loginForm.reset();
                    navigateTo('home-section');
                } else {
                    showToast(result.message || 'Login failed', 'error');
                }
            } catch (error) {
                console.error('Login error:', error);
                showToast('An error occurred during login. Please try again.', 'error');
            }
        });
    }

    if(signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.getElementById('signup-name').value.trim();
            const email = document.getElementById('signup-email').value.trim().toLowerCase();
            const password = document.getElementById('signup-password').value;

            // Simple validations
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                return showToast('Please enter a valid email address.', 'error');
            }
            if (password.length < 6) {
                return showToast('Password must be at least 6 characters.', 'error');
            }

            try {
                const response = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name, email, password })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loginSession(result.user);
                    showToast(`Account created! Welcome to CineTicket, ${name}.`, 'success');
                    signupForm.reset();
                    navigateTo('home-section');
                } else {
                    showToast(result.message || 'Registration failed', 'error');
                }
            } catch (error) {
                console.error('Signup error:', error);
                showToast('An error occurred during signup. Please try again.', 'error');
            }
        });
    }

    if(logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            currentUser = null;
            localStorage.removeItem('ct_session_user');
            updateNavState();
            showToast('You have been logged out.', 'info');
            navigateTo('home-section');
        });
    }

    if(forgotPwLink) {
        forgotPwLink.addEventListener('click', async (e) => {
            e.preventDefault();
            const email = prompt("Enter your registered email address to reset password:");
            if(email) {
                const finalEmail = email.trim().toLowerCase();
                const newPw = prompt("Enter your new password (min 6 chars):");
                
                if (newPw && newPw.length >= 6) {
                    try {
                        const response = await fetch('api/forgot_password.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ email: finalEmail, newPassword: newPw })
                        });
                        
                        const result = await response.json();
                        if (result.success) {
                            showToast(result.message, 'success');
                        } else {
                            showToast(result.message || "Failed to update password.", 'error');
                        }
                    } catch (error) {
                        console.error('Forgot password error:', error);
                        showToast('An error occurred. Please try again.', 'error');
                    }
                } else {
                    showToast("Invalid password. Must be at least 6 characters.", 'error');
                }
            }
        });
    }
};

const loginSession = (user) => {
    currentUser = user;
    localStorage.setItem('ct_session_user', JSON.stringify(user));
    updateNavState();
};

const updateNavState = () => {
    const authReqLinks = document.querySelectorAll('.auth-req');
    const adminNavLink = document.querySelector('.nav-link[data-target="admin-section"]');
    const loginNavBtn = document.getElementById('login-nav-btn');
    const userProfileNav = document.getElementById('user-profile-nav');

    if (currentUser) {
        // Logged in
        authReqLinks.forEach(link => {
            // Hide admin link unless user is admin
            if (link.dataset.target === 'admin-section') {
                link.classList.toggle('hidden', !currentUser.isAdmin);
            } else {
                link.classList.remove('hidden');
            }
        });
        loginNavBtn.classList.add('hidden');
        userProfileNav.classList.remove('hidden');
        
        document.getElementById('nav-username').textContent = currentUser.name;
        document.getElementById('nav-avatar').textContent = currentUser.name.charAt(0).toUpperCase();
    } else {
        // Logged out
        authReqLinks.forEach(link => link.classList.add('hidden'));
        loginNavBtn.classList.remove('hidden');
        userProfileNav.classList.add('hidden');
    }
};

export const getCurrentUser = () => currentUser;
export const requireAuth = (callback) => {
    if (!currentUser) {
        showToast('Please sign in to access this feature.', 'info');
        navigateTo('auth-section');
        return false;
    }
    if (callback) callback();
    return true;
};
