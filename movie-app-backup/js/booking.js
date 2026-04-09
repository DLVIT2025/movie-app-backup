import { navigateTo, showToast } from './ui.js';
import { getCurrentUser } from './auth.js';
import { generateQR } from './qrcode.js';

let activeBookingData = null;

// Retrieves previous bookings from localStorage for current user
const getBookings = () => JSON.parse(localStorage.getItem('ct_bookings')) || [];
const saveBookings = (bookings) => localStorage.setItem('ct_bookings', JSON.stringify(bookings));

// Get selected theatre from UI
const getSelectedTheatre = () => {
    const activeChip = document.querySelector('.theatre-chip.active');
    return activeChip ? activeChip.dataset.theatre : 'PVR Cinemas';
};

// Get selected showtime from UI
const getSelectedShowtime = () => {
    const activeChip = document.querySelector('.showtime-chip:not(.theatre-chip):not(.member-chip).active');
    return activeChip ? activeChip.dataset.time : '6:00 PM';
};

// Called from payment modal confirm
export const finalizeBooking = async (data) => {
    try {
        console.log('finalizeBooking called with data:', JSON.stringify(data, null, 2));
        
        if (!data || !data.movie) {
            showToast('Booking data is incomplete. Please try again from the movie selection.', 'error');
            console.error('finalizeBooking: data.movie is missing. Full data:', data);
            return;
        }
        
        activeBookingData = data;
        
        // Generate Booking ID
        const bookingId = 'BK' + Math.random().toString(36).substr(2, 6).toUpperCase();
        const user = getCurrentUser();
        if (!user) {
            showToast('Session expired. Please log in again to book your ticket.', 'error');
            navigateTo('auth-section');
            return;
        }

        const showtime = data.showtime || getSelectedShowtime();
        const theatre = data.theatre || getSelectedTheatre();

        // Compile final ticket object
        const finalTicket = {
            bookingId,
            userId: user.email,
            userName: user.name,
            movieTitle: data.movie.title,
            movieId: data.movie.id,
            showtime,
            theatre,
            seats: data.seats.map(s => s.id),
            seatTypes: [...new Set(data.seats.map(s => s.type))],
            snacks: data.snacks,
            date: new Date().toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' }),
            baseTotal: data.baseTotal,
            snacksTotal: data.snacksTotal,
            grandTotal: data.grandTotal
        };

        // Save booked seats globally to prevent double booking
        const seatStoreKey = `ct_booked_${data.movie.id}`;
        const globallyBooked = JSON.parse(localStorage.getItem(seatStoreKey)) || [];
        localStorage.setItem(seatStoreKey, JSON.stringify([...globallyBooked, ...finalTicket.seats]));

        // Save booked tickets locally as fallback/cache as requested
        const localList = getBookings();
        localList.push(finalTicket);
        saveBookings(localList);

        // Save to Database
        const response = await fetch('api/book_ticket.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(finalTicket)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Render Ticket View
            renderTicketUI(finalTicket);
            navigateTo('ticket-section');
            showToast('Booking Successful! Enjoy your movie.', 'success');
        } else {
            showToast(result.message || 'Failed to securely save booking.', 'error');
        }
    } catch (error) {
        console.error('Booking save error:', error);
        showToast('Booking Error: ' + error.message, 'error');
    }
};

const renderTicketUI = (ticket) => {
    document.getElementById('ticket-movie-title').textContent = ticket.movieTitle;
    document.getElementById('ticket-date').textContent = ticket.date + (ticket.showtime ? ` at ${ticket.showtime}` : '') + (ticket.theatre ? ` | ${ticket.theatre}` : '');
    document.getElementById('ticket-user-name').textContent = ticket.userName;
    
    // Safety check if the element exists in case of backward compat
    const emailEl = document.getElementById('ticket-user-email');
    if (emailEl) emailEl.textContent = ticket.userId || 'N/A';
    
    document.getElementById('ticket-id').textContent = ticket.bookingId;
    
    document.getElementById('ticket-seat-count').textContent = ticket.seats.length;
    document.getElementById('ticket-seats').textContent = ticket.seats.join(', ');
    document.getElementById('ticket-seat-types').textContent = ticket.seatTypes.join(', ');

    // Snacks
    const snacksContainer = document.getElementById('ticket-snacks-container');
    if (ticket.snacks && ticket.snacks.length > 0) {
        snacksContainer.classList.remove('hidden');
        document.getElementById('ticket-snacks').innerHTML = ticket.snacks.map(s => 
            `${s.icon || ''} ${s.name} x${s.quantity} — ₹${s.price * s.quantity}`
        ).join('<br>');
    } else {
        snacksContainer.classList.add('hidden');
    }

    document.getElementById('ticket-total-price').textContent = ticket.grandTotal;

    // Generate QR code: Booking ID + Movie Name + Seats + Date + Time
    generateQR(`${ticket.bookingId}|${ticket.movieTitle}|${ticket.seats.join(',')}|${ticket.date}|${ticket.showtime}`);
};

// Keep backward compat alias
export const openBookingSummary = finalizeBooking;

// Expose rendering function for "My Tickets" section
export const renderMyTickets = async () => {
    const user = getCurrentUser();
    if (!user) return;

    const grid = document.getElementById('my-tickets-grid');
    grid.innerHTML = '<p class="text-muted">Loading your tickets...</p>';

    try {
        const response = await fetch(`api/get_bookings.php?email=${encodeURIComponent(user.email)}`);
        const result = await response.json();
        
        if (result.success) {
            const bookings = result.bookings;

            if (bookings.length === 0) {
                grid.innerHTML = '<p class="text-muted">You have no bookings yet.</p>';
                return;
            }

            // Save retrieved back into legacy format just so viewTicket() works seamlessly
            saveBookings(bookings);

            grid.innerHTML = bookings.map(b => `
                <div class="mini-ticket glass-panel cursor-pointer" onclick="viewTicket('${b.bookingId}')">
                    <h4>${b.movieTitle}</h4>
                    <p class="text-sm text-muted">📅 ${b.date}${b.showtime ? ` at ${b.showtime}` : ''}</p>
                    <p class="text-sm text-muted mb-2"><i class="fas fa-building"></i> ${b.theatre || 'PVR Cinemas'}</p>
                    <p class="mt-2"><b>Seats:</b> ${b.seats.join(', ')} <span class="badge" style="margin-left:0.5rem;">${b.seatTypes && b.seatTypes.length > 0 ? b.seatTypes.join(', ') : ''}</span></p>
                    ${b.snacks && b.snacks.length > 0 ? `<p class="text-sm text-muted mt-1">🍿 ${b.snacks.map(s => s.name + ' x' + s.quantity).join(', ')}</p>` : ''}
                    <h4 class="text-accent mt-2">₹${b.grandTotal}</h4>
                </div>
            `).join('');
        } else {
            grid.innerHTML = '<p class="text-danger">Failed to load tickets.</p>';
        }
    } catch (error) {
        console.error('Fetch bookings error:', error);
        grid.innerHTML = '<p class="text-danger">Error connecting to server.</p>';
    }
};

window.viewTicket = (bookingId) => {
    const booking = getBookings().find(b => b.bookingId === bookingId);
    if(booking) {
        renderTicketUI(booking);
        navigateTo('ticket-section');
    }
};

// Listen for entering "my tickets" section + payment confirmation
document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('.nav-link[data-target="my-tickets-section"]').addEventListener('click', () => {
        renderMyTickets();
    });

    // Confirm payment -> trigger booking (handled here directly, not via dynamic import)
    // Clone the button to remove ANY old event listeners from cached scripts
    const oldBtn = document.getElementById('confirm-payment-btn');
    if (oldBtn) {
        const confirmPayBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(confirmPayBtn, oldBtn);
        confirmPayBtn.addEventListener('click', () => {
            document.getElementById('payment-modal').classList.add('hidden');
            if (window._pendingBookingData) {
                const pendingData = window._pendingBookingData;
                window._pendingBookingData = null;
                finalizeBooking(pendingData);
            }
        });
    }
});

// HTML2Canvas Ticket Download Exporter
window.downloadTicketImage = () => {
    const ticketEl = document.getElementById('printable-ticket');
    if (!ticketEl) return;
    
    // Check if html2canvas is loaded physically via index.html CDN
    if (typeof html2canvas !== 'undefined') {
        // Temporarily reset styles that might break canvas
        const originalTransform = ticketEl.style.transform;
        ticketEl.style.transform = 'none';

        html2canvas(ticketEl, {
            backgroundColor: '#1E1E2C', // Match dark theme
            scale: 2 // High Resolution
        }).then(canvas => {
            const link = document.createElement('a');
            const bookingId = document.getElementById('ticket-id').textContent || 'Booking';
            link.download = `CineTicket_${bookingId}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            // Restore transform
            ticketEl.style.transform = originalTransform;
        }).catch(err => {
            console.error('Error generating ticket image:', err);
            alert('Failed to generate image. Please try printing.');
        });
    } else {
        alert("Image generation library is still loading or blocked. Please try Print instead.");
    }
};
