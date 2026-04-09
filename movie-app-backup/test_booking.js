const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  page.on('console', msg => console.log('BROWSER LOG:', msg.text()));
  page.on('pageerror', error => console.log('JS EXCEPTION:', error.message));
  await page.goto('http://127.0.0.1/movie-app/');
  // Quick test: simulate filling _pendingBookingData and triggering finalizedBooking
  await page.evaluate(async () => {
     window.currentUser = { email: 'a@a.com', name: 'A' }; // Auth mock
     localStorage.setItem('ct_session_user', JSON.stringify({ email: 'a@a.com', name: 'A' }));
  });
  await page.reload();
  await page.evaluate(async () => {
     try {
         const m = await import('./js/booking.js');
         await m.finalizeBooking({
            movie: { title: 'Test', id: '1' },
            seats: [{id:'A1', type:'regular'}],
            baseTotal: 100,
            snacks: [], snacksTotal: 0, grandTotal: 100
         });
         console.log('Finalize booking done!');
     } catch (e) {
         console.error('Captured Error:', e);
     }
  });
  await page.waitForTimeout(2000);
  await browser.close();
})();
