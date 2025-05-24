document.addEventListener("DOMContentLoaded", function () {
    const searchData = JSON.parse(sessionStorage.getItem("searchData"));

    if (!searchData) {
        document.getElementById("flights-list").innerHTML = "<p>لا توجد بيانات بحث.</p>";
        return;
    }

    fetch(API_ENDPOINTS.flights, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(searchData)
    })
    .then(response => response.json())
    .then(data => {
        const flightsContainer = document.getElementById("flights-list");
        flightsContainer.innerHTML = "";

        if (data.status === "no_results") {
            flightsContainer.innerHTML = "<p>لا توجد رحلات متاحة.</p>";
            return;
        }

        data.flights.forEach(flight => {
            const flightCard = document.createElement("div");
            flightCard.classList.add("flight-card");

            flightCard.innerHTML = `
                <span>flight Number : <i>${flight.flight_number}</i></span>
                <span>departure : <i>${flight.departure}</i></span>
                <span>destination : <i>${flight.destination}</i></span>
                <span>departure time: <i>${flight.departure_time}</i></span>
                <span>arrival time: <i>${flight.arrival_time}</i></span>
                <span>seats available : <i>${flight.seats_available}</i></span>
                <span>price : <i>${flight.price}</i></span>
                <input type="button" value="pay" class="btn select-flight" data-id="${flight.id}">
            `;

            flightsContainer.appendChild(flightCard);
        });

        document.querySelectorAll(".select-flight").forEach(button => {
            button.addEventListener("click", function () {
                const flightData = {
                    flightId: this.getAttribute("data-id"),
                    flight_number: this.parentElement.querySelector("span:nth-child(1) i").textContent,
                    departure: this.parentElement.querySelector("span:nth-child(2) i").textContent,
                    destination: this.parentElement.querySelector("span:nth-child(3) i").textContent,
                    departure_time: this.parentElement.querySelector("span:nth-child(4) i").textContent,
                    arrival_time: this.parentElement.querySelector("span:nth-child(5) i").textContent,
                    seats_available: this.parentElement.querySelector("span:nth-child(6) i").textContent,
                    price: this.parentElement.querySelector("span:nth-child(7) i").textContent
                };
        
                console.log("🚀 تخزين بيانات الرحلة:", flightData);
                sessionStorage.setItem("selectedFlight", JSON.stringify(flightData));
        
                window.location.href = "booking.html";
            });
        });
        
    })
    .catch(error => {
        console.error("خطأ في جلب الرحلات:", error);
        document.getElementById("flights-list").innerHTML = "<p>حدث خطأ أثناء تحميل الرحلات.</p>";
    });
});

