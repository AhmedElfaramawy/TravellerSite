document.addEventListener("DOMContentLoaded", function () {
  const registerForm = document.getElementById("register-form");

  registerForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new FormData(registerForm);

      fetch(API_ENDPOINTS.register, {
          method: "POST",
          body: formData,
          headers: {
            "Accept": "application/json"
        }
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === "success") {
              alert(data.message);
              window.location.href = "login.html"; // تحويل المستخدم إلى صفحة تسجيل الدخول
          } else {
              alert(data.message);
          }
      })
      .catch(error => console.error("An error occurred:", error));
  });
});

function reg(){
    document.querySelector('.loader-container').classList.add('active');
  }
  
function register(){
  setTimeout(reg, 3000);
}
register();

