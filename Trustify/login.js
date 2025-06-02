document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('login-form');
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        
        if (username.trim() === '' || password.trim() === '') {
            alert('Please fill in both fields.');
            return;
        }
        
        const formData = new FormData(loginForm);
        
        fetch('php/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes('Login successful!')) {
                alert('Login successful!');
                window.location.href = 'dashboard.php';
            } else {
                alert('Invalid username or password.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
