function loadAvailability() {
    const date = document.getElementById('booking_date').value;
    const courtId = document.getElementById('court_id').value;
    const grid = document.getElementById('availabilityGrid');

    if (!date) return;

    grid.innerHTML = "<p>Loading slots...</p>";

    fetch(`get_availability.php?date=${date}&court_id=${courtId}`)
        .then(res => res.json())
        .then(data => {
            grid.innerHTML = '';
            data.forEach(slot => {
                const card = document.createElement('div');
                card.className = `slot-card status-${slot.status.toLowerCase()}`;
                card.innerHTML = `<strong>${slot.time_label}</strong><br><small>${slot.status}</small>`;

                if (slot.status === 'Available') {
                    card.style.cursor = "pointer";
                    card.onclick = () => {
                        document.getElementById('start_time').value = slot.raw_time;
                        // Visual feedback for selection
                        document.querySelectorAll('.slot-card').forEach(s => s.style.border = "1px solid #ddd");
                        card.style.border = "2px solid #3498db";
                    };
                }
                grid.appendChild(card);
            });
        });
}

// Initialize listeners once the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('booking_date');
    const courtInput = document.getElementById('court_id');

    // Set default date to today
    if (!dateInput.value) dateInput.valueAsDate = new Date();

    dateInput.addEventListener('change', loadAvailability);
    courtInput.addEventListener('change', loadAvailability);

    loadAvailability(); // Initial load
});