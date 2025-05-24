document.addEventListener('DOMContentLoaded', function() {
    const bookingId = new URLSearchParams(window.location.search).get('id');
    const bookingContainer = document.getElementById('booking-details-container');

    if (!bookingId) {
        showError('No booking ID provided');
        return;
    }

    // Load booking details
    loadBookingDetails(bookingId);

    // Function to load booking details
    function loadBookingDetails(bookingId) {
        // Show loading state
        bookingContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading booking details...</p>
            </div>`;

        // In a real application, you would fetch this from your API
        // For now, we'll simulate an API call with a timeout
        setTimeout(() => {
            // This is mock data - replace with actual API call
            const mockBooking = {
                id: bookingId,
                booking_reference: `BOOK-${Math.floor(100000 + Math.random() * 900000)}`,
                status: 'confirmed',
                booking_date: new Date().toISOString(),
                total_amount: 450.00,
                currency: 'SAR',
                flight: {
                    flight_number: 'SV' + Math.floor(100 + Math.random() * 900),
                    airline: 'Saudi Airlines',
                    departure: {
                        airport: 'JED',
                        city: 'Jeddah',
                        time: '2025-06-15T08:30:00',
                        terminal: '1'
                    },
                    arrival: {
                        airport: 'RUH',
                        city: 'Riyadh',
                        time: '2025-06-15T10:45:00',
                        terminal: '2'
                    },
                    aircraft: 'Boeing 787-9',
                    cabin_class: 'Economy',
                    duration: '2h 15m',
                    baggage_allowance: '30kg'
                },
                passengers: [
                    {
                        name: 'Ahmed Ali',
                        type: 'Adult',
                        seat: '15A',
                        ticket_number: `TKT-${Math.floor(1000000 + Math.random() * 9000000)}`
                    },
                    {
                        name: 'Sara Ahmed',
                        type: 'Child',
                        seat: '15B',
                        ticket_number: `TKT-${Math.floor(1000000 + Math.random() * 9000000)}`
                    }
                ],
                payment: {
                    method: 'Credit Card',
                    card_ending: '•••• 4242',
                    payment_date: new Date().toISOString(),
                    status: 'Paid'
                }
            };

            // Render booking details
            renderBookingDetails(mockBooking);
        }, 1000);
    }

    // Function to render booking details
    function renderBookingDetails(booking) {
        const formattedDate = new Date(booking.booking_date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });

        const statusClass = {
            'confirmed': 'success',
            'pending': 'warning',
            'cancelled': 'danger',
            'completed': 'info'
        }[booking.status] || 'secondary';

        bookingContainer.innerHTML = `
            <div class="booking-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                    <div class="mb-3 mb-md-0">
                        <span class="badge bg-${statusClass} fs-6 fw-normal mb-2">${booking.status.toUpperCase()}</span>
                        <h2 class="h4 mb-1">Booking #${booking.booking_reference}</h2>
                        <p class="text-muted mb-0">Booked on ${formattedDate}</p>
                    </div>
                    <div class="text-md-end">
                        <div class="h3 mb-0">${booking.total_amount.toFixed(2)} ${booking.currency}</div>
                        <div class="text-muted">Total Amount</div>
                    </div>
                </div>
            </div>

            <div class="booking-section">
                <h3 class="h5 mb-3">Flight Details</h3>
                <div class="booking-flight-info">
                    <div class="airline-logo">
                        <i class="fas fa-plane"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h4 class="h5 mb-1">${booking.flight.airline}</h4>
                        <p class="text-muted mb-0">Flight ${booking.flight.flight_number} • ${booking.flight.aircraft}</p>
                        <p class="text-muted mb-0">${booking.flight.cabin_class} Class</p>
                    </div>
                </div>

                <div class="flight-route">
                    <div class="text-center">
                        <div class="fw-bold">${formatTime(booking.flight.departure.time)}</div>
                        <div class="text-muted small">${formatDate(booking.flight.departure.time)}</div>
                        <div class="mt-2 fw-bold">${booking.flight.departure.airport}</div>
                        <div class="text-muted small">${booking.flight.departure.city}</div>
                        <div class="text-muted small">Terminal ${booking.flight.departure.terminal}</div>
                    </div>

                    <div class="d-flex flex-column align-items-center">
                        <div class="route-dot"></div>
                        <div class="route-line"></div>
                        <div class="route-dot"></div>
                        <div class="mt-2 small text-muted">${booking.flight.duration}</div>
                    </div>

                    <div class="text-center">
                        <div class="fw-bold">${formatTime(booking.flight.arrival.time)}</div>
                        <div class="text-muted small">${formatDate(booking.flight.arrival.time)}</div>
                        <div class="mt-2 fw-bold">${booking.flight.arrival.airport}</div>
                        <div class="text-muted small">${booking.flight.arrival.city}</div>
                        <div class="text-muted small">Terminal ${booking.flight.arrival.terminal}</div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-suitcase-rolling me-2"></i>
                    Baggage allowance: ${booking.flight.baggage_allowance} per passenger
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="passenger-details">
                        <h3 class="h5 mb-3">Passenger Details</h3>
                        ${booking.passengers.map(passenger => `
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="h6 mb-1">${passenger.name}</h4>
                                        <span class="badge bg-light text-dark">${passenger.type}</span>
                                    </div>
                                    <div class="text-end">
                                        <div>Seat: ${passenger.seat}</div>
                                        <div class="text-muted small">${passenger.ticket_number}</div>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="payment-details h-100">
                        <h3 class="h5 mb-3">Payment Information</h3>
                        <div class="detail-item">
                            <span class="detail-label">Payment Method:</span>
                            <span class="detail-value">${booking.payment.method} (•••• ${booking.payment.card_ending})</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Status:</span>
                            <span class="detail-value">
                                <span class="badge bg-success">${booking.payment.status}</span>
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Date:</span>
                            <span class="detail-value">${new Date(booking.payment.payment_date).toLocaleString()}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Total Amount:</span>
                            <span class="detail-value fw-bold">${booking.total_amount.toFixed(2)} ${booking.currency}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="actions">
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Ticket
                </button>
                <button class="btn btn-outline-secondary ms-auto" onclick="window.history.back()">
                    <i class="fas fa-arrow-left me-2"></i>Back to Bookings
                </button>
            </div>`;
    }

    // Helper function to format time
    function formatTime(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    // Helper function to format date
    function formatDate(dateTimeString) {
        const date = new Date(dateTimeString);
        return date.toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }

    // Function to show error message
    function showError(message) {
        bookingContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                ${message}
            </div>
            <div class="text-center mt-3">
                <a href="profile.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Profile
                </a>
            </div>`;
    }
});
