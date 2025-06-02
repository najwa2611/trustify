// Get the query parameter from the URL
const urlParams = new URLSearchParams(window.location.search);
const service = urlParams.get('service');

// Populate the service input field
if (service) {
    document.getElementById('service').value = service;
}

// Form validation
document.getElementById('service-form').addEventListener('submit', function(event) {
    const name = document.getElementById('name').value;
    const email = document.getElementById('email').value;
    if (!name || !email) {
        alert('Please fill in all required fields.');
        event.preventDefault();
    }
});
