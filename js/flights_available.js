document.addEventListener("DOMContentLoaded", function () {
    // Function to get URL parameters
    function getUrlParams() {
        const params = {};
        const queryString = window.location.search;
        if (queryString) {
            const urlParams = new URLSearchParams(queryString);
            urlParams.forEach((value, key) => {
                params[key] = value;
            });
        }
        return params;
    }
    
    // Get parameters from URL
    const urlParams = getUrlParams();
    let destinationText = "All Destinations";
    
    // Check if we have destination in URL parameters
    if (urlParams.destination) {
        destinationText = decodeURIComponent(urlParams.destination);
        // Store the destination for the booking page
        sessionStorage.setItem("selectedDestination", destinationText);
    } else {
        // Check if we have it in sessionStorage as a fallback
        const travelChoice = JSON.parse(sessionStorage.getItem("travelChoice"));
        if (travelChoice && travelChoice.destination) {
            destinationText = travelChoice.destination;
            sessionStorage.setItem("selectedDestination", destinationText);
        }
    }
    
    // Display the destination
    document.getElementById("destination-text").textContent = destinationText;
    
    console.log("Fetching flights from:", API_ENDPOINTS.flightsAvailable);
    
    fetch(API_ENDPOINTS.flightsAvailable)
        .then(response => {
            console.log("API Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("API Response data:", data);
            const flightsList = document.getElementById("flights-list");
            flightsList.innerHTML = "";

            if (data.status === "success") {
                if (data.flights && data.flights.length > 0) {
                    // Display flights
                    data.flights.forEach(flight => {
                        const flightDiv = document.createElement("div");
                        flightDiv.classList.add("flight-item");
                        flightDiv.innerHTML = `
                            <h3>Flight Number: ${flight.flight_number}</h3>
                            <p><strong>Route:</strong> ${flight.departure} to ${flight.destination}</p>
                            <p><strong>Departure Time:</strong> ${flight.departure_time}</p>
                            <p><strong>Price:</strong> ${flight.price} $</p>
                            <button class="select-flight btn" data-flight='${JSON.stringify(flight)}'>Book Now</button>
                        `;
                        flightsList.appendChild(flightDiv);
                    });

                    // Add event listeners to book buttons
                    document.querySelectorAll(".select-flight").forEach(button => {
                        button.addEventListener("click", function () {
                            const selectedFlight = JSON.parse(this.getAttribute("data-flight"));
                            sessionStorage.setItem("selectedFlight", JSON.stringify(selectedFlight));
                            window.location.href = "booking.html"; // ✅ الانتقال إلى صفحة الحجز
                        });
                    });
                } else {
                    // No flights found
                    flightsList.innerHTML = `
                        <div class="no-flights-message">
                            <p>❌ No flights available at this time.</p>
                            <p>Selected destination: ${destinationText}</p>
                        </div>
                    `;
                }
            } else {
                // Error in API response
                flightsList.innerHTML = `
                    <div class="error-message">
                        <p>❌ Error loading flights.</p>
                        <p>${data.message || 'Please try again later.'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error("❌ Error loading flights:", error);
            const flightsList = document.getElementById("flights-list");
            flightsList.innerHTML = `
                <div class="error-message">
                    <p>❌ Error connecting to the server.</p>
                    <p>Please check your database connection or try again later.</p>
                </div>
            `;
        });
});

