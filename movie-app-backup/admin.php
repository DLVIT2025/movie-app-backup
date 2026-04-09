<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTicket — Admin Panel</title>
    <meta name="description" content="CineTicket Admin Dashboard — Manage movies, users, bookings, and export data.">
    <link rel="stylesheet" href="css/admin.css?v=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <!-- Toast Container -->
    <div class="admin-toast-container" id="admin-toast-container"></div>

    <!-- =============== LOGIN PAGE =============== -->
    <div class="admin-login-page" id="admin-login-page">
        <div class="admin-login-card">
            <div class="login-icon"><i class="fas fa-shield-alt"></i></div>
            <h1>Admin Portal</h1>
            <p class="subtitle">CineTicket Management Console</p>

            <div class="login-error" id="login-error">
                <i class="fas fa-exclamation-circle"></i> <span id="login-error-text"></span>
            </div>

            <form id="admin-login-form" autocomplete="off">
                <div class="admin-form-group">
                    <input type="email" id="admin-email" placeholder="Admin Email" required>
                    <i class="fas fa-envelope field-icon"></i>
                </div>
                <div class="admin-form-group">
                    <input type="password" id="admin-password" placeholder="Password" required>
                    <i class="fas fa-lock field-icon"></i>
                </div>
                <button type="submit" class="admin-btn" id="admin-login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
                </button>
            </form>

            <a href="index.php" class="back-to-site"><i class="fas fa-arrow-left"></i> Back to CineTicket</a>
        </div>
    </div>

    <!-- =============== DASHBOARD LAYOUT =============== -->
    <div class="admin-layout" id="admin-layout">

        <!-- Sidebar -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-brand">
                <div class="brand-icon"><i class="fas fa-ticket-alt"></i></div>
                <div>
                    <h2>CineTicket</h2>
                    <span>Admin Panel</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <p class="sidebar-nav-label">Main</p>
                <button class="sidebar-link active" data-page="dashboard-page">
                    <i class="fas fa-chart-pie"></i> Dashboard
                </button>
                <button class="sidebar-link" data-page="movies-page">
                    <i class="fas fa-film"></i> Movies
                </button>
                <button class="sidebar-link" data-page="users-page">
                    <i class="fas fa-users"></i> Users
                </button>
                <button class="sidebar-link" data-page="bookings-page">
                    <i class="fas fa-ticket-alt"></i> Bookings
                </button>

                <p class="sidebar-nav-label" style="margin-top: 1rem;">Tools</p>
                <button class="sidebar-link" data-page="export-page">
                    <i class="fas fa-download"></i> Export Data
                </button>
            </nav>

            <div class="sidebar-footer">
                <div class="admin-user-info">
                    <div class="admin-avatar" id="sidebar-avatar">A</div>
                    <div>
                        <div class="admin-name" id="sidebar-name">Admin</div>
                        <div class="admin-email" id="sidebar-email">admin@cineticket.com</div>
                    </div>
                </div>
                <button class="admin-logout-btn" id="admin-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <div style="display:flex;align-items:center;gap:12px;">
                    <button class="mobile-menu-btn" id="mobile-menu-btn"><i class="fas fa-bars"></i></button>
                    <h1 id="admin-page-title">Dashboard</h1>
                </div>
                <div class="admin-header-actions">
                    <a href="index.php" class="header-btn" title="View Site">
                        <i class="fas fa-external-link-alt"></i> View Site
                    </a>
                </div>
            </header>

            <div class="admin-content">

                <!-- ===== DASHBOARD PAGE ===== -->
                <div class="admin-page active" id="dashboard-page">
                    <div class="stats-grid">
                        <div class="stat-card movies-card">
                            <div class="stat-icon"><i class="fas fa-film"></i></div>
                            <div class="stat-value" id="stat-movies">0</div>
                            <div class="stat-label">Total Movies</div>
                        </div>
                        <div class="stat-card users-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-value" id="stat-users">0</div>
                            <div class="stat-label">Registered Users</div>
                        </div>
                        <div class="stat-card bookings-card">
                            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                            <div class="stat-value" id="stat-bookings">0</div>
                            <div class="stat-label">Total Bookings</div>
                        </div>
                        <div class="stat-card revenue-card">
                            <div class="stat-icon"><i class="fas fa-rupee-sign"></i></div>
                            <div class="stat-value" id="stat-revenue">₹0</div>
                            <div class="stat-label">Total Revenue</div>
                        </div>
                    </div>

                    <!-- Recent Bookings Preview -->
                    <div class="admin-table-wrapper">
                        <div class="table-header">
                            <h3><i class="fas fa-history"></i> Recent Bookings</h3>
                        </div>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Movie</th>
                                        <th>User</th>
                                        <th>Seats</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-bookings-body">
                                    <tr><td colspan="6" class="table-empty"><i class="fas fa-inbox"></i>Loading...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== MOVIES PAGE ===== -->
                <div class="admin-page" id="movies-page">
                    <div class="admin-table-wrapper">
                        <div class="table-header">
                            <h3><i class="fas fa-film"></i> All Movies</h3>
                            <div class="table-header-actions">
                                <div class="table-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="table-search" id="movies-search" placeholder="Search movies...">
                                </div>
                                <button class="btn-add" id="add-movie-btn"><i class="fas fa-plus"></i> Add Movie</button>
                            </div>
                        </div>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Movie</th>
                                        <th>Language</th>
                                        <th>Genre</th>
                                        <th>Rating</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="movies-table-body">
                                    <tr><td colspan="7" class="table-empty"><i class="fas fa-film"></i>Loading movies...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== USERS PAGE ===== -->
                <div class="admin-page" id="users-page">
                    <div class="admin-table-wrapper">
                        <div class="table-header">
                            <h3><i class="fas fa-users"></i> Registered Users</h3>
                            <div class="table-header-actions">
                                <div class="table-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="table-search" id="users-search" placeholder="Search users...">
                                </div>
                            </div>
                        </div>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="users-table-body">
                                    <tr><td colspan="5" class="table-empty"><i class="fas fa-users"></i>Loading users...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== BOOKINGS PAGE ===== -->
                <div class="admin-page" id="bookings-page">
                    <div class="admin-table-wrapper">
                        <div class="table-header">
                            <h3><i class="fas fa-ticket-alt"></i> All Bookings</h3>
                            <div class="table-header-actions">
                                <div class="table-search-wrap">
                                    <i class="fas fa-search"></i>
                                    <input type="text" class="table-search" id="bookings-search" placeholder="Search bookings...">
                                </div>
                            </div>
                        </div>
                        <div class="table-scroll">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Booking ID</th>
                                        <th>Movie</th>
                                        <th>User Email</th>
                                        <th>Theatre</th>
                                        <th>Seats</th>
                                        <th>Showtime</th>
                                        <th>Total</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="bookings-table-body">
                                    <tr><td colspan="10" class="table-empty"><i class="fas fa-ticket-alt"></i>Loading bookings...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ===== EXPORT PAGE ===== -->
                <div class="admin-page" id="export-page">
                    <div class="export-grid">
                        <div class="export-card excel">
                            <div class="export-icon"><i class="fas fa-file-excel"></i></div>
                            <h3>Full Database Export</h3>
                            <p>Download a multi-sheet Excel file containing both Users and Bookings data from the MySQL database.</p>
                            <button class="export-btn green" id="export-excel-btn">
                                <i class="fas fa-download"></i> Download Excel (.xls)
                            </button>
                        </div>
                        <div class="export-card users-exp">
                            <div class="export-icon"><i class="fas fa-users"></i></div>
                            <h3>Users CSV</h3>
                            <p>Export all registered user data (ID, Name, Email) as a lightweight CSV file.</p>
                            <button class="export-btn blue" id="export-users-csv-btn">
                                <i class="fas fa-download"></i> Download Users CSV
                            </button>
                        </div>
                        <div class="export-card bookings-exp">
                            <div class="export-icon"><i class="fas fa-ticket-alt"></i></div>
                            <h3>Bookings CSV</h3>
                            <p>Export all ticket booking records (Movie, Seats, Price, Dates) as a CSV file.</p>
                            <button class="export-btn orange" id="export-bookings-csv-btn">
                                <i class="fas fa-download"></i> Download Bookings CSV
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ===== MOVIE MODAL ===== -->
    <div class="admin-modal-overlay" id="movie-modal-overlay">
        <div class="admin-modal">
            <div class="admin-modal-header">
                <h3 id="movie-modal-title">Add New Movie</h3>
                <button class="modal-close-btn" id="movie-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="admin-modal-body">
                <form id="movie-form">
                    <input type="hidden" id="movie-edit-id">
                    <div class="modal-field">
                        <label>Movie Title *</label>
                        <input type="text" id="movie-title-input" placeholder="e.g. Inception" required>
                    </div>
                    <div class="modal-field-row">
                        <div class="modal-field">
                            <label>Language</label>
                            <select id="movie-language-input">
                                <option value="">Select</option>
                                <option value="Tamil">Tamil</option>
                                <option value="English">English</option>
                                <option value="Hindi">Hindi</option>
                                <option value="Telugu">Telugu</option>
                                <option value="Malayalam">Malayalam</option>
                                <option value="Kannada">Kannada</option>
                            </select>
                        </div>
                        <div class="modal-field">
                            <label>Genre</label>
                            <select id="movie-genre-input">
                                <option value="">Select</option>
                                <option value="Action">Action</option>
                                <option value="Drama">Drama</option>
                                <option value="Sci-Fi">Sci-Fi</option>
                                <option value="Comedy">Comedy</option>
                                <option value="Horror">Horror</option>
                                <option value="Thriller">Thriller</option>
                                <option value="Romance">Romance</option>
                                <option value="Adventure">Adventure</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-field-row">
                        <div class="modal-field">
                            <label>Rating (e.g. 8.5)</label>
                            <input type="text" id="movie-rating-input" placeholder="8.5">
                        </div>
                        <div class="modal-field">
                            <label>Duration (e.g. 2h 30m)</label>
                            <input type="text" id="movie-duration-input" placeholder="2h 30m">
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>Poster URL</label>
                        <input type="url" id="movie-poster-input" placeholder="https://image.tmdb.org/t/p/w500/...">
                    </div>
                    <div class="modal-field">
                        <label>Backdrop URL</label>
                        <input type="url" id="movie-backdrop-input" placeholder="https://image.tmdb.org/t/p/w1280/...">
                    </div>
                    <div class="modal-field">
                        <label>Cast (JSON Array)</label>
                        <textarea id="movie-cast-input" placeholder='[{"name":"Actor Name","img":"https://..."}]'></textarea>
                    </div>
                </form>
            </div>
            <div class="admin-modal-footer">
                <button class="btn-cancel" id="movie-modal-cancel">Cancel</button>
                <button class="btn-save" id="movie-modal-save"><i class="fas fa-save"></i> Save Movie</button>
            </div>
        </div>
    </div>

    <!-- ===== DELETE CONFIRM MODAL ===== -->
    <div class="admin-modal-overlay" id="delete-modal-overlay">
        <div class="admin-modal" style="max-width:420px;">
            <div class="admin-modal-header">
                <h3><i class="fas fa-exclamation-triangle" style="color:var(--admin-danger);"></i> Confirm Delete</h3>
                <button class="modal-close-btn" id="delete-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="admin-modal-body">
                <p id="delete-confirm-text" style="line-height:1.6;">Are you sure you want to delete this item? This action cannot be undone.</p>
            </div>
            <div class="admin-modal-footer">
                <button class="btn-cancel" id="delete-modal-cancel">Cancel</button>
                <button class="btn-save" id="delete-modal-confirm" style="background:linear-gradient(135deg, var(--admin-danger), #d50000);">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script src="js/admin-panel.js?v=1"></script>
</body>
</html>
