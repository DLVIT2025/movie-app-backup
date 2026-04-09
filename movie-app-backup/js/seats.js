import { requireAuth } from './auth.js';
import { navigateTo } from './ui.js';
import { openSnacksSelection } from './snacks.js';

let currentMovie = null;
export let selectedSeats = [];
let seatPriceTotal = 0;

// Configuration
const SEAT_PRICES = {
    regular: 150,
    premium: 250,
    vip: 400
};

const ROWS = [
    { label: 'A', type: 'regular', count: 10 },
    { label: 'B', type: 'regular', count: 10 },
    { label: 'C', type: 'regular', count: 10 },
    { label: 'D', type: 'regular', count: 10 },
    { label: 'E', type: 'premium', count: 10 },
    { label: 'F', type: 'premium', count: 10 },
    { label: 'G', type: 'premium', count: 10 },
    { label: 'H', type: 'vip', count: 10 },
    { label: 'I', type: 'vip', count: 10 }
];

export const openSeatSelection = (movie) => {
    if (!requireAuth()) return;

    currentMovie = movie;
    selectedSeats = [];
    seatPriceTotal = 0;

    document.getElementById('seat-movie-title').textContent = movie.title;
    updateSeatFooter();
    
    renderSeatMap();
    navigateTo('seats-section');
};

const getBookedSeatsStr = (movieId) => {
    // Basic deterministic mock for booked seats based on movie id
    const seed = parseInt(movieId.replace(/\D/g, '')) || 1;
    let booked = [];
    for(let i=0; i<8; i++) {
        const row = ROWS[(seed + i * 3) % ROWS.length].label;
        const num = (seed * i) % 10 + 1;
        booked.push(`${row}${num}`);
    }
    // Also merge from actual localstorage bookings
    const stored = JSON.parse(localStorage.getItem(`ct_booked_${movieId}`)) || [];
    return [...booked, ...stored];
};

const renderSeatMap = () => {
    const seatMap = document.getElementById('seat-map');
    seatMap.innerHTML = '';
    
    const bookedSeats = getBookedSeatsStr(currentMovie.id);

    ROWS.forEach(row => {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'seat-row';
        
        const rowLabel = document.createElement('div');
        rowLabel.className = 'row-label';
        rowLabel.textContent = row.label;
        rowDiv.appendChild(rowLabel);

        for (let i = 1; i <= row.count; i++) {
            const seatId = `${row.label}${i}`;
            const seatDiv = document.createElement('div');
            const isBooked = bookedSeats.includes(seatId);
            
            seatDiv.className = `seat seat-${row.type} ${isBooked ? 'occupied' : ''}`;
            seatDiv.dataset.id = seatId;
            seatDiv.dataset.price = SEAT_PRICES[row.type];
            seatDiv.dataset.type = row.type;
            seatDiv.textContent = i;

            if (!isBooked) {
                seatDiv.addEventListener('click', () => toggleSeat(seatDiv));
            }

            rowDiv.appendChild(seatDiv);
        }
        
        seatMap.appendChild(rowDiv);
    });
};

const toggleSeat = (seatDiv) => {
    // If clicking an already selected seat, clear the selection
    if (seatDiv.classList.contains('selected')) {
        clearSelection();
        return;
    }

    const activeMemberChip = document.querySelector('.member-chip.active');
    const memberCount = activeMemberChip ? parseInt(activeMemberChip.dataset.count) : 1;
    
    const startId = seatDiv.dataset.id;
    const row = startId.charAt(0);
    const startNum = parseInt(startId.substring(1));

    const seatMap = document.getElementById('seat-map');
    const seatsToSelect = [];

    // Check availability for N consecutive seats
    for (let i = 0; i < memberCount; i++) {
        const targetId = `${row}${startNum + i}`;
        const targetSeat = seatMap.querySelector(`.seat[data-id="${targetId}"]`);
        
        if (!targetSeat || targetSeat.classList.contains('occupied')) {
            import('./ui.js').then(m => m.showToast(`Cannot select ${memberCount} consecutive seats from here.`, 'error'));
            return;
        }
        seatsToSelect.push(targetSeat);
    }

    // Success: clear old and select new
    clearSelection();
    
    seatsToSelect.forEach(seat => {
        seat.classList.add('selected');
        const price = parseInt(seat.dataset.price);
        selectedSeats.push({
            id: seat.dataset.id,
            price: price,
            type: seat.dataset.type
        });
        seatPriceTotal += price;
    });

    updateSeatFooter();
};

const clearSelection = () => {
    document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
    selectedSeats = [];
    seatPriceTotal = 0;
    updateSeatFooter();
};

const updateSeatFooter = () => {
    document.getElementById('selected-seats-count').textContent = selectedSeats.length;
    document.getElementById('selected-seats-list').textContent = selectedSeats.length ? selectedSeats.map(s => s.id).join(', ') : 'None';
    document.getElementById('seats-total').textContent = seatPriceTotal;

    const proceedBtn = document.getElementById('proceed-snacks-btn');
    if (selectedSeats.length > 0) {
        proceedBtn.disabled = false;
    } else {
        proceedBtn.disabled = true;
    }
};

// Event Init
document.addEventListener('DOMContentLoaded', () => {
    const proceedBtn = document.getElementById('proceed-snacks-btn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', () => {
            if (selectedSeats.length > 0 && currentMovie) {
                openSnacksSelection(currentMovie, selectedSeats, seatPriceTotal);
            }
        });
    }
});
