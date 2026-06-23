document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            var username = document.getElementById('username').value.trim();
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value;
            var confirm = document.getElementById('confirm_password').value;
            var errors = [];

            if (username.length < 3) {
                errors.push('Username must be at least 3 characters.');
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                errors.push('Please enter a valid email address.');
            }
            if (password.length < 6) {
                errors.push('Password must be at least 6 characters.');
            }
            if (!/[a-zA-Z]/.test(password)) {
                errors.push('Password must contain at least one letter.');
            }
            if (!/[A-Z]/.test(password)) {
                errors.push('Password must contain at least one uppercase letter.');
            }
            if (!/[^a-zA-Z0-9\s]/.test(password)) {
                errors.push('Password must contain at least one special character.');
            }
            if (password !== confirm) {
                errors.push('Passwords do not match.');
            }

            if (errors.length > 0) {
                e.preventDefault();
                alert(errors.join('\n'));
            }
        });
    }
});
