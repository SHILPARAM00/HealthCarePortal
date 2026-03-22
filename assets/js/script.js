// Fixed JS for Healthcare Portal Frontend
document.addEventListener("DOMContentLoaded", function() {
    console.log("Healthcare Portal Frontend Loaded");

    // Highlight current nav link
    const navLinks = document.querySelectorAll("nav a");
    navLinks.forEach(link => {
        if (link.href === window.location.href) {
            link.style.textDecoration = "underline";
            link.style.fontWeight = "bold";
            link.style.color = "#007bff"; // optional style
        }
    });

    // Optional: simple client-side form validation example (does NOT prevent login/register)
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            // Only run validation if needed
            // Example: check required fields are not empty
            const requiredFields = form.querySelectorAll("[required]");
            let valid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.style.border = "1px solid red";
                } else {
                    field.style.border = "";
                }
            });

            if (!valid) {
                e.preventDefault(); // stop submission only if validation fails
                alert("Please fill all required fields correctly.");
            }

            // If all fields valid, form submits normally to backend (PHP)
        });
    });
});