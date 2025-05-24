// Configuration file for the Traveller Site
// This file contains common configuration variables used across the site

// Base URL for API calls - automatically detects the site root
const BASE_URL = window.location.origin + '/TravellerSite/';

// API endpoints
const API_ENDPOINTS = {
    login: BASE_URL + 'php/login.php',
    register: BASE_URL + 'php/register.php',
    profile: BASE_URL + 'php/profile.php',
    updateProfile: BASE_URL + 'php/update_profile.php',
    booking: BASE_URL + 'php/booking.php', // تم نقل الملف إلى المجلد الرئيسي لتسهيل الوصول إليه
    flights: BASE_URL + 'php/flights.php',
    flightsAvailable: BASE_URL + 'php/direct_flights.php', // تم نقل الملف إلى المجلد الرئيسي
    payment: BASE_URL + 'php/payment.php',
    contact: BASE_URL + 'php/contact.php',
    logout: BASE_URL + 'php/logout.php',
    getFlight: BASE_URL + 'php/get_flight.php',
    cancelBooking: BASE_URL + 'php/cancel_booking.php',
};
