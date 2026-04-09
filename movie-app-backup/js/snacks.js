import { navigateTo } from './ui.js';

const SNACKS_DATA = [
    { id: 's1', name: 'Popcorn (Medium)', price: 120, icon: '🍿' },
    { id: 's2', name: 'Popcorn (Large)', price: 180, icon: '🍿' },
    { id: 's3', name: 'Coca Cola', price: 80, icon: '🥤' },
    { id: 's4', name: 'Nachos with Salsa', price: 150, icon: '🌮' },
    { id: 's5', name: 'Combo: Popcorn + Coke', price: 220, icon: '🍿+🥤' }
];

// =============================================================
// ALL shared state is stored on `window` to survive the ES module
// identity mismatch caused by versioned <script> tags in index.php.
// Without this, seats.js imports ./snacks.js (no version) while
// index.php loads snacks.js?v=N — two separate module instances
// with two separate copies of every module-scoped variable.
// =============================================================

export const openSnacksSelection = (movie, seats, baseTotal) => {
    window._snacksBookingData = { movie, seats, baseTotal };
    window._currentSnacks = {};
    window._snacksTotal = 0;

    // Initialize quantities to 0
    SNACKS_DATA.forEach(s => window._currentSnacks[s.id] = 0);

    renderSnacksGrid();
    updateSnacksFooter();
    navigateTo('snacks-section');
};

const renderSnacksGrid = () => {
    const grid = document.getElementById('snacks-grid');
    const cs = window._currentSnacks || {};
    grid.innerHTML = SNACKS_DATA.map(snack => `
        <div class="snack-card glass-panel" data-id="${snack.id}">
            <div class="snack-icon">${snack.icon}</div>
            <div class="snack-title">${snack.name}</div>
            <div class="snack-price">₹${snack.price}</div>
            <div class="snack-controls">
                <button class="qty-btn minus-btn" data-id="${snack.id}">-</button>
                <span class="qty-display" id="qty-${snack.id}">${cs[snack.id] || 0}</span>
                <button class="qty-btn plus-btn" data-id="${snack.id}">+</button>
            </div>
        </div>
    `).join('');

    // Attach listeners
    grid.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', () => updateSnackQuantity(btn.dataset.id, 1));
    });
    grid.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', () => updateSnackQuantity(btn.dataset.id, -1));
    });
};

const updateSnackQuantity = (id, delta) => {
    const cs = window._currentSnacks || {};
    if ((cs[id] || 0) + delta >= 0) {
        cs[id] = (cs[id] || 0) + delta;
        window._currentSnacks = cs;
        document.getElementById(`qty-${id}`).textContent = cs[id];
        updateSnacksFooter();
    }
};

export const incrementSnackByVoice = () => {
    updateSnackQuantity('s1', 1);
};

const updateSnacksFooter = () => {
    const cs = window._currentSnacks || {};
    let total = 0;
    SNACKS_DATA.forEach(s => {
        total += (cs[s.id] || 0) * s.price;
    });
    window._snacksTotal = total;

    const bd = window._snacksBookingData || {};
    document.getElementById('snacks-total').textContent = total;
    document.getElementById('grand-total').textContent = ((bd.baseTotal || 0) + total);
};

document.addEventListener('DOMContentLoaded', () => {
    const proceedBtn = document.getElementById('proceed-checkout-btn');
    if (proceedBtn) {
        proceedBtn.addEventListener('click', () => {
            const cs = window._currentSnacks || {};
            const snkTotal = window._snacksTotal || 0;

            const finalSnacksArr = [];
            SNACKS_DATA.forEach(s => {
                if ((cs[s.id] || 0) > 0) {
                    finalSnacksArr.push({
                        ...s,
                        quantity: cs[s.id]
                    });
                }
            });

            const bd = window._snacksBookingData || {};
            const grandTotal = (bd.baseTotal || 0) + snkTotal;

            // Store pending booking and open payment modal
            window._pendingBookingData = {
                movie: bd.movie,
                seats: bd.seats,
                baseTotal: bd.baseTotal,
                snacks: finalSnacksArr,
                snacksTotal: snkTotal,
                grandTotal
            };
            console.log('_pendingBookingData set:', JSON.stringify(window._pendingBookingData, null, 2));

            const payModal = document.getElementById('payment-modal');
            const payText = document.getElementById('payment-summary-text');
            if (payText) payText.textContent = `Grand Total: ₹${grandTotal} (${(bd.seats || []).length} seat${(bd.seats || []).length > 1 ? 's' : ''} + snacks)`;
            if (payModal) payModal.classList.remove('hidden');
        });
    }
});
