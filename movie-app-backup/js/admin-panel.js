/**
 * CineTicket Admin Panel — Frontend Logic
 * Handles: login, navigation, CRUD, export, stats
 */

(function () {
    'use strict';

    // ===== STATE =====
    let adminSession = null;
    let allMovies = [];
    let allUsers = [];
    let allBookings = [];
    let pendingDeleteAction = null;

    // ===== DOM REFS =====
    const loginPage = document.getElementById('admin-login-page');
    const layout = document.getElementById('admin-layout');
    const loginForm = document.getElementById('admin-login-form');
    const loginError = document.getElementById('login-error');
    const loginErrorText = document.getElementById('login-error-text');
    const loginBtn = document.getElementById('admin-login-btn');
    const logoutBtn = document.getElementById('admin-logout-btn');
    const pageTitle = document.getElementById('admin-page-title');

    // ===== TOAST =====
    function showToast(msg, type = 'info') {
        const container = document.getElementById('admin-toast-container');
        const toast = document.createElement('div');
        toast.className = `admin-toast ${type}`;
        toast.textContent = msg;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(40px)';
            toast.style.transition = '0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ===== AUTH =====
    function checkSession() {
        const stored = sessionStorage.getItem('ct_admin_session');
        if (stored) {
            try {
                adminSession = JSON.parse(stored);
                showDashboard();
                return;
            } catch (e) { sessionStorage.removeItem('ct_admin_session'); }
        }
        showLogin();
    }

    function showLogin() {
        loginPage.style.display = '';
        layout.classList.remove('active');
    }

    function showDashboard() {
        loginPage.style.display = 'none';
        layout.classList.add('active');

        // Update sidebar user info
        document.getElementById('sidebar-name').textContent = adminSession.name || 'Admin';
        document.getElementById('sidebar-email').textContent = adminSession.email || '';
        document.getElementById('sidebar-avatar').textContent = (adminSession.name || 'A').charAt(0).toUpperCase();

        // Load all data
        loadDashboardStats();
        loadMovies();
        loadUsers();
        loadBookings();
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const email = document.getElementById('admin-email').value.trim();
        const password = document.getElementById('admin-password').value;

        loginBtn.classList.add('loading');
        loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
        loginError.classList.remove('visible');

        try {
            const resp = await fetch('api/admin_login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const result = await resp.json();

            if (result.success) {
                adminSession = result.admin;
                sessionStorage.setItem('ct_admin_session', JSON.stringify(adminSession));
                showToast('Welcome, ' + adminSession.name + '!', 'success');
                showDashboard();
            } else {
                loginErrorText.textContent = result.message || 'Login failed';
                loginError.classList.add('visible');
            }
        } catch (err) {
            loginErrorText.textContent = 'Connection error. Is XAMPP running?';
            loginError.classList.add('visible');
        }

        loginBtn.classList.remove('loading');
        loginBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In to Dashboard';
    });

    logoutBtn.addEventListener('click', () => {
        adminSession = null;
        sessionStorage.removeItem('ct_admin_session');
        showToast('Signed out successfully.', 'info');
        showLogin();
    });

    // ===== NAVIGATION =====
    const sidebarLinks = document.querySelectorAll('.sidebar-link[data-page]');
    const pageTitles = {
        'dashboard-page': 'Dashboard',
        'movies-page': 'Movies',
        'users-page': 'Users',
        'bookings-page': 'Bookings',
        'export-page': 'Export Data'
    };

    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            const target = link.dataset.page;

            // Update sidebar active
            sidebarLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            // Show page
            document.querySelectorAll('.admin-page').forEach(p => p.classList.remove('active'));
            document.getElementById(target).classList.add('active');

            // Update title
            pageTitle.textContent = pageTitles[target] || 'Dashboard';

            // Close mobile sidebar
            document.getElementById('admin-sidebar').classList.remove('open');
        });
    });

    // Mobile menu
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            document.getElementById('admin-sidebar').classList.toggle('open');
        });
    }

    // ===== DASHBOARD STATS =====
    async function loadDashboardStats() {
        try {
            const [moviesResp, usersResp, bookingsResp] = await Promise.all([
                fetch('api/admin_movies.php'),
                fetch('api/admin_users.php'),
                fetch('api/admin_bookings.php')
            ]);

            const moviesData = await moviesResp.json();
            const usersData = await usersResp.json();
            const bookingsData = await bookingsResp.json();

            const movieCount = moviesData.success ? moviesData.movies.length : 0;
            const userCount = usersData.success ? usersData.users.length : 0;
            const bookingCount = bookingsData.success ? bookingsData.bookings.length : 0;
            const revenue = bookingsData.success
                ? bookingsData.bookings.reduce((sum, b) => sum + (parseInt(b.grand_total) || 0), 0)
                : 0;

            document.getElementById('stat-movies').textContent = movieCount;
            document.getElementById('stat-users').textContent = userCount;
            document.getElementById('stat-bookings').textContent = bookingCount;
            document.getElementById('stat-revenue').textContent = '₹' + revenue.toLocaleString('en-IN');

            // Recent bookings (top 5)
            if (bookingsData.success && bookingsData.bookings.length > 0) {
                const recent = bookingsData.bookings.slice(0, 5);
                document.getElementById('recent-bookings-body').innerHTML = recent.map(b => `
                    <tr>
                        <td><code style="color:var(--admin-accent);font-size:0.8rem;">${esc(b.booking_id)}</code></td>
                        <td>${esc(b.movie_title)}</td>
                        <td>${esc(b.user_email)}</td>
                        <td>${esc(b.seats)}</td>
                        <td><strong>₹${parseInt(b.grand_total || 0).toLocaleString('en-IN')}</strong></td>
                        <td style="color:var(--admin-text-muted);font-size:0.82rem;">${esc(b.booking_date)}</td>
                    </tr>
                `).join('');
            } else {
                document.getElementById('recent-bookings-body').innerHTML =
                    '<tr><td colspan="6" class="table-empty"><i class="fas fa-inbox"></i>No bookings yet</td></tr>';
            }
        } catch (err) {
            console.error('Stats load error:', err);
        }
    }

    // ===== MOVIES =====
    async function loadMovies() {
        try {
            const resp = await fetch('api/admin_movies.php');
            const data = await resp.json();
            if (data.success) {
                allMovies = data.movies;
                renderMoviesTable(allMovies);
            }
        } catch (err) {
            console.error('Load movies error:', err);
        }
    }

    function renderMoviesTable(movies) {
        const body = document.getElementById('movies-table-body');
        if (movies.length === 0) {
            body.innerHTML = '<tr><td colspan="7" class="table-empty"><i class="fas fa-film"></i>No movies found. Click "Add Movie" to get started.</td></tr>';
            return;
        }
        body.innerHTML = movies.map(m => `
            <tr>
                <td><code style="color:var(--admin-text-muted);font-size:0.78rem;">${esc(m.id)}</code></td>
                <td>
                    <div class="table-movie-cell">
                        <img src="${esc(m.poster_url)}" alt="" class="table-movie-poster" onerror="this.src='https://via.placeholder.com/40x56/1a1a3e/555?text=N/A'">
                        <span>${esc(m.title)}</span>
                    </div>
                </td>
                <td>${esc(m.language)}</td>
                <td>${esc(m.genre)}</td>
                <td>${esc(m.rating)}</td>
                <td>${esc(m.duration)}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn-icon edit" title="Edit" onclick="window._adminEditMovie('${esc(m.id)}')"><i class="fas fa-pen"></i></button>
                        <button class="btn-icon delete" title="Delete" onclick="window._adminDeleteMovie('${esc(m.id)}','${esc(m.title)}')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Search movies
    document.getElementById('movies-search').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        const filtered = allMovies.filter(m =>
            m.title.toLowerCase().includes(q) || m.language.toLowerCase().includes(q) || m.genre.toLowerCase().includes(q)
        );
        renderMoviesTable(filtered);
    });

    // Add Movie button
    document.getElementById('add-movie-btn').addEventListener('click', () => {
        document.getElementById('movie-modal-title').textContent = 'Add New Movie';
        document.getElementById('movie-edit-id').value = '';
        document.getElementById('movie-form').reset();
        document.getElementById('movie-modal-overlay').classList.add('active');
    });

    // Edit Movie
    window._adminEditMovie = (id) => {
        const movie = allMovies.find(m => m.id === id);
        if (!movie) return;

        document.getElementById('movie-modal-title').textContent = 'Edit Movie';
        document.getElementById('movie-edit-id').value = movie.id;
        document.getElementById('movie-title-input').value = movie.title || '';
        document.getElementById('movie-language-input').value = movie.language || '';
        document.getElementById('movie-genre-input').value = movie.genre || '';
        document.getElementById('movie-rating-input').value = movie.rating || '';
        document.getElementById('movie-duration-input').value = movie.duration || '';
        document.getElementById('movie-poster-input').value = movie.poster_url || '';
        document.getElementById('movie-backdrop-input').value = movie.backdrop_url || '';
        document.getElementById('movie-cast-input').value = movie.cast_json || '[]';
        document.getElementById('movie-modal-overlay').classList.add('active');
    };

    // Delete Movie
    window._adminDeleteMovie = (id, title) => {
        document.getElementById('delete-confirm-text').textContent = `Are you sure you want to delete "${title}"? This action cannot be undone.`;
        pendingDeleteAction = async () => {
            try {
                const resp = await fetch('api/admin_movies.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await resp.json();
                if (data.success) {
                    showToast('Movie deleted successfully.', 'success');
                    loadMovies();
                    loadDashboardStats();
                } else {
                    showToast(data.message || 'Delete failed.', 'error');
                }
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
        };
        document.getElementById('delete-modal-overlay').classList.add('active');
    };

    // Save Movie (Add/Edit)
    document.getElementById('movie-modal-save').addEventListener('click', async () => {
        const editId = document.getElementById('movie-edit-id').value;
        const payload = {
            title: document.getElementById('movie-title-input').value.trim(),
            language: document.getElementById('movie-language-input').value,
            genre: document.getElementById('movie-genre-input').value,
            rating: document.getElementById('movie-rating-input').value.trim(),
            duration: document.getElementById('movie-duration-input').value.trim(),
            poster_url: document.getElementById('movie-poster-input').value.trim(),
            backdrop_url: document.getElementById('movie-backdrop-input').value.trim(),
            cast_json: document.getElementById('movie-cast-input').value.trim() || '[]'
        };

        if (!payload.title) {
            showToast('Movie title is required.', 'error');
            return;
        }

        try {
            let method = 'POST';
            if (editId) {
                payload.id = editId;
                method = 'PUT';
            }

            const resp = await fetch('api/admin_movies.php', {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();

            if (data.success) {
                showToast(editId ? 'Movie updated!' : 'Movie added!', 'success');
                document.getElementById('movie-modal-overlay').classList.remove('active');
                loadMovies();
                loadDashboardStats();
            } else {
                showToast(data.message || 'Save failed.', 'error');
            }
        } catch (err) {
            showToast('Error: ' + err.message, 'error');
        }
    });

    // Movie modal close/cancel
    document.getElementById('movie-modal-close').addEventListener('click', () => {
        document.getElementById('movie-modal-overlay').classList.remove('active');
    });
    document.getElementById('movie-modal-cancel').addEventListener('click', () => {
        document.getElementById('movie-modal-overlay').classList.remove('active');
    });

    // ===== USERS =====
    async function loadUsers() {
        try {
            const resp = await fetch('api/admin_users.php');
            const data = await resp.json();
            if (data.success) {
                allUsers = data.users;
                renderUsersTable(allUsers);
            }
        } catch (err) {
            console.error('Load users error:', err);
        }
    }

    function renderUsersTable(users) {
        const body = document.getElementById('users-table-body');
        if (users.length === 0) {
            body.innerHTML = '<tr><td colspan="5" class="table-empty"><i class="fas fa-users"></i>No users found.</td></tr>';
            return;
        }
        body.innerHTML = users.map(u => {
            const isAdmin = u.is_admin == 1 || u.email === 'admin@cineticket.com';
            return `
            <tr>
                <td><code style="color:var(--admin-text-muted);font-size:0.78rem;">${u.id}</code></td>
                <td>${esc(u.name)}</td>
                <td>${esc(u.email)}</td>
                <td>${isAdmin ? '<span class="badge-admin">Admin</span>' : '<span class="badge-user">User</span>'}</td>
                <td>
                    <div class="table-actions">
                        ${isAdmin ? '<span style="color:var(--admin-text-muted);font-size:0.78rem;">—</span>' :
                        `<button class="btn-icon delete" title="Delete" onclick="window._adminDeleteUser(${u.id},'${esc(u.name)}')"><i class="fas fa-trash"></i></button>`}
                    </div>
                </td>
            </tr>
        `;
        }).join('');
    }

    document.getElementById('users-search').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        const filtered = allUsers.filter(u =>
            u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q)
        );
        renderUsersTable(filtered);
    });

    window._adminDeleteUser = (id, name) => {
        document.getElementById('delete-confirm-text').textContent = `Are you sure you want to delete user "${name}"? This action cannot be undone.`;
        pendingDeleteAction = async () => {
            try {
                const resp = await fetch('api/admin_users.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await resp.json();
                if (data.success) {
                    showToast('User deleted.', 'success');
                    loadUsers();
                    loadDashboardStats();
                } else {
                    showToast(data.message || 'Delete failed.', 'error');
                }
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
        };
        document.getElementById('delete-modal-overlay').classList.add('active');
    };

    // ===== BOOKINGS =====
    async function loadBookings() {
        try {
            const resp = await fetch('api/admin_bookings.php');
            const data = await resp.json();
            if (data.success) {
                allBookings = data.bookings;
                renderBookingsTable(allBookings);
            }
        } catch (err) {
            console.error('Load bookings error:', err);
        }
    }

    function renderBookingsTable(bookings) {
        const body = document.getElementById('bookings-table-body');
        if (bookings.length === 0) {
            body.innerHTML = '<tr><td colspan="10" class="table-empty"><i class="fas fa-ticket-alt"></i>No bookings found.</td></tr>';
            return;
        }
        body.innerHTML = bookings.map(b => `
            <tr>
                <td>${b.id}</td>
                <td><code style="color:var(--admin-accent);font-size:0.78rem;">${esc(b.booking_id)}</code></td>
                <td>${esc(b.movie_title)}</td>
                <td>${esc(b.user_email)}</td>
                <td>${esc(b.theatre || '—')}</td>
                <td>${esc(b.seats)}</td>
                <td>${esc(b.showtime || '—')}</td>
                <td><strong>₹${parseInt(b.grand_total || 0).toLocaleString('en-IN')}</strong></td>
                <td style="color:var(--admin-text-muted);font-size:0.82rem;">${esc(b.booking_date)}</td>
                <td>
                    <div class="table-actions">
                        <button class="btn-icon delete" title="Delete" onclick="window._adminDeleteBooking(${b.id},'${esc(b.booking_id)}')"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    document.getElementById('bookings-search').addEventListener('input', (e) => {
        const q = e.target.value.toLowerCase();
        const filtered = allBookings.filter(b =>
            (b.booking_id || '').toLowerCase().includes(q) ||
            (b.movie_title || '').toLowerCase().includes(q) ||
            (b.user_email || '').toLowerCase().includes(q)
        );
        renderBookingsTable(filtered);
    });

    window._adminDeleteBooking = (id, bookingId) => {
        document.getElementById('delete-confirm-text').textContent = `Are you sure you want to delete booking "${bookingId}"? This action cannot be undone.`;
        pendingDeleteAction = async () => {
            try {
                const resp = await fetch('api/admin_bookings.php', {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const data = await resp.json();
                if (data.success) {
                    showToast('Booking deleted.', 'success');
                    loadBookings();
                    loadDashboardStats();
                } else {
                    showToast(data.message || 'Delete failed.', 'error');
                }
            } catch (err) { showToast('Error: ' + err.message, 'error'); }
        };
        document.getElementById('delete-modal-overlay').classList.add('active');
    };

    // ===== DELETE CONFIRM MODAL =====
    document.getElementById('delete-modal-confirm').addEventListener('click', async () => {
        if (pendingDeleteAction) {
            await pendingDeleteAction();
            pendingDeleteAction = null;
        }
        document.getElementById('delete-modal-overlay').classList.remove('active');
    });

    document.getElementById('delete-modal-close').addEventListener('click', () => {
        pendingDeleteAction = null;
        document.getElementById('delete-modal-overlay').classList.remove('active');
    });
    document.getElementById('delete-modal-cancel').addEventListener('click', () => {
        pendingDeleteAction = null;
        document.getElementById('delete-modal-overlay').classList.remove('active');
    });

    // ===== EXPORT =====
    document.getElementById('export-excel-btn').addEventListener('click', () => {
        window.open('api/export_excel.php', '_blank');
        showToast('Downloading Excel file...', 'success');
    });

    document.getElementById('export-users-csv-btn').addEventListener('click', () => {
        generateCSV(allUsers, ['id', 'name', 'email'], 'CineTicket_Users.csv');
        showToast('Users CSV downloaded.', 'success');
    });

    document.getElementById('export-bookings-csv-btn').addEventListener('click', () => {
        generateCSV(allBookings,
            ['id', 'booking_id', 'movie_title', 'user_email', 'theatre', 'seats', 'showtime', 'grand_total', 'booking_date'],
            'CineTicket_Bookings.csv'
        );
        showToast('Bookings CSV downloaded.', 'success');
    });

    function generateCSV(data, columns, filename) {
        if (!data || data.length === 0) {
            showToast('No data to export.', 'error');
            return;
        }

        let csv = columns.join(',') + '\n';
        data.forEach(row => {
            csv += columns.map(col => {
                let val = (row[col] !== undefined && row[col] !== null) ? String(row[col]) : '';
                // Escape quotes and wrap in quotes
                val = val.replace(/"/g, '""');
                return `"${val}"`;
            }).join(',') + '\n';
        });

        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    // ===== HELPERS =====
    function esc(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = String(str);
        return div.innerHTML;
    }

    // ===== INIT =====
    checkSession();

})();
