document.getElementById("search-form").addEventListener("submit", function (event) {
    event.preventDefault(); // منع إعادة تحميل الصفحة

    const destination = document.getElementById("destination").value;
    const departureDate = document.getElementById("departure-date").value;
    const numTravelers = document.getElementById("num-travelers").value;

    if (!destination || !departureDate || !numTravelers) {
        alert("يرجى ملء جميع الحقول!");
        return;
    }

    // تخزين البيانات في sessionStorage لاستخدامها في flights.html
    sessionStorage.setItem("searchData", JSON.stringify({
        destination: destination,
        date: departureDate,
        travelers: numTravelers
    }));

    // التوجيه إلى flights.html
    window.location.href = "flights.html";
});

// Direct functions for Book Now buttons
function bookNowHeader() {
    console.log("Header Book Now clicked");
    // Store default destination data
    sessionStorage.setItem("travelChoice", JSON.stringify({
        destination: "Popular Destination",
        type: "header-selection"
    }));
    window.location.href = "flights_available.html";
}

function bookNowHome() {
    console.log("Home Book Now clicked");
    // Store default destination data
    sessionStorage.setItem("travelChoice", JSON.stringify({
        destination: "Featured Destination",
        type: "home-selection"
    }));
    window.location.href = "flights_available.html";
}


let navbar = document.querySelector('.header .navbar');

document.querySelector('#menu-btn').onclick = () => {
    navbar.classList.toggle('active');
};

window.onscroll = () => {
    navbar.classList.remove('active');
}

document.querySelectorAll('.about .video-container .controls .control-btn').forEach(btn=> {
    btn.onclick = () => {
        let src = btn.getAttribute('data-src');
        document.querySelector('.about .video-container .video').src = src;
    }
})