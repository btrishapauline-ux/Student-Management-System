// Admin Login Script
document.addEventListener('DOMContentLoaded', function() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    const togglePasswordBtn = document.getElementById('toggleAdminPassword');
    const passwordInput = document.getElementById('adminPassword');
    const successMessage = document.getElementById('adminSuccessMessage');
    
    // Toggle password visibility
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = this.querySelector('i');
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    }
    
    // Form validation and submission
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset previous errors
            clearErrors();
            
            // Get form values
            const username = document.getElementById('adminUsername').value.trim();
            const password = document.getElementById('adminPassword').value.trim();
            const securityCode = document.getElementById('adminCode').value.trim();
            
            let isValid = true;
            
            // Validate username
            if (!username) {
                showError('usernameError', 'Admin username is required');
                isValid = false;
            } else if (username.length < 3) {
                showError('usernameError', 'Username must be at least 3 characters');
                isValid = false;
            }
            
            // Validate password
            if (!password) {
                showError('passwordError', 'Password is required');
                isValid = false;
            } else if (password.length < 6) {
                showError('passwordError', 'Password must be at least 6 characters');
                isValid = false;
            }
            
            // Validate security code if provided
            if (securityCode && securityCode.length < 4) {
                showError('codeError', 'Security code must be at least 4 characters');
                isValid = false;
            }
            
            // If form is valid, simulate login
            if (isValid) {
                simulateAdminLogin();
            }
        });
    }
    
    // Social login buttons
    const socialButtons = document.querySelectorAll('.admin-social-btn');
    socialButtons.forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.querySelector('span').textContent;
            alert(`Simulating ${provider} authentication for admin login.`);
            
            // Simulate successful authentication
            setTimeout(() => {
                showSuccessMessage();
            }, 1500);
        });
    });
    
    // Helper functions
    function showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }
    
    function clearErrors() {
        const errorElements = document.querySelectorAll('.error-message');
        errorElements.forEach(element => {
            element.textContent = '';
            element.style.display = 'none';
        });
    }
    
    function simulateAdminLogin() {
        // Show loading state on submit button
        const submitBtn = adminLoginForm.querySelector('.admin-submit-btn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
        submitBtn.disabled = true;
        
        // Simulate API call delay
        setTimeout(() => {
            showSuccessMessage();
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    }
    
    function showSuccessMessage() {
        // Hide form, show success message
        adminLoginForm.style.display = 'none';
        successMessage.style.display = 'block';
        
        // Simulate redirect to admin dashboard
        setTimeout(() => {
            alert('Redirecting to admin dashboard...');
            // In a real application: window.location.href = 'admin-dashboard.html';
        }, 3000);
    }
    
    // Security session checkbox
    const secureSessionCheckbox = document.getElementById('secureSession');
    if (secureSessionCheckbox) {
        secureSessionCheckbox.addEventListener('change', function() {
            if (this.checked) {
                console.log('Secure session enabled');
            } else {
                console.log('Secure session disabled');
            }
        });
    }
    
    // Add visual feedback for input focus
    const inputs = document.querySelectorAll('.admin-login-form .input-with-icon input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Back link functionality
    const backLink = document.querySelector('.back-link a');
    if (backLink) {
        backLink.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'student-login.html';
        });
    }
});


  document.addEventListener('DOMContentLoaded', function() {
    // Force back link to work
    const backLink = document.querySelector('.back-link a');
    if (backLink) {
      backLink.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = 'LogIn.html';
      });
    }
    
    // Also make sure no other JavaScript is interfering
    document.querySelectorAll('a[href="LogIn.html"]').forEach(link => {
      link.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
      });
    });
  });