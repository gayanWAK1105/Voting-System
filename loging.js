// Form validation and submission
document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();
    
    // Basic validation
    if (!email) {
        alert('Please enter your email address.');
        return;
    }
    
    if (!password) {
        alert('Please enter your password.');
        return;
    }
    
    // Email format validation
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert('Please enter a valid email address.');
        return;
    }
    
    // Password length validation
    if (password.length < 6) {
        alert('Password must be at least 6 characters long.');
        return;
    }
    
    // If all validations pass
    console.log('Login attempt with:', { email, password });
    alert('Login successful! Redirecting to home page...');
    
    // Redirect to home page
    window.location.href = 'home.html';
    
    // In a real application, you would send this data to a server
    // Example: 
    // fetch('/api/login', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ email, password })
    // })
});

// Real-time input validation
document.getElementById('email').addEventListener('blur', function() {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (this.value && !emailPattern.test(this.value)) {
        this.style.borderColor = '#e74c3c';
    } else {
        this.style.borderColor = '#e0e0e0';
    }
});

document.getElementById('password').addEventListener('input', function() {
    if (this.value.length < 6 && this.value.length > 0) {
        this.style.borderColor = '#f39c12';
    } else if (this.value.length >= 6) {
        this.style.borderColor = '#27ae60';
    } else {
        this.style.borderColor = '#e0e0e0';
    }
});
