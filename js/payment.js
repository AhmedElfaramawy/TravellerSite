document.addEventListener("DOMContentLoaded", function () {
    const bookingId = sessionStorage.getItem("bookingId");
    const bookingDetails = JSON.parse(sessionStorage.getItem("bookingDetails"));
    const selectedFlight = JSON.parse(sessionStorage.getItem("selectedFlight"));
    
    // Try to get flight details from either bookingDetails or selectedFlight
    const flightDetails = bookingDetails || selectedFlight;

    if (!flightDetails) {
        alert("No booking or flight details found. Please try again.");
        window.location.href = "index.html";
        return;
    }

    // Display flight details
    document.getElementById("flight-number").textContent = flightDetails.flight_number || "N/A";
    document.getElementById("departure").textContent = flightDetails.departure || "N/A";
    document.getElementById("destination").textContent = flightDetails.destination || "N/A";
    document.getElementById("departure-time").textContent = flightDetails.departure_time || "N/A";
    document.getElementById("price").textContent = flightDetails.price || "N/A";

    // Payment method selection
    const paymentOptions = document.querySelectorAll('.payment-option');
    const paymentForm = document.getElementById('payment-form');
    const confirmPaymentBtn = document.getElementById('confirm-payment');
    let selectedPaymentMethod = null;

    // Handle payment option selection
    paymentOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            paymentOptions.forEach(opt => opt.classList.remove('selected'));
            
            // Add selected class to clicked option
            this.classList.add('selected');
            
            // Store selected payment method
            selectedPaymentMethod = this.getAttribute('data-method');
            
            // Show appropriate form based on selected method
            showPaymentForm(selectedPaymentMethod);
        });
    });

    // Show payment form based on selected method
    function showPaymentForm(method) {
        let formHtml = '';
        
        switch(method) {
            case 'vodafone':
                formHtml = `
                    <div class="form-group">
                        <label for="phone">Vodafone Cash Phone Number <span class="required">*</span></label>
                        <input type="text" id="phone" placeholder="Enter your phone number (e.g., 01000000000)" 
                               required pattern="^01[0-2|5]\d{8}$" 
                               title="Please enter a valid Vodafone number (e.g., 01000000000)"
                               autocomplete="tel">
                        <small class="error-message" id="phone-error">This field is required</small>
                    </div>
                `;
                break;
            case 'instapay':
                formHtml = `
                    <div class="form-group">
                        <label for="instapay-id">InstaPay ID <span class="required">*</span></label>
                        <input type="text" id="instapay-id" placeholder="Enter your InstaPay ID" 
                               required minlength="4">
                        <small class="error-message" id="instapay-id-error">This field is required</small>
                    </div>
                    <div class="form-group">
                        <label for="instapay-password">Password <span class="required">*</span></label>
                        <input type="password" id="instapay-password" placeholder="Enter your password" 
                               required minlength="6">
                        <small class="error-message" id="instapay-password-error">This field is required</small>
                    </div>
                `;
                break;
            case 'credit':
                formHtml = `
                    <div class="form-group">
                        <label for="card-number">Card Number <span class="required">*</span></label>
                        <input type="text" id="card-number" placeholder="XXXX XXXX XXXX XXXX" 
                               required pattern="^\d{4}\s?\d{4}\s?\d{4}\s?\d{4}$" 
                               title="Please enter a valid 16-digit card number"
                               autocomplete="cc-number">
                        <small class="error-message" id="card-number-error">This field is required</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date <span class="required">*</span></label>
                            <input type="text" id="expiry" placeholder="MM/YY" 
                                   required pattern="^(0[1-9]|1[0-2])\/([0-9]{2})$" 
                                   title="Please enter a valid expiry date (MM/YY)"
                                   autocomplete="cc-exp">
                            <small class="error-message" id="expiry-error">This field is required</small>
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV <span class="required">*</span></label>
                            <input type="text" id="cvv" placeholder="XXX" 
                                   required pattern="^[0-9]{3,4}$" 
                                   title="Please enter a valid 3 or 4 digit CVV"
                                   autocomplete="cc-csc"
                                   maxlength="4">
                            <small class="error-message" id="cvv-error">This field is required</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="card-holder">Cardholder Name <span class="required">*</span></label>
                        <input type="text" id="card-holder" placeholder="Enter name as it appears on card" 
                               required minlength="3"
                               autocomplete="cc-name">
                        <small class="error-message" id="card-holder-error">This field is required</small>
                    </div>
                `;
                break;
            default:
                formHtml = '<p>Please select a payment method</p>';
        }
        
        // Update form content
        paymentForm.innerHTML = formHtml;
        
        // Show form and enable confirm button
        paymentForm.classList.add('active');
        confirmPaymentBtn.disabled = true; // Initially disable the button until validation passes
        
        // Add input validation
        const inputs = paymentForm.querySelectorAll('input');
        inputs.forEach(input => {
            // Add both input and blur event listeners for real-time validation
            input.addEventListener('input', () => validateInput(input));
            input.addEventListener('blur', () => validateInput(input, true));
        });
    }

    // Validate a single input field
    function validateInput(input, showError = false) {
        let isValid = true;
        const errorElement = document.getElementById(`${input.id}-error`);
        
        // Clear previous error message
        if (errorElement) {
            errorElement.textContent = '';
        }
        
        // Check if field is empty
        if (!input.value.trim()) {
            isValid = false;
            if (showError && errorElement) {
                errorElement.textContent = 'This field is required';
            }
        } 
        // Check if field meets pattern requirements
        else if (input.pattern && !new RegExp(input.pattern).test(input.value)) {
            isValid = false;
            if (showError && errorElement) {
                errorElement.textContent = input.title || 'Invalid format';
            }
        }
        // Check minimum length if specified
        else if (input.minLength && input.value.length < input.minLength) {
            isValid = false;
            if (showError && errorElement) {
                errorElement.textContent = `Must be at least ${input.minLength} characters`;
            }
        }
        
        // Add visual feedback
        if (!isValid) {
            input.classList.add('invalid');
            input.classList.remove('valid');
        } else {
            input.classList.add('valid');
            input.classList.remove('invalid');
        }
        
        // Validate all fields and update confirm button state
        validateForm();
        
        return isValid;
    }
    
    // Form validation for all fields
    function validateForm() {
        const inputs = paymentForm.querySelectorAll('input');
        let isValid = true;
        
        inputs.forEach(input => {
            // Check validity without showing error messages
            const inputValid = input.checkValidity() && input.value.trim() !== '';
            if (!inputValid) {
                isValid = false;
            }
        });
        
        confirmPaymentBtn.disabled = !isValid;
        return isValid;
    }

    // Handle payment confirmation
    confirmPaymentBtn.addEventListener("click", function () {
        if (confirmPaymentBtn.disabled) return;
        
        // Show loading state
        confirmPaymentBtn.textContent = "Processing...";
        confirmPaymentBtn.disabled = true;
        
        // Simulate payment processing
        setTimeout(() => {
            // Create payment data object
            const paymentData = {
                booking_id: bookingId || (flightDetails ? flightDetails.id : null),
                payment_method: selectedPaymentMethod,
                amount: flightDetails.price,
                status: "success"
            };
            
            // Store payment in sessionStorage
            sessionStorage.setItem("paymentData", JSON.stringify(paymentData));
            
            // Show success message
            alert("Payment successful! Your booking is confirmed.");
            
            // Clean up session storage
            if (bookingId) sessionStorage.removeItem("bookingId");
            if (bookingDetails) sessionStorage.removeItem("bookingDetails");
            
            // Redirect to profile page
            window.location.href = "profile.html";
        }, 2000);
    });

    // Handle payment cancellation
    document.getElementById("cancel-payment").addEventListener("click", function () {
        const confirmCancel = confirm("Are you sure you want to cancel this payment?");
        if (confirmCancel) {
            window.location.href = "index.html";
        }
    });
});
