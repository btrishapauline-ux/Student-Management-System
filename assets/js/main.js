/**
* Template Name: Arsha
* Template URL: https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/
* Updated: Feb 22 2025 with Bootstrap v5.3.3
* Author: BootstrapMade.com
* License: https://bootstrapmade.com/license/
*/

(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  if (mobileNavToggleBtn) {
    mobileNavToggleBtn.addEventListener('click', mobileNavToogle);
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      this.parentNode.classList.toggle('active');
      this.parentNode.nextElementSibling.classList.toggle('dropdown-active');
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');
    });
  });

  /**
   * Animate the skills items on reveal
   */
  let skillsAnimation = document.querySelectorAll('.skills-animation');
  skillsAnimation.forEach((item) => {
    new Waypoint({
      element: item,
      offset: '80%',
      handler: function(direction) {
        let progress = item.querySelectorAll('.progress .progress-bar');
        progress.forEach(el => {
          el.style.width = el.getAttribute('aria-valuenow') + '%';
        });
      }
    });
  });

  /**
   * Init isotope layout and filters
   */
  document.querySelectorAll('.isotope-layout').forEach(function(isotopeItem) {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), function() {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort
      });
    });

    isotopeItem.querySelectorAll('.isotope-filters li').forEach(function(filters) {
      filters.addEventListener('click', function() {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        if (typeof aosInit === 'function') {
          aosInit();
        }
      }, false);
    });

  });

  /**
   * Correct scrolling position upon page load for URLs containing hash links.
   */
  window.addEventListener('load', function(e) {
    if (window.location.hash) {
      if (document.querySelector(window.location.hash)) {
        setTimeout(() => {
          let section = document.querySelector(window.location.hash);
          let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
          window.scrollTo({
            top: section.offsetTop - parseInt(scrollMarginTop),
            behavior: 'smooth'
          });
        }, 100);
      }
    }
  });

  /**
   * Navmenu Scrollspy
   */
  let navmenulinks = document.querySelectorAll('.navmenu a');

  function navmenuScrollspy() {
    navmenulinks.forEach(navmenulink => {
      if (!navmenulink.hash) return;
      let section = document.querySelector(navmenulink.hash);
      if (!section) return;
      let position = window.scrollY + 200;
      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));
        navmenulink.classList.add('active');
      } else {
        navmenulink.classList.remove('active');
      }
    })
  }
  window.addEventListener('load', navmenuScrollspy);
  document.addEventListener('scroll', navmenuScrollspy);

})();

// ============================================
// LOGIN OPTIONS MODAL FUNCTIONALITY
// ============================================

// Initialize login options modal
function initLoginOptionsModal() {
    // Get modal elements
    const loginOptionsModal = document.getElementById('loginOptionsModal');
    const loginTriggerBtn = document.getElementById('loginTriggerBtn');
    const heroLoginTriggerBtn = document.getElementById('heroLoginTriggerBtn');
    const loginModalCloseBtn = document.getElementById('loginModalCloseBtn');
    
    // Function to open modal
    function openLoginModal() {
        loginOptionsModal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
    
    // Function to close modal
    function closeLoginModal() {
        loginOptionsModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }
    
    // Add event listeners to trigger buttons
    if (loginTriggerBtn) {
        loginTriggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openLoginModal();
        });
    }
    
    if (heroLoginTriggerBtn) {
        heroLoginTriggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            openLoginModal();
        });
    }
    
    // Close modal when close button is clicked
    if (loginModalCloseBtn) {
        loginModalCloseBtn.addEventListener('click', closeLoginModal);
    }
    
    // Close modal when clicking outside the modal content
    if (loginOptionsModal) {
        loginOptionsModal.addEventListener('click', function(e) {
            if (e.target === loginOptionsModal) {
                closeLoginModal();
            }
        });
    }
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && loginOptionsModal.style.display === 'block') {
            closeLoginModal();
        }
    });
    
    // Add hover effects to login option cards
    const loginCards = document.querySelectorAll('.login-option-card');
    loginCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px)';
            this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Add click effects to login buttons
    const loginButtons = document.querySelectorAll('.login-select-btn');
    loginButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const card = this.closest('.login-option-card');
            const optionType = card.classList[1].replace('-login', '');
            
            // Add click animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 200);
            
            // Store the selected login type (optional, for analytics or tracking)
            localStorage.setItem('lastLoginType', optionType);
            
            console.log(`Selected login type: ${optionType}`);
        });
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initLoginOptionsModal();
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('signupForm');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const emailInput = document.getElementById('email');
    const firstNameInput = document.getElementById('firstName');
    const lastNameInput = document.getElementById('lastName');
    const studentIdInput = document.getElementById('studentId');
    const courseSelect = document.getElementById('course');
    const yearLevelSelect = document.getElementById('yearLevel');
    const termsCheckbox = document.getElementById('terms');

    // If we are not on the signup page, skip the validation logic to avoid null errors
    if (!form || !passwordInput || !confirmPasswordInput || !emailInput) {
        return;
    }

    // Validate email format (basic university email check)
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email) && email.includes('.edu');
    }

    // Validate password strength
    function validatePassword(password) {
        const minLength = 8;
        const hasNumber = /\d/;
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/;
        
        return password.length >= minLength && 
               hasNumber.test(password) && 
               hasSpecialChar.test(password);
    }

    // Show error on an input field
    function showError(input, message) {
        input.classList.add('error');
        let errorElement = input.nextElementSibling;
        
        if (!errorElement || !errorElement.classList.contains('error-message')) {
            errorElement = document.createElement('div');
            errorElement.className = 'error-message';
            input.parentNode.insertBefore(errorElement, input.nextSibling);
        }
        
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }

    // Remove error from an input field
    function removeError(input) {
        input.classList.remove('error');
        const errorElement = input.nextElementSibling;
        if (errorElement && errorElement.classList.contains('error-message')) {
            errorElement.style.display = 'none';
        }
    }

    // Real-time validation for email
    emailInput.addEventListener('blur', function() {
        if (!validateEmail(this.value)) {
            showError(this, 'Please enter a valid university email address (.edu)');
        } else {
            removeError(this);
        }
    });

    // Real-time validation for password
    passwordInput.addEventListener('input', function() {
        if (this.value && !validatePassword(this.value)) {
            showError(this, 'Password must be 8+ characters with a number and special character.');
        } else {
            removeError(this);
        }
        
        // Check password match
        if (confirmPasswordInput.value) {
            if (this.value !== confirmPasswordInput.value) {
                showError(confirmPasswordInput, 'Passwords do not match');
            } else {
                removeError(confirmPasswordInput);
            }
        }
    });

    // Real-time validation for confirm password
    confirmPasswordInput.addEventListener('input', function() {
        if (passwordInput.value !== this.value) {
            showError(this, 'Passwords do not match');
        } else {
            removeError(this);
        }
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true;

        // Reset all errors
        document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        document.querySelectorAll('.error-message').forEach(el => el.style.display = 'none');

        // Validate each field (mapped to actual signup.php inputs)
        if (firstNameInput && !firstNameInput.value.trim()) {
            showError(firstNameInput, 'First name is required');
            isValid = false;
        }

        if (lastNameInput && !lastNameInput.value.trim()) {
            showError(lastNameInput, 'Last name is required');
            isValid = false;
        }

        if (!validateEmail(emailInput.value)) {
            showError(emailInput, 'Valid university email is required');
            isValid = false;
        }

        if (studentIdInput && !studentIdInput.value.trim()) {
            showError(studentIdInput, 'Student ID is required');
            isValid = false;
        }

        if (courseSelect && !courseSelect.value) {
            showError(courseSelect, 'Please select your course');
            isValid = false;
        }

        if (yearLevelSelect && !yearLevelSelect.value) {
            showError(yearLevelSelect, 'Please select your year level');
            isValid = false;
        }

        if (!validatePassword(passwordInput.value)) {
            showError(passwordInput, 'Password does not meet requirements');
            isValid = false;
        }

        if (passwordInput.value !== confirmPasswordInput.value) {
            showError(confirmPasswordInput, 'Passwords do not match');
            isValid = false;
        }

        if (termsCheckbox && !termsCheckbox.checked) {
            alert('You must agree to the Terms of Service to continue.');
            isValid = false;
        }

        // If valid, simulate submission
        if (isValid) {
            const submitBtn = form.querySelector('.submit-btn');
            submitBtn.textContent = 'Creating Account...';
            submitBtn.disabled = true;

            // Simulate API call
            setTimeout(() => {
                alert('Account created successfully! Welcome to the student community.');
                form.reset();
                submitBtn.textContent = 'Create Account';
                submitBtn.disabled = false;
            }, 1500);
        }
    });

    // Auto-remove error when user starts typing
    form.querySelectorAll('input, select').forEach(input => {
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                removeError(this);
            }
        });
    });
});