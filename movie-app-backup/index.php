<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CineTicket - Premium Movie Booking</title>
    <link rel="stylesheet" href="css/styles.css?v=10">
    <link rel="stylesheet" href="css/animations.css?v=10">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- QR Code Library CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- HTML2Canvas CDN for Ticket Download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="dark-mode">

    <!-- Toast Notifications Container -->
    <div id="toast-container"></div>

    <!-- Navigation -->
    <nav class="navbar glass-panel">
        <div class="nav-brand">
            <i class="fas fa-ticket-alt text-accent"></i> CineTicket
        </div>
        <div class="nav-links">
            <a href="#" class="nav-link active" data-target="home-section">Home</a>
            <a href="#" class="nav-link" data-target="movies-section">Movies</a>
            <a href="#" class="nav-link auth-req hidden" data-target="social-section">Community</a>
            <a href="#" class="nav-link auth-req hidden" data-target="my-tickets-section">My Tickets</a>
            <a href="#" class="nav-link auth-req hidden" data-target="admin-section">Admin</a>
        </div>
        <!-- City Selector -->
        <div class="city-selector" id="city-selector">
            <button class="city-btn" id="city-btn" title="Select City">
                <i class="fas fa-map-marker-alt"></i>
                <span id="selected-city">Bengaluru</span>
                <i class="fas fa-chevron-down" style="font-size:0.7rem"></i>
            </button>
            <div class="city-dropdown hidden" id="city-dropdown">
                <div class="city-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="city-search" placeholder="Search for your city">
                </div>
                <p class="city-section-label">Popular Cities</p>
                <div class="city-grid" id="city-grid">
                    <div class="city-item" data-city="Mumbai"><span class="city-icon">🏙️</span><span>Mumbai</span></div>
                    <div class="city-item" data-city="Kochi"><span class="city-icon">🌴</span><span>Kochi</span></div>
                    <div class="city-item" data-city="Delhi-NCR"><span class="city-icon">🏛️</span><span>Delhi-NCR</span></div>
                    <div class="city-item active" data-city="Bengaluru"><span class="city-icon">🏗️</span><span>Bengaluru</span></div>
                    <div class="city-item" data-city="Hyderabad"><span class="city-icon">🕌</span><span>Hyderabad</span></div>
                    <div class="city-item" data-city="Chandigarh"><span class="city-icon">🌳</span><span>Chandigarh</span></div>
                    <div class="city-item" data-city="Ahmedabad"><span class="city-icon">🏰</span><span>Ahmedabad</span></div>
                    <div class="city-item" data-city="Pune"><span class="city-icon">⛰️</span><span>Pune</span></div>
                    <div class="city-item" data-city="Chennai"><span class="city-icon">🛕</span><span>Chennai</span></div>
                    <div class="city-item" data-city="Kolkata"><span class="city-icon">🌉</span><span>Kolkata</span></div>
                </div>
            </div>
        </div>
        <div class="nav-actions">
            <button id="voice-btn" class="icon-btn" title="Voice Command">
                <i class="fas fa-microphone"></i>
            </button>
            <button id="theme-toggle" class="icon-btn" title="Toggle Theme">
                <i class="fas fa-sun"></i>
            </button>
            <button id="login-nav-btn" class="btn btn-outline" id="login-nav-btn">Login</button>
            <div id="user-profile-nav" class="user-profile hidden">
                <div class="avatar" id="nav-avatar">U</div>
                <span id="nav-username">User</span>
                <button id="logout-btn" class="icon-btn text-danger" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
            </div>
        </div>
    </nav>

    <main id="main-content">
        <!-- Auth Section -->
        <section id="auth-section" class="page-section hidden">
            <div class="auth-container">
                <div class="auth-card glass-panel animate-fade-in">
                    <!-- Login Form -->
                    <div id="login-form-wrapper">
                        <h2>Welcome Back</h2>
                        <p class="text-muted">Sign in to book your favorite movies</p>
                        <form id="login-form">
                            <div class="form-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="login-email" placeholder="Email Address" required>
                            </div>
                            <div class="form-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="login-password" placeholder="Password" required>
                                <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
                            </div>
                            <div class="auth-options">
                                <label><input type="checkbox"> Remember me</label>
                                <a href="#" id="forgot-password-link" class="text-accent">Forgot Password?</a>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Sign In</button>
                        </form>
                        <p class="auth-switch">Don't have an account? <a href="#" id="go-to-signup" class="text-accent">Sign up now</a></p>
                    </div>

                    <!-- Signup Form -->
                    <div id="signup-form-wrapper" class="hidden">
                        <h2>Create Account</h2>
                        <p class="text-muted">Join the ultimate movie community</p>
                        <form action="connect.php" method="post">
                            <div class="form-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="signup-name" name="name" placeholder="Full Name / Username" required>
                            </div>
                            <div class="form-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="signup-email" name="email" placeholder="Email Address" required>
                            </div>
                            <div class="form-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="signup-password" name="password" placeholder="Password (min 6 chars)" required minlength="6">
                                <button type="button" class="toggle-password"><i class="fas fa-eye"></i></button>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Create Account</button>
                        </form>
                        <p class="auth-switch">Already have an account? <a href="#" id="go-to-login" class="text-accent">Sign in</a></p>
                    </div>
                </div>
            </div>
        </section>

        <section id="home-section" class="page-section active">
            <div class="hero-carousel" id="hero-carousel">
                <div class="hero-slides" id="hero-slides">
                    <div class="hero-slide active" style="background-image:url('https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1920&q=80')"></div>
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1536440136628-849c177e76a1?auto=format&fit=crop&w=1920&q=80')"></div>
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1440404653325-ab127d49abc1?auto=format&fit=crop&w=1920&q=80')"></div>
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1517604931442-7e0c8ed2963c?auto=format&fit=crop&w=1920&q=80')"></div>
                    <div class="hero-slide" style="background-image:url('https://images.unsplash.com/photo-1595769816263-9b910be24d5f?auto=format&fit=crop&w=1920&q=80')"></div>
                </div>
                <div class="hero-overlay"></div>
                <div class="hero-content animate-fade-in">
                    <h1>Experience Cinema Like Never Before</h1>
                    <p>Book tickets, grab snacks, and join communities.</p>
                    <button class="btn btn-primary btn-lg" onclick="document.querySelector('[data-target=\'movies-section\']').click()">Explore Movies</button>
                </div>
                <div class="hero-dots" id="hero-dots">
                    <div class="hero-dot active" data-idx="0"></div>
                    <div class="hero-dot" data-idx="1"></div>
                    <div class="hero-dot" data-idx="2"></div>
                    <div class="hero-dot" data-idx="3"></div>
                    <div class="hero-dot" data-idx="4"></div>
                </div>
            </div>
            <script>
            // Inline carousel — no ES module dependency
            (function(){
                let cur = 0;
                const slides = document.querySelectorAll('.hero-slide');
                const dots = document.querySelectorAll('.hero-dot');
                if(!slides.length) return;
                function go(i){ slides[cur].classList.remove('active'); dots[cur].classList.remove('active'); cur=i; slides[cur].classList.add('active'); dots[cur].classList.add('active'); }
                dots.forEach(d => d.addEventListener('click', () => go(+d.dataset.idx)));
                setInterval(() => go((cur+1)%slides.length), 5000);
            })();
            </script>
            <div class="container">
                <h2 class="section-title">Trending Now</h2>
                <div class="movie-row" id="trending-movies">
                    <!-- Populated by JS -->
                </div>
            </div>
        </section>

        <!-- Movies Section -->
        <section id="movies-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <div class="movies-header">
                    <h2>All Movies</h2>
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="movie-search" placeholder="Search for movies, genres...">
                    </div>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label>Language:</label>
                        <select id="filter-lang">
                            <option value="All">All Languages</option>
                            <option value="Tamil">Tamil</option>
                            <option value="English">English</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Telugu">Telugu</option>
                            <option value="Malayalam">Malayalam</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Genre:</label>
                        <select id="filter-genre">
                            <option value="All">All Genres</option>
                            <option value="Action">Action</option>
                            <option value="Drama">Drama</option>
                            <option value="Sci-Fi">Sci-Fi</option>
                            <option value="Comedy">Comedy</option>
                            <option value="Horror">Horror</option>
                            <option value="Thriller">Thriller</option>
                            <option value="Romance">Romance</option>
                        </select>
                    </div>
                </div>

                <div class="movie-grid" id="all-movies-grid">
                    <!-- Populated by JS -->
                </div>
            </div>
        </section>

        <!-- Movie Detail Overlay -->
        <div id="movie-detail-overlay" class="movie-detail-overlay hidden">
            <div class="movie-detail-content glass-panel animate-slide-up">
                <button class="close-btn" id="close-detail"><i class="fas fa-times"></i></button>
                <div class="detail-backdrop" id="detail-backdrop">
                    <div class="detail-backdrop-overlay"></div>
                </div>
                <div class="detail-info">
                    <div class="detail-poster-wrapper">
                        <img src="" alt="Poster" id="detail-poster" class="detail-poster" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=500&h=750';">
                    </div>
                    <div class="detail-text">
                        <h1 id="detail-title">Movie Title</h1>
                        <div class="detail-meta">
                            <span class="badge" id="detail-lang">Language</span>
                            <span class="badge" id="detail-genre">Genre</span>
                            <span class="rating"><i class="fas fa-star text-accent"></i> <span id="detail-rating">8.5</span></span>
                            <span class="duration"><i class="fas fa-clock"></i> <span id="detail-duration">2h 15m</span></span>
                        </div>
                        <div class="cast-section">
                            <h3>Cast</h3>
                            <div class="cast-list" id="detail-cast">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                        <div class="theatre-selector mt-4" id="theatre-selector">
                            <label style="display:block;margin-bottom:0.5rem;font-weight:600;color:var(--text-muted);"><i class="fas fa-building"></i> Select Theatre</label>
                            <div class="showtime-chips" id="theatre-chips">
                                <button class="showtime-chip theatre-chip" data-theatre="PVR Cinemas">PVR Cinemas</button>
                                <button class="showtime-chip theatre-chip" data-theatre="INOX">INOX</button>
                                <button class="showtime-chip theatre-chip" data-theatre="Cinepolis">Cinepolis</button>
                            </div>
                        </div>
                        <div class="showtime-selector mt-4 disabled-section" id="showtime-selector">
                            <label style="display:block;margin-bottom:0.5rem;font-weight:600;color:var(--text-muted);"><i class="fas fa-clock"></i> Select Showtime</label>
                            <div class="showtime-chips" id="showtime-chips">
                                <button class="showtime-chip" data-time="10:00 AM">10:00 AM</button>
                                <button class="showtime-chip" data-time="1:30 PM">1:30 PM</button>
                                <button class="showtime-chip" data-time="5:00 PM">5:00 PM</button>
                                <button class="showtime-chip" data-time="9:00 PM">9:00 PM</button>
                            </div>
                        </div>

                        <div class="member-selector mt-4 disabled-section" id="member-selector">
                            <label style="display:block;margin-bottom:0.5rem;font-weight:600;color:var(--text-muted);"><i class="fas fa-users"></i> Select Members</label>
                            <div class="showtime-chips" id="member-chips">
                                <button class="showtime-chip member-chip" data-count="1">1</button>
                                <button class="showtime-chip member-chip" data-count="2">2</button>
                                <button class="showtime-chip member-chip" data-count="3">3</button>
                                <button class="showtime-chip member-chip" data-count="4">4</button>
                                <button class="showtime-chip member-chip" data-count="5">5</button>
                                <button class="showtime-chip member-chip" data-count="6">6</button>
                            </div>
                        </div>

                        <div class="d-flex" style="gap:1rem; flex-wrap:wrap;">
                            <button class="btn btn-primary btn-lg mt-4" id="book-ticket-btn" disabled style="opacity:0.5; cursor:not-allowed;">Book Tickets</button>
                            <button class="btn btn-outline btn-lg mt-4" id="watch-trailer-btn"><i class="fab fa-youtube text-accent"></i> Watch Trailer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seat Selection Section -->
        <section id="seats-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <div class="booking-header">
                    <button class="btn btn-outline back-btn" data-target="movies-section"><i class="fas fa-arrow-left"></i> Back</button>
                    <h2>Select Seats</h2>
                    <h3 id="seat-movie-title" class="text-accent">Movie Title</h3>
                </div>

                <div class="seat-legend">
                    <div class="legend-item"><div class="seat seat-regular"></div> Regular (₹150)</div>
                    <div class="legend-item"><div class="seat seat-premium"></div> Premium (₹250)</div>
                    <div class="legend-item"><div class="seat seat-vip"></div> VIP (₹400)</div>
                    <div class="legend-item"><div class="seat occupied"></div> Booked</div>
                    <div class="legend-item"><div class="seat selected"></div> Selected</div>
                </div>

                <div class="screen-container">
                    <div class="screen">SCREEN</div>
                </div>

                <div class="seat-map" id="seat-map">
                    <!-- Populated by JS -->
                </div>

                <div class="booking-footer glass-panel">
                    <div class="selected-seats-info">
                        <p>Selected Seats: <span id="selected-seats-count">0</span></p>
                        <p class="seat-numbers text-muted" id="selected-seats-list">None</p>
                    </div>
                    <div class="total-info">
                        <h3>Total: ₹<span id="seats-total">0</span></h3>
                        <button class="btn btn-primary" id="proceed-snacks-btn" disabled>Proceed to Snacks</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Snacks Section -->
        <section id="snacks-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <div class="booking-header">
                    <button class="btn btn-outline back-btn" data-target="seats-section"><i class="fas fa-arrow-left"></i> Back to Seats</button>
                    <h2>Grab Some Snacks 🍿</h2>
                </div>

                <div class="snacks-grid" id="snacks-grid">
                    <!-- Populated by JS -->
                </div>

                <div class="booking-footer glass-panel">
                    <div class="selected-snacks-info">
                        <p>Snacks Total: ₹<span id="snacks-total">0</span></p>
                    </div>
                    <div class="total-info">
                        <h3>Grand Total: ₹<span id="grand-total">0</span></h3>
                        <button class="btn btn-primary" id="proceed-checkout-btn">Proceed to Checkout</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Ticket / Booking Summary Section -->
        <section id="ticket-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <div class="ticket-wrapper">
                    <div class="ticket glass-panel" id="printable-ticket">
                        <div class="ticket-header">
                            <h2>CineTicket</h2>
                            <p>Booking Confirmed</p>
                        </div>
                        <div class="ticket-body">
                            <div class="ticket-info">
                                <h3><span id="ticket-movie-title">Movie Title</span></h3>
                                <p class="text-muted">Date: <span id="ticket-date">Today</span></p>
                                <hr>
                                <div class="ticket-row">
                                    <div>
                                        <p class="text-sm text-muted">Name</p>
                                        <p><strong id="ticket-user-name">John Doe</strong></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted">Email</p>
                                        <p><strong id="ticket-user-email">user@example.com</strong></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted">Booking ID</p>
                                        <p><strong id="ticket-id">BK123456</strong></p>
                                    </div>
                                </div>
                                <div class="ticket-row mt-3">
                                    <div>
                                        <p class="text-sm text-muted">Seats (<span id="ticket-seat-count">2</span>)</p>
                                        <p><strong id="ticket-seats">A1, A2</strong></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-muted">Seat Types</p>
                                        <p><strong id="ticket-seat-types">Regular</strong></p>
                                    </div>
                                </div>
                                <div class="ticket-snacks mt-3" id="ticket-snacks-container">
                                    <p class="text-sm text-muted">Add-ons</p>
                                    <p><strong id="ticket-snacks">None</strong></p>
                                </div>
                                <hr>
                                <div class="ticket-total">
                                    <p>Total Amount</p>
                                    <h3>₹<span id="ticket-total-price">0</span></h3>
                                </div>
                            </div>
                            <div class="ticket-qr">
                                <div id="qrcode"></div>
                                <p class="text-sm mt-2 text-center text-muted">Scan at entry</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button class="btn btn-primary" onclick="window.downloadTicketImage()"><i class="fas fa-download"></i> Download Ticket</button>
                        <button class="btn btn-outline mx-2" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
                        <button class="btn btn-outline" onclick="document.querySelector('[data-target=\'home-section\']').click()">Back to Home</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Social Section -->
        <section id="social-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <div class="social-header">
                    <h2>Community</h2>
                    <div class="social-tabs">
                        <button class="tab-btn active" data-tab="friends-tab">Friends</button>
                        <button class="tab-btn" data-tab="clubs-tab">Clubs</button>
                    </div>
                </div>

                <div class="tab-content active" id="friends-tab">
                    <div class="social-grid">
                        <div class="social-card glass-panel">
                            <h3>Add Friend</h3>
                            <div class="d-flex w-100">
                                <input type="text" id="add-friend-input" placeholder="Enter username...">
                                <button class="btn btn-primary" id="add-friend-btn">Add</button>
                            </div>
                            
                            <h4 class="mt-4">Requests</h4>
                            <div id="friend-requests-list" class="list-group">
                                <!-- Populated by JS -->
                                <p class="text-muted text-sm">No pending requests</p>
                            </div>
                        </div>
                        <div class="social-card glass-panel">
                            <h3>My Friends</h3>
                            <div id="friends-list" class="list-group">
                                <!-- Populated by JS -->
                                <p class="text-muted text-sm">No friends yet. Add some!</p>
                            </div>
                        </div>
                        <div class="social-card chat-card glass-panel">
                            <div class="chat-header">
                                <h3 id="chat-title">Select a friend to chat</h3>
                            </div>
                            <div class="chat-messages" id="chat-messages">
                                <!-- Populated by JS -->
                            </div>
                            <div class="chat-input-area">
                                <input type="text" id="chat-input" placeholder="Type a message..." disabled>
                                <button class="icon-btn text-accent" id="send-msg-btn" disabled><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="clubs-tab">
                    <div class="social-grid">
                        <div class="social-card glass-panel">
                            <h3>Create Club</h3>
                            <input type="text" id="club-name-input" placeholder="Club Name" class="mb-2 w-100">
                            <input type="text" id="club-desc-input" placeholder="Description" class="mb-2 w-100">
                            <button class="btn btn-primary w-100" id="create-club-btn">Create Club</button>
                        </div>
                        <div class="social-card glass-panel">
                            <h3>Discover Clubs</h3>
                            <div id="discover-clubs-list" class="list-group">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                        <div class="social-card glass-panel">
                            <h3>My Clubs</h3>
                            <div id="my-clubs-list" class="list-group">
                                <!-- Populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- My Tickets Section -->
        <section id="my-tickets-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <h2>My Bookings</h2>
                <div class="tickets-grid" id="my-tickets-grid">
                    <!-- Populated by JS -->
                </div>
            </div>
        </section>

        <!-- Admin Section -->
        <section id="admin-section" class="page-section hidden">
            <div class="container animate-fade-in">
                <h2>Admin Panel</h2>
                <div class="glass-panel" style="padding: 2.5rem; margin-top: 1.5rem; text-align: center;">
                    <div style="width:72px;height:72px;background:linear-gradient(135deg,#e50914,#ff6b35);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:white;margin:0 auto 1.5rem;box-shadow:0 8px 32px rgba(229,9,20,0.3);">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 style="margin-bottom:0.5rem;">Management Console</h3>
                    <p class="text-muted mb-4">Access the full admin dashboard to manage movies, view users, track bookings, and export database records.</p>
                    <a href="admin.php" class="btn btn-primary btn-lg" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;">
                        <i class="fas fa-external-link-alt"></i> Open Admin Dashboard
                    </a>
                    <div style="margin-top:1.5rem;">
                        <button class="btn btn-outline" id="export-csv-btn" style="display:inline-flex;align-items:center;gap:8px;">
                            <i class="fas fa-file-excel"></i> Quick Export (CSV)
                        </button>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Voice Visualizer Overlay -->
    <div id="voice-overlay" class="voice-overlay hidden animate-fade-in">
        <div class="voice-content glass-panel">
            <button id="voice-close-btn" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--text-muted);line-height:1;" title="Close"><i class="fas fa-times"></i></button>
            <div class="mic-pulse">
                <i class="fas fa-microphone"></i>
            </div>
            <h3 id="voice-status">Listening...</h3>
            <p class="text-muted mt-2">Try saying: "Select Inception", "Book Ticket", "Close"</p>
            
            <div class="mt-4">
                <p class="text-sm text-muted mb-2">Mic not working? Type command:</p>
                <input type="text" id="manual-voice-cmd" placeholder="e.g. Select Leo" style="background: rgba(0,0,0,0.3); border: 1px solid #444; color: white; border-radius: 4px; padding: 5px 10px; width: 100%; text-align: center;">
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="payment-modal" class="voice-overlay hidden animate-fade-in">
        <div class="voice-content glass-panel" style="max-width:480px;width:100%;">
            <button id="payment-modal-close" style="position:absolute;top:1rem;right:1rem;background:none;border:none;font-size:1.4rem;cursor:pointer;color:var(--text-muted);line-height:1;" title="Close"><i class="fas fa-times"></i></button>
            <h2 style="margin-bottom:0.5rem;"><i class="fas fa-credit-card text-accent"></i> Payment</h2>
            <p class="text-muted mb-4" id="payment-summary-text">Total: ₹0</p>
            <div class="payment-tabs" style="display:flex;gap:0.5rem;margin-bottom:1.5rem;">
                <button class="pay-tab-btn active" data-tab="card">💳 Card</button>
                <button class="pay-tab-btn" data-tab="upi">📱 UPI</button>
                <button class="pay-tab-btn" data-tab="cod">🏧 Cash</button>
            </div>
            <!-- Card Tab -->
            <div id="pay-tab-card" class="pay-tab-content">
                <div class="form-group"><i class="fas fa-user"></i><input type="text" id="pay-card-name" placeholder="Cardholder Name"></div>
                <div class="form-group"><i class="fas fa-credit-card"></i><input type="text" id="pay-card-number" placeholder="Card Number (16 digits)" maxlength="19"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group"><i class="fas fa-calendar"></i><input type="text" id="pay-card-expiry" placeholder="MM/YY" maxlength="5"></div>
                    <div class="form-group"><i class="fas fa-lock"></i><input type="text" id="pay-card-cvv" placeholder="CVV" maxlength="3"></div>
                </div>
            </div>
            <!-- UPI Tab -->
            <div id="pay-tab-upi" class="pay-tab-content hidden">
                <div class="form-group"><i class="fas fa-mobile-alt"></i><input type="text" id="pay-upi-id" placeholder="yourname@upi"></div>
                <p class="text-muted text-sm mt-2">Enter your UPI ID and confirm payment.</p>
            </div>
            <!-- Cash Tab -->
            <div id="pay-tab-cod" class="pay-tab-content hidden">
                <p style="text-align:center;padding:1.5rem 0;">Pay at the cinema counter before showtime. Show your booking QR code at the entrance.</p>
            </div>
            <button class="btn btn-primary w-100 mt-4" id="confirm-payment-btn"><i class="fas fa-check-circle"></i> Confirm &amp; Pay</button>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module" src="js/data.js?v=10"></script>
    <script type="module" src="js/ui.js?v=10"></script>
    <script type="module" src="js/auth.js?v=10"></script>
    <script type="module" src="js/movies.js?v=10"></script>
    <script type="module" src="js/movieDetail.js?v=10"></script>
    <script type="module" src="js/seats.js?v=10"></script>
    <!-- snacks.js is imported by seats.js; do NOT load it twice from HTML -->
    <script type="module" src="js/booking.js?v=10"></script>
    <script type="module" src="js/qrcode.js?v=10"></script>
    <script type="module" src="js/voice.js?v=10"></script>
    <script type="module" src="js/social.js?v=10"></script>
    <script type="module" src="js/admin.js?v=10"></script>
    <script type="module" src="js/app.js?v=10"></script>
</body>
</html>
