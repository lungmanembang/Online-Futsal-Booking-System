const timeSlots = ["07:00:00", "08:00:00", "09:00:00", "17:00:00", "18:00:00", "19:00:00"];

document.getElementById('booking_date').addEventListener('change', function() {
    const selectedDate = this.value;
    const grid = document.getElementById('availabilityGrid');

    fetch(`get_availability.php?date=${selectedDate}`)
        .then(response => response.json())
        .then(booked => {
            let html = `
                <div class="grid-header">Time</div>
                <div class="grid-header">Main Arena</div>
                <div class="grid-header">Pro Court</div>
            `;

            timeSlots.forEach(time => {
                const displayTime = time.substring(0, 5); // 08:00
                html += `<div class="grid-cell time-cell">${displayTime}</div>`;

                // Check Court 1 and Court 2
                [1, 2].forEach(courtId => {
                    const isBooked = booked.includes(`${courtId}-${time}`);
                    const statusClass = isBooked ? 'status-busy' : 'status-available';
                    const statusText = isBooked ? 'BUSY' : 'OPEN';
                    html += `<div class="grid-cell ${statusClass}">${statusText}</div>`;
                });
            });

            grid.innerHTML = html;
        });
});