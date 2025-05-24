document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("login-form");

    loginForm.addEventListener("submit", function (event) {
        event.preventDefault(); // ✅ منع إعادة تحميل الصفحة

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value.trim();

        if (!email || !password) {
            alert("❌ يرجى إدخال البريد الإلكتروني وكلمة المرور.");
            return;
        }

        // ✅ إرسال البيانات بصيغة JSON
        fetch(API_ENDPOINTS.login, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(response => response.json())
        .then(data => {
            console.log("📦 استجابة login.php:", data);

            if (data.status === "success" && data.user_id) {
                // تخزين البيانات في sessionStorage للجلسة الحالية
                sessionStorage.setItem("userId", data.user_id);
                sessionStorage.setItem("userRole", data.role || "passenger");
                sessionStorage.setItem("userName", data.name || "");
                sessionStorage.setItem("userEmail", data.email || "");
                
                // Store data in localStorage for session persistence
                localStorage.setItem("userId", data.user_id);
                localStorage.setItem("userRole", data.role || "passenger");
                localStorage.setItem("userName", data.name || "");
                localStorage.setItem("userEmail", data.email || "");

                console.log("✅ userId المخزن:", sessionStorage.getItem("userId"));

                window.location.href = "index.html"; // ✅ إعادة التوجيه بعد تسجيل الدخول
            } else {
                alert("❌ Login failed: " + (data.message || "An unknown error occurred."));
            }
        })
        .catch(error => console.error("❌ Error during login:", error));
    });
});
