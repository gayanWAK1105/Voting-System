document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function (e) {
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
            if (!/[A-Z]/.test(password)) {
                errors.push('Password must contain at least one uppercase letter.');
            }
            if (!/[a-z]/.test(password)) {
                errors.push('Password must contain at least one lowercase letter.');
            }
            if (!/[^A-Za-z0-9]/.test(password)) {
                errors.push('Password must contain at least one special character.');
            }
            if (password !== confirm) {
                errors.push('Passwords do not match.');
            }

            var errorContainer = document.getElementById('js-errors');

            if (errors.length > 0) {
                e.preventDefault();

                // Hide any server-side error boxes to prevent duplicate boxes
                var serverErrors = document.querySelectorAll('.auth-box .alert-error:not(#js-errors)');
                serverErrors.forEach(function (el) {
                    el.style.display = 'none';
                });

                if (errorContainer) {
                    errorContainer.innerHTML = '';
                    errors.forEach(function (error) {
                        var p = document.createElement('p');
                        p.textContent = error;
                        errorContainer.appendChild(p);
                    });
                    errorContainer.style.display = 'block';
                    errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            } else {
                if (errorContainer) {
                    errorContainer.style.display = 'none';
                }
            }
        });
    }
});
