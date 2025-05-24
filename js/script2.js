document.addEventListener("DOMContentLoaded", function () {
    const userId = sessionStorage.getItem("userId");

    if (userId) {
        document.getElementById("profile-link").style.display = "inline";
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const userId = sessionStorage.getItem("userId"); // ✅ جلب `userId` من `sessionStorage`
    const joinUsBtn = document.getElementById("join-us-btn");

    if (userId && joinUsBtn) {
        joinUsBtn.style.display = "none"; // ✅ إخفاء الزر إذا كان المستخدم مسجل دخول
    }
});
