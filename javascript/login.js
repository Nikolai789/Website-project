
// function of this function is that we display a specific form
function showForm(formId) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(formId).classList.add("active")
    
}

document.addEventListener("DOMContentLoaded", () => {
    const successMessage = document.querySelector(".success-message");

    if (!successMessage) {
        return;
    }

    setTimeout(() => {
        successMessage.classList.add("hide-message");

        setTimeout(() => {
            successMessage.remove();
        }, 350);
    }, 3000);
});