document.addEventListener("DOMContentLoaded", function () {
    const selectedFlight = JSON.parse(sessionStorage.getItem("selectedFlight"));
    const selectedDestination = sessionStorage.getItem("selectedDestination");
    // Also try to get the flight ID from separate storage if available
    const selectedFlightId = sessionStorage.getItem("selectedFlightId");

    if (!selectedFlight) {
        document.getElementById("booking-details").innerHTML = "<p>No booking data available.</p>";
        return;
    }
    
    console.log("Selected flight data:", selectedFlight);
    console.log("Selected flight ID from separate storage:", selectedFlightId);
    
    // Determine which flight ID to use
    // First try the ID from the flight object, then from separate storage
    const flightId = selectedFlight.id || selectedFlightId;
    
    if (!flightId) {
        console.error("Could not determine flight ID");
        document.getElementById("booking-details").innerHTML = "<p>Error: Could not determine flight ID. Please go back and try again.</p>";
        return;
    }
    
    fetch(API_ENDPOINTS.getFlight, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ flight_id: flightId })
    })
    .then(response => response.json())
    .then(data => {
        const bookingDetails = document.getElementById("booking-details");

        if (data.status === "error") {
            bookingDetails.innerHTML = `<p>Error: ${data.message}</p>`;
            return;
        }

        const flight = data.flight;
        // Get the selected destination from session storage
        const chosenDestination = selectedDestination || "Default Destination";
        
        bookingDetails.innerHTML = `
            <h2>Booking Details</h2>
            <p><strong>Your Selected Travel:</strong> ${chosenDestination}</p>
            <p><strong>Flight Number:</strong> ${flight.flight_number}</p>
            <p><strong>From:</strong> ${flight.departure} → <strong>To:</strong> ${flight.destination}</p>
            <p><strong>Departure Time:</strong> ${flight.departure_time}</p>
            <p><strong>Price:</strong> ${flight.price} $</p>
            <p><strong>Available Seats:</strong> ${flight.seats_available}</p>
        `;
    })
    .catch(error => console.error("Error while fetching flight data:", error));
});
