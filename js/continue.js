document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("booking-form");
    const bookAndPayBtn = document.getElementById("book-and-pay-btn");

    // Function to collect and validate booking data
    function collectBookingData() {
        // ✅ الحصول على معرف المستخدم من الجلسة
        const userId = sessionStorage.getItem("userId") || localStorage.getItem("userId");
        // ✅ الحصول على بيانات الرحلة المحددة
        const selectedFlight = JSON.parse(sessionStorage.getItem("selectedFlight"));
        // ✅ محاولة الحصول على معرف الرحلة من مكان تخزين منفصل إذا كان متاحًا
        const selectedFlightId = sessionStorage.getItem("selectedFlightId");

        console.log("User ID from session:", userId);
        console.log("Selected flight data:", selectedFlight);

        if (!userId) {
            alert("Error! You must be logged in to book a flight.");
            window.location.href = "login.html";
            return null;
        }

        if (!selectedFlight) {
            alert("Error! No flight selected. Please try again.");
            return null;
        }
        
        // Determine which flight ID to use
        // First try the ID from the flight object, then from separate storage
        const flightId = selectedFlight.id || selectedFlightId;
        
        if (!flightId) {
            console.error("Could not determine flight ID");
            alert("Error! Could not determine flight ID. Please go back and try again.");
            return null;
        }

        // Validate required fields
        const name = document.getElementById("name").value;
        const famName = document.getElementById("fam-name").value;
        const email = document.getElementById("E-mail").value;
        const phone = document.getElementById("num").value;
        const birthDay = document.getElementById("birth-day").value;
        const passportNum = document.getElementById("passport-num").value;
        const nationality = document.getElementById("passportNationality_0").value;
        const passportExpiry = document.getElementById("passport-time").value;

        if (!name || !famName || !email || !phone || !birthDay || !passportNum || !nationality || !passportExpiry) {
            alert("Please fill in all required fields");
            return null;
        }

        // ✅ تحديد قيمة الجنس المختار
        const genderInput = document.querySelector('input[name="sex"]:checked');
        const gender = genderInput ? genderInput.id : "male"; // استخدام القيمة الافتراضية "male" إذا لم يتم اختيار أي قيمة
        
        console.log("Selected gender:", gender);
        
        const bookingData = {
            user_id: userId,
            flight_id: flightId, // استخدام معرف الرحلة المحدد
            first_name: name,
            last_name: famName,
            email: email,
            phone_number: phone,
            birth_date: birthDay,
            gender: gender, // استخدام القيمة المعدلة للجنس
            passport_number: passportNum,
            nationality: nationality,
            passport_expiration: passportExpiry
        };
        
        console.log("Complete booking data being sent:", bookingData);

        // Store booking details in session storage for payment page
        sessionStorage.setItem("bookingDetails", JSON.stringify({
            flight_number: selectedFlight.flight_number,
            departure: selectedFlight.departure,
            destination: selectedFlight.destination,
            departure_time: selectedFlight.departure_time,
            price: selectedFlight.price
        }));

        return bookingData;
    }

    // Check if user already has a booking for this flight
    async function checkExistingBooking(userId, flightId) {
        try {
            const response = await fetch(`${API_ENDPOINTS.checkBooking}?user_id=${userId}&flight_id=${flightId}`);
            const data = await response.json();
            return data.exists;
        } catch (error) {
            console.error("Error checking existing booking:", error);
            return false; // Assume no booking exists if there's an error
        }
    }

    // Handle form submission (combined functionality)
    form.addEventListener("submit", async function (event) {
        event.preventDefault();
        
        // Show loading state
        const submitBtn = document.getElementById("book-and-pay-btn");
        const originalBtnHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;

        try {
            const bookingData = collectBookingData();
            if (!bookingData) {
                submitBtn.innerHTML = originalBtnHTML;
                submitBtn.disabled = false;
                return;
            }

            // Check if user already has a booking for this flight
            const hasExistingBooking = await checkExistingBooking(bookingData.user_id, bookingData.flight_id);
            
            if (hasExistingBooking) {
                alert("You already have a booking for this flight. Please check your profile for existing bookings.");
                submitBtn.innerHTML = originalBtnHTML;
                submitBtn.disabled = false;
                return;
            }

            console.log("Sending data to booking API:", bookingData);

            // Submit booking data to API
            const response = await fetch(API_ENDPOINTS.booking, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(bookingData)
            });
            
            const data = await response.json();
            console.log("Booking API response:", data);
            
            if (data.status === "success") {
                // Store booking ID and passenger details
                sessionStorage.setItem("bookingId", data.booking_id);
                sessionStorage.setItem("passengerDetails", JSON.stringify(bookingData));
                
                console.log("✅ Booking successful! Redirecting to payment page...");
                
                // ✅ قم بتعيين بيانات الدفع بشكل مناسب للصفحة التالية
                let flightDetails = {
                    id: bookingData.flight_id,
                    flight_number: selectedFlight.flight_number,
                    departure: selectedFlight.departure,
                    destination: selectedFlight.destination,
                    departure_time: selectedFlight.departure_time,
                    price: selectedFlight.price
                };
                
                // ✅ تأكد من تخزين تفاصيل الرحلة لصفحة الدفع
                sessionStorage.setItem("bookingDetails", JSON.stringify(flightDetails));
                
                try {
                    // ✅ التوجيه إلى صفحة الدفع مع استخدام setTimeout لنفعل التوجيه للتأكد من تخزين البيانات أولاً
                    setTimeout(function() {
                        window.location.href = "payment.html";
                    }, 500);
                } catch (err) {
                    console.error("Navigation error:", err);
                    // ✅ إذا فشل التوجيه، حاول فتح صفحة جديدة
                    alert("Redirecting to payment page...");
                    window.open("payment.html", "_blank");
                }
            } else {
                alert(data.message || "Error creating booking. Please try again.");
                submitBtn.innerHTML = originalBtnHTML;
                submitBtn.disabled = false;
            }
        } catch (error) {
            console.error("Error processing booking:", error);
            alert("An error occurred while processing your booking. Please try again.");
            submitBtn.innerHTML = originalBtnHTML;
            submitBtn.disabled = false;
        }
    });
});
