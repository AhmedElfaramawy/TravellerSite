// Update phone number display in the UI
function updatePhoneDisplay(phoneNumber, userId) {
    console.log("Updating phone display for user", userId, "with:", phoneNumber);
    
    // Update input field
    const phoneInput = document.getElementById("phone");
    if (phoneInput) {
        phoneInput.value = phoneNumber || "";
    }
    
    // Update display elements
    const phoneDisplays = document.querySelectorAll("#user-phone, #user-phone-display");
    phoneDisplays.forEach(display => {
        if (display) {
            display.textContent = phoneNumber || "Not set";
            if (phoneNumber && phoneNumber.trim() !== "") {
                display.classList.add('has-number');
            } else {
                display.classList.remove('has-number');
            }
        }
    });
    
    // Store in both session and local storage for persistence, scoped by user ID
    if (userId) {
        const storageKey = `userPhone_${userId}`;
        if (phoneNumber && phoneNumber.trim() !== "") {
            localStorage.setItem(storageKey, phoneNumber);
            sessionStorage.setItem(storageKey, phoneNumber);
        } else {
            localStorage.removeItem(storageKey);
            sessionStorage.removeItem(storageKey);
        }
    }
    
    // If we're in edit mode and the phone number is cleared, show the form
    const phoneForm = document.querySelector('.phone-form');
    const phoneDisplay = document.querySelector('.phone-display');
    if ((!phoneNumber || phoneNumber.trim() === '') && phoneForm && phoneDisplay) {
        phoneForm.style.display = 'block';
        phoneDisplay.style.display = 'none';
    }
}

// Show success message to user
function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.alerts-container') || document.body;
    container.prepend(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Show error message to user
function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        <i class="fas fa-exclamation-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('.alerts-container') || document.body;
    container.prepend(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// Load user profile data
async function loadUserProfile(userId) {
    try {
        console.log('Loading profile for user ID:', userId);
        const response = await fetch(API_ENDPOINTS.profile, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Profile data received:', data);
        
        if (data.status === 'success') {
            // Update profile information
            document.getElementById('profile-name').textContent = data.name || 'User';
            
            // Update user ID and username
            const userIdElement = document.getElementById('user-id');
            const usernameElement = document.getElementById('username');
            const emailElement = document.getElementById('email');
            
            if (userIdElement) userIdElement.textContent = data.id || 'N/A';
            if (usernameElement) usernameElement.textContent = data.name || 'N/A';
            if (emailElement) emailElement.textContent = data.email || 'No email provided';
            
            // Update phone number display (without populating the input field)
            const phoneDisplay = document.getElementById('user-phone');
            if (phoneDisplay && data.phone_number) {
                phoneDisplay.textContent = data.phone_number;
            }
            
            // Check if user is admin
            if (data.role === 'admin') {
                const adminBtn = document.getElementById('admin-dashboard-btn');
                if (adminBtn) adminBtn.style.display = 'block';
            }
            
            // Load user bookings
            loadUserBookings(userId);
        } else {
            showError(data.message || 'Failed to load profile');
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        showError('Failed to load profile. Please try again.');
    }
}

// Load user bookings
async function loadUserBookings(userId) {
    console.log('Loading bookings for user ID:', userId);
    const bookingsContainer = document.getElementById('booking-info');
    if (!bookingsContainer) {
        console.error('Bookings container not found');
        return;
    }
    
    // Show loading state
    bookingsContainer.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading bookings...</p>
        </div>`;
    
    try {
        console.log('Sending request to:', API_ENDPOINTS.booking);
        const response = await fetch(API_ENDPOINTS.booking, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                user_id: userId,
                action: 'get_bookings'
            })
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
        }

        const data = await response.json();
        console.log('Bookings data received:', data);
        
        // Check for bookings data in different possible locations
        const bookingsData = data.bookings || data.data || [];
        
        if (Array.isArray(bookingsData) && bookingsData.length > 0) {
            console.log('Rendering', bookingsData.length, 'bookings');
            renderBookings(bookingsData);
        } else {
            console.log('No bookings found in response');
            showNoBookingsMessage(bookingsContainer);
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
        showErrorMessage(bookingsContainer, 'Error loading bookings: ' + error.message);
    }
}

// Render bookings in the UI
function renderBookings(bookings) {
    console.log('Rendering bookings:', bookings);
    const bookingsContainer = document.getElementById('booking-info');
    if (!bookingsContainer) {
        console.error('Bookings container not found');
        return;
    }
    
    try {
        // Check if bookings is an array and has items
        if (!Array.isArray(bookings) || bookings.length === 0) {
            console.log('No bookings to display');
            showNoBookingsMessage(bookingsContainer);
            return;
        }

        // Clear the container
        bookingsContainer.innerHTML = '';

        // Create a row for the cards
        const row = document.createElement('div');
        row.className = 'bookings-grid';

        // Add each booking as a card
        bookings.forEach((booking, index) => {
            try {
                console.log('Processing booking:', booking);
                const card = createBookingCard(booking, index);
                if (card) {
                    row.appendChild(card);
                }
            } catch (error) {
                console.error('Error creating booking card:', error, booking);
            }
        });

        // Add the row to the container
        if (row.children.length > 0) {
            bookingsContainer.appendChild(row);
        } else {
            showNoBookingsMessage(bookingsContainer);
        }

    } catch (error) {
        console.error('Error rendering bookings:', error);
        showErrorMessage(bookingsContainer, 'Error displaying bookings: ' + error.message);
    }
}

// Create a booking card HTML
function createBookingCard(booking, index) {
    try {
        if (!booking) {
            console.error('Invalid booking data:', booking);
            return null;
        }

        // Extract data with fallbacks
        const bookingId = booking.id || booking.booking_id || 'N/A';
        const flightNumber = booking.flight_number || `FLT-${index + 1}`;
        const airline = booking.airline || 'شركة طيران';
        const departureCity = booking.departure || booking.departure_city || 'غير محدد';
        const arrivalCity = booking.destination || booking.arrival_city || 'غير محدد';
        const departureAirport = booking.departure_airport || '';
        const arrivalAirport = booking.arrival_airport || '';
        const status = (booking.status || 'مؤكد').toLowerCase();
        const statusClass = status.replace(/\s+/g, '-');
        const price = parseFloat(booking.price || 0).toFixed(2);
        const passengerName = booking.passenger_name || booking.name || 'زائر';
        const seatClass = booking.class || 'اقتصادية';
        const seatNumber = booking.seat_number || '--';
        const bookingDate = formatDate(booking.booking_date || new Date().toISOString());

        // Format dates
        const departureDate = formatDate(booking.departure_time || booking.departure_date);
        const arrivalDate = formatDate(booking.arrival_time || booking.arrival_date);
        
        // Get status icon and color
        const statusInfo = getStatusInfo(status);
        
        // Create actions based on status (case-insensitive check)
        let actions = '';
        const normalizedStatus = status.toLowerCase().trim();
        if (normalizedStatus === 'confirmed' || normalizedStatus === 'pending' || normalizedStatus === 'مؤكد' || normalizedStatus === 'قيد الانتظار') {
            actions = `
                <div class="booking-actions-container">
                    <button class="btn booking-btn-details" onclick="viewBookingDetails('${bookingId}')">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn booking-btn-cancel" onclick="cancelBooking('${bookingId}', this)">
                        <i class="fas fa-times"></i> Cancel Booking
                    </button>
                </div>`;
        } else {
            actions = `
                <div class="booking-actions-container">
                    <button class="btn booking-btn-details" onclick="viewBookingDetails('${bookingId}')">
                        <i class="fas fa-eye"></i> View Details
                    </button>
                    <button class="btn booking-btn-disabled" disabled>
                        <i class="fas fa-ban"></i> Non-refundable
                    </button>
                </div>`;
        }
        
        // Create booking card HTML
        const card = document.createElement('div');
        card.className = `booking-card-item booking-status-${statusClass}`;
        card.dataset.bookingId = bookingId;
        
        card.innerHTML = `
            <div class="booking-card-inner">
                <div class="booking-status-bar" style="background-color: ${statusInfo.color}20; border-left: 4px solid ${statusInfo.color}">
                    <div class="booking-status-info">
                        <i class="fas ${statusInfo.icon}" style="color: ${statusInfo.color}"></i>
                        <span class="booking-status-text">${statusInfo.text}</span>
                    </div>
                    <div class="booking-ref-number">
                        <span class="ref-label">Booking #:</span>
                        <span class="number">#${String(bookingId).padStart(6, '0')}</span>
                    </div>
                </div>
                
                <div class="booking-content-wrapper">
                    <div class="booking-header-content">
                        <div class="booking-flight-info">
                            <div class="booking-airline-logo">
                                <i class="fas fa-plane-departure"></i>
                            </div>
                            <div class="booking-flight-details">
                                <h3>${flightNumber}</h3>
                                <p>${airline}</p>
                            </div>
                        </div>
                        <div class="booking-date-info">
                            <i class="far fa-calendar-alt"></i>
                            <span>${bookingDate}</span>
                        </div>
                    </div>
                    
                    <div class="booking-route-info">
                        <div class="booking-location departure">
                            <div class="time">${formatTime(booking.departure_time || booking.departure_date)}</div>
                            <div class="airport">${departureAirport}</div>
                            <div class="city">${departureCity}</div>
                        </div>
                        
                        <div class="booking-route-line">
                            <div class="line"></div>
                            <div class="booking-plane-icon">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div class="booking-duration">${calculateDuration(booking.departure_time, booking.arrival_time)}</div>
                        </div>
                        
                        <div class="booking-location arrival">
                            <div class="time">${formatTime(booking.arrival_time || booking.arrival_date)}</div>
                            <div class="airport">${arrivalAirport}</div>
                            <div class="city">${arrivalCity}</div>
                        </div>
                    </div>
                    
                    <div class="booking-footer-content">
                        <div class="booking-passenger-info">
                            <div class="info-item">
                                <i class="fas fa-user"></i>
                                <span>${passengerName}</span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-chair"></i>
                                <span>Seat ${seatNumber} - ${seatClass}</span>
                            </div>
                        </div>
                        
                        <div class="booking-price-info">
                            <span class="booking-price-label">Total Price</span>
                            <span class="booking-price-amount">${price} <span class="currency">$</span></span>
                        </div>
                    </div>
                    
                    ${actions}
                </div>
            </div>`;
            
        return card;
        
    } catch (error) {
        console.error('Error creating booking card:', error, booking);
        return null;
    }
}

// Helper function to get status information
function getStatusInfo(status) {
    const statusMap = {
        'confirmed': {
            icon: 'fa-check-circle',
            text: 'Confirmed',
            color: '#28a745'
        },
        'pending': {
            icon: 'fa-clock',
            text: 'Pending',
            color: '#ffc107'
        },
        'cancelled': {
            icon: 'fa-times-circle',
            text: 'Cancelled',
            color: '#dc3545'
        },
        'completed': {
            icon: 'fa-check-double',
            text: 'Completed',
            color: '#17a2b8'
        },
        'refunded': {
            icon: 'fa-undo',
            text: 'Refunded',
            color: '#6c757d'
        }
    };
    
    // Map Arabic status to English
    const statusMapping = {
        'مؤكد': 'confirmed',
        'قيد الانتظار': 'pending',
        'ملغي': 'cancelled',
        'completed': 'completed',
        'refunded': 'refunded'
    };
    
    // Convert status to lowercase for case-insensitive matching
    const normalizedStatus = status.toLowerCase();
    const englishStatus = statusMapping[status] || normalizedStatus;
    
    return statusMap[englishStatus] || {
        icon: 'fa-ticket-alt',
        text: status || 'Unknown',
        color: '#6c757d'
    };
}

// Helper function to calculate duration between two dates
function calculateDuration(departure, arrival) {
    if (!departure || !arrival) return '--:--';
    
    try {
        const dep = new Date(departure);
        const arr = new Date(arrival);
        const diffMs = arr - dep;
        
        if (isNaN(diffMs)) return '--:--';
        
        const hours = Math.floor(diffMs / (1000 * 60 * 60));
        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
        
        return `${hours} س ${minutes} د`;
    } catch (e) {
        console.error('Error calculating duration:', e);
        return '--:--';
    }
}

// Format time from datetime string
function formatTime(datetime) {
    if (!datetime) return '--:--';
    const date = new Date(datetime);
    return date.toLocaleTimeString('ar-SA', { hour: '2-digit', minute: '2-digit' });
}

// Show no bookings message
function showNoBookingsMessage(container) {
    if (!container) return;
    
    container.innerHTML = `
        <div class="no-bookings-message">
            <div class="no-bookings-icon">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3>No Bookings Found</h3>
            <p>You don't have any active bookings at the moment. Explore available flights and book your next trip.</p>
            <a href="direct_flights.php" class="btn btn-primary btn-lg">
                <i class="fas fa-search me-2"></i> Browse Available Flights
            </a>
        </div>`;
}

// Show error message
function showErrorMessage(container, message) {
    if (!container) return;
    
    container.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <h3>Error</h3>
            <p>${message || 'An unexpected error occurred. Please try again later.'}</p>
            <button class="btn btn-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt"></i> إعادة تحميل الصفحة
            </button>
        </div>`;
}

// Format date for display
function formatDate(dateString) {
    if (!dateString) return '--/--/----';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-SA', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            weekday: 'long'
        });
    } catch (error) {
        console.error('Error formatting date:', error);
        return '--/--/----';
    }
}

// Handle phone number update
async function updatePhoneNumber(userId, newPhone) {
    try {
        const response = await fetch(API_ENDPOINTS.updateProfile, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: userId,
                phone_number: newPhone
            })
        });

        const data = await response.json();
        
        if (data.status === 'success') {
            // Update the phone number display without affecting the input field
            const phoneDisplay = document.getElementById('user-phone');
            if (phoneDisplay) {
                phoneDisplay.textContent = newPhone || "Not set";
            }
            
            // Phone Input Functionality
            document.addEventListener('DOMContentLoaded', function() {
                const phoneInput = document.getElementById('phone');
                const phoneContainer = document.querySelector('.phone-input-container');
                const phoneForm = document.getElementById('update-phone-form');
                const phoneDisplay = document.querySelector('.phone-display');
                const editPhoneBtn = document.getElementById('edit-phone-btn');
                const cancelEditBtn = document.getElementById('cancel-edit-phone');
                const savePhoneBtn = document.getElementById('save-phone-btn');

                // Initialize phone input mask
                if (phoneInput) {
                    phoneInput.addEventListener('input', function(e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                        e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2] + (x[3] ? '-' + x[3] : '');
                    });

                    // Add focus/blur effects
                    phoneInput.addEventListener('focus', function() {
                        phoneContainer.classList.add('focused');
                    });

                    phoneInput.addEventListener('blur', function() {
                        phoneContainer.classList.remove('focused');
                    });
                }

                // Toggle edit mode
                if (editPhoneBtn) {
                    editPhoneBtn.addEventListener('click', function() {
                        phoneDisplay.style.display = 'none';
                        phoneForm.style.display = 'block';
                        phoneInput.focus();
                    });
                }

                // Cancel edit
                if (cancelEditBtn) {
                    cancelEditBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        phoneForm.style.display = 'none';
                        phoneDisplay.style.display = 'flex';
                    });
                }


                // Form submission
                if (phoneForm) {
                    phoneForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Show loading state
                        const submitBtn = phoneForm.querySelector('button[type="submit"]');
                        const originalBtnText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                        submitBtn.disabled = true;

                        // Simulate API call (replace with actual API call)
                        setTimeout(() => {
                            // On success
                            const phoneNumber = phoneInput.value;
                            document.querySelector('.phone-number').textContent = phoneNumber || 'Not set';
                            
                            // Hide form and show display
                            phoneForm.style.display = 'none';
                            phoneDisplay.style.display = 'flex';
                            
                            // Show success message
                            showNotification('Phone number updated successfully!', 'success');
                            
                            // Reset button
                            submitBtn.innerHTML = originalBtnText;
                            submitBtn.disabled = false;
                        }, 1000);
                    });
                }
            });

            // Helper function to show notifications
            function showNotification(message, type = 'success') {
                const notification = document.createElement('div');
                notification.className = `notification ${type}`;
                notification.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                    <span>${message}</span>
                `;
                
                document.body.appendChild(notification);
                
                // Show notification
                setTimeout(() => {
                    notification.classList.add('show');
                }, 10);
                
                // Hide after 3 seconds
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        notification.remove();
                    }, 300);
                }, 3000);
            }
            
            showSuccess(data.message || 'Phone number updated successfully');
            
            // Update session storage with the new phone number
            if (newPhone) {
                const storageKey = `userPhone_${userId}`;
                sessionStorage.setItem(storageKey, newPhone);
            }
        } else {
            throw new Error(data.message || 'Failed to update phone number');
        }
    } catch (error) {
        console.error('Error updating phone number:', error);
        showError('Failed to update phone number: ' + error.message);
    }
}

// Handle booking cancellation
async function cancelBooking(bookingId, button) {
    if (!confirm('Are you sure you want to cancel this booking?')) {
        return;
    }
    
    const card = button.closest('.booking-card');
    if (card) {
        card.classList.add('cancelling');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelling...';
    }
    
    try {
        const response = await fetch(API_ENDPOINTS.cancelBooking, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ booking_id: bookingId })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            if (card) {
                // Update status badge
                const statusBadge = card.querySelector('.status-badge') || card.querySelector('.booking-status');
                if (statusBadge) {
                    statusBadge.innerHTML = `
                        <i class="fas fa-times-circle"></i> Cancelled`;
                }
                
                // Disable action buttons
                const actionsContainer = card.querySelector('.booking-actions-container');
                if (actionsContainer) {
                    actionsContainer.innerHTML = `
                        <button class="btn booking-btn-details" onclick="viewBookingDetails('${bookingId}')">
                            <i class="fas fa-eye"></i> View Details
                        </button>
                        <button class="btn booking-btn-disabled" disabled>
                            <i class="fas fa-ban"></i> Cancelled
                        </button>`;
                }
                
                // Update card status class
                card.classList.remove('booking-status-pending', 'booking-status-confirmed');
                card.classList.add('booking-status-cancelled');
            }
            showSuccess('Booking cancelled successfully');
            
            // Reload bookings after a short delay
            setTimeout(() => {
                const userId = sessionStorage.getItem('userId');
                if (userId) loadUserBookings(userId);
            }, 1000);
            
        } else {
            throw new Error(data.message || 'Failed to cancel booking');
        }
    } catch (error) {
        console.error('Error cancelling booking:', error);
        showError('Failed to cancel booking: ' + error.message);
        
        if (button) {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-times"></i> Cancel';
        }
    }
}

// View booking details
function viewBookingDetails(bookingId) {
    // You can implement a modal or redirect to a booking details page
    window.location.href = `booking_details.php?id=${bookingId}`;
}

// Handle profile picture upload
function setupProfilePictureUpload() {
    const profileAvatar = document.getElementById('profile-avatar');
    const fileInput = document.getElementById('profile-upload');
    const profileImage = document.getElementById('profile-image');

    // Load saved profile picture from localStorage
    const savedImage = localStorage.getItem('profileImage');
    if (savedImage) {
        profileImage.src = savedImage;
    }

    // Open file dialog when clicking on the avatar
    profileAvatar.addEventListener('click', () => {
        fileInput.click();
    });

    // Handle file selection
    fileInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(event) {
                // Show loading state
                profileImage.style.opacity = '0.7';
                
                // Simulate upload delay
                setTimeout(() => {
                    // Set the new image
                    profileImage.src = event.target.result;
                    profileImage.style.opacity = '1';
                    
                    // Save to localStorage
                    localStorage.setItem('profileImage', event.target.result);
                    
                    // Show success message
                    showSuccess('Profile picture updated successfully!');
                    
                    // Here you would typically upload the image to your server
                    // uploadProfilePicture(file);
                    
                }, 500);
            };
            
            reader.readAsDataURL(file);
        }
    });
}

// Function to upload profile picture to server (example)
function uploadProfilePicture(file) {
    const formData = new FormData();
    formData.append('profile_image', file);
    
    fetch('upload_profile_picture.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Profile picture updated successfully!');
        } else {
            throw new Error(data.message || 'Failed to upload profile picture');
        }
    })
    .catch(error => {
        console.error('Error uploading profile picture:', error);
        showError('Failed to update profile picture. Please try again.');
    });
}

// Initialize the profile page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize profile picture upload
    setupProfilePictureUpload();
    
    // Initialize phone number functionality
    setupPhoneNumberHandling();
    
    // Get user ID from session storage
    const userId = sessionStorage.getItem('userId');
    
    // Redirect to login if not authenticated
    if (!userId) {
        window.location.href = 'login.html';
        return;
    }
    
    // Don't restore phone number to input field on page load
    // Only update the display if we have a phone number from the server
    // This prevents the input field from being populated on refresh
    
    // Check for admin role
    const userRole = sessionStorage.getItem('userRole') || localStorage.getItem('userRole');
    const adminBtn = document.getElementById('admin-dashboard-btn');
    if (userRole === 'admin' && adminBtn) {
        adminBtn.style.display = 'inline-block';
    }
    
    // Setup event listeners
    setupEventListeners(userId);
    
    // Load user profile and bookings
    loadUserProfile(userId);
});

// Setup event listeners
function setupEventListeners(userId) {
    // Phone number form submission
    const phoneForm = document.getElementById('update-phone-form');
    if (phoneForm) {
        phoneForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const phoneInput = document.getElementById('phone');
            if (phoneInput && phoneInput.value.trim()) {
                updatePhoneNumber(userId, phoneInput.value.trim());
            }
        });
    }
    
    // Logout button
    const logoutBtn = document.getElementById('logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            // Clear all stored data for this user
            const userId = sessionStorage.getItem('userId');
            if (userId) {
                localStorage.removeItem(`userPhone_${userId}`);
                sessionStorage.removeItem(`userPhone_${userId}`);
            }
            sessionStorage.clear();
            
            // Redirect to login page
            window.location.href = 'login.html';
        });
    }
    
    // View bookings button
    const viewBookingsBtn = document.getElementById('view-bookings-btn');
    if (viewBookingsBtn) {
        viewBookingsBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loadUserBookings(userId);
        });
    }
}

// Setup phone number handling
function setupPhoneNumberHandling() {
    const phoneForm = document.getElementById('update-phone-form');
    const phoneDisplay = document.querySelector('.phone-display');
    const editPhoneBtn = document.getElementById('edit-phone-btn');
    const cancelEditBtn = document.getElementById('cancel-edit-phone');
    const phoneInput = document.getElementById('phone');
    const userId = document.body.getAttribute('data-user-id');

    // Toggle edit mode
    if (editPhoneBtn && phoneForm && phoneDisplay) {
        editPhoneBtn.addEventListener('click', function() {
            phoneDisplay.style.display = 'none';
            phoneForm.style.display = 'block';
            phoneInput.focus();
        });
    }

    // Cancel edit
    if (cancelEditBtn && phoneForm && phoneDisplay) {
        cancelEditBtn.addEventListener('click', function(e) {
            e.preventDefault();
            phoneForm.style.display = 'none';
            phoneDisplay.style.display = 'flex';
            // Reset input value to current phone number
            const currentPhone = document.getElementById('user-phone-display')?.textContent;
            if (phoneInput && currentPhone && currentPhone !== 'Not set') {
                phoneInput.value = currentPhone;
            } else {
                phoneInput.value = '';
            }
        });
    }
    
    // Handle form submission
    if (phoneForm && userId) {
        phoneForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let phoneNumber = phoneInput?.value.trim();
            if (!phoneNumber) {
                showError('الرجاء إدخال رقم هاتف صحيح');
                return;
            }
            
            // Ensure it's a valid Egyptian mobile number (starts with 1 and is 10 digits)
            if (!/^1\d{9}$/.test(phoneNumber)) {
                showError('الرجاء إدخال رقم هاتف مصري صحيح (يبدأ بـ 1 ويتكون من 10 أرقام)');
                phoneInput.focus();
                return;
            }
            
            // Show loading state
            const submitBtn = phoneForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn?.innerHTML;
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                submitBtn.disabled = true;
            }
            
            // Call the update function
            updatePhoneNumber(userId, phoneNumber)
                .then(() => {
                    // Update the display with full international format
                    updatePhoneDisplay(`+20 ${phoneNumber}`, userId);
                    
                    // Hide form and show display
                    if (phoneForm && phoneDisplay) {
                        phoneForm.style.display = 'none';
                        phoneDisplay.style.display = 'flex';
                    }
                    
                    // Show success message
                    showSuccess('Phone number updated successfully!');
                })
                .catch(error => {
                    console.error('Error updating phone number:', error);
                    showError(error.message || 'Failed to update phone number');
                })
                .finally(() => {
                    // Reset button
                    if (submitBtn) {
                        submitBtn.innerHTML = originalBtnText || '<i class="fas fa-save"></i> Save Changes';
                        submitBtn.disabled = false;
                    }
                });
        });
    }
    
    // Initialize phone input for Egyptian numbers
    if (phoneInput) {
        // Only allow numbers and limit to 10 digits
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            // Ensure it starts with 1 (Egyptian mobile numbers start with 1)
            if (value.length > 0 && value[0] !== '1') {
                value = '1' + value;
            }
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
            
            // Update the display in real-time
            const display = document.getElementById('user-phone-display');
            if (display) {
                display.textContent = value ? `+20 ${value}` : 'Not set';
            }
        });
        
        // Add paste handler to clean up pasted content
        phoneInput.addEventListener('paste', function(e) {
            e.preventDefault();
            let paste = (e.clipboardData || window.clipboardData).getData('text');
            let numbers = paste.replace(/\D/g, '');
            // Remove +20 if pasted
            if (numbers.startsWith('20')) {
                numbers = numbers.substring(2);
            } else if (numbers.startsWith('0020')) {
                numbers = numbers.substring(4);
            }
            // Ensure it starts with 1
            if (numbers.length > 0 && numbers[0] !== '1') {
                numbers = '1' + numbers;
            }
            // Limit to 10 digits
            if (numbers.length > 10) {
                numbers = numbers.substring(0, 10);
            }
            this.value = numbers;
            
            // Trigger input event to update display
            this.dispatchEvent(new Event('input'));
        });
    }
}

// Initialize global functions for button clicks
window.cancelBooking = cancelBooking;
window.viewBookingDetails = viewBookingDetails;
