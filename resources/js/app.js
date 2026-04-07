// Kesfet Lab - Ana JavaScript
console.log("Kesfet Lab uygulaması yüklendi");

// Temel fonksiyonlar
function showAlert(message, type = "info") {
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector(".container");
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}

// Form validasyonu
function validateForm(form) {
    const inputs = form.querySelectorAll("input[required], select[required]");
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = "red";
            isValid = false;
        } else {
            input.style.borderColor = "";
        }
    });
    
    return isValid;
}

// Sayfa yüklendiğinde çalışacak kodlar
document.addEventListener("DOMContentLoaded", function() {
    console.log("Sayfa yüklendi");
    
    // Form validasyonu
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showAlert("Lütfen tüm gerekli alanları doldurun.", "danger");
            }
        });
    });
});