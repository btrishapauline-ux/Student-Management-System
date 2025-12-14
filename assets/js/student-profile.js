// Student Profile JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Profile Dropdown Functionality
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    
    // Toggle profile dropdown
    if (profileTrigger) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (profileTrigger && !profileTrigger.contains(e.target) && 
            profileDropdown && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });
    
    // Avatar Change Functionality
    const changeAvatarBtn = document.getElementById('changeAvatarBtn');
    const avatarUploadModal = new bootstrap.Modal(document.getElementById('avatarUploadModal'));
    const avatarFileInput = document.getElementById('avatarFileInput');
    const avatarPreview = document.getElementById('avatarPreview');
    const useDefaultBtn = document.getElementById('useDefaultBtn');
    const saveAvatarBtn = document.getElementById('saveAvatarBtn');
    
    // Avatar elements
    const mainProfileAvatar = document.getElementById('mainProfileAvatar');
    const profileAvatar = document.getElementById('profileAvatar');
    const dropdownAvatar = document.getElementById('dropdownAvatar');
    
    let selectedAvatarFile = null;
    
    // Open avatar upload modal
    if (changeAvatarBtn) {
        changeAvatarBtn.addEventListener('click', function() {
            if (avatarPreview && mainProfileAvatar) {
                avatarPreview.src = mainProfileAvatar.src;
            }
            selectedAvatarFile = null;
            if (saveAvatarBtn) {
                saveAvatarBtn.disabled = false;
                saveAvatarBtn.innerHTML = 'Save Changes';
            }
            if (avatarFileInput) {
                avatarFileInput.value = '';
            }
            avatarUploadModal.show();
        });
    }
    
    // Handle file selection
    if (avatarFileInput) {
        avatarFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size should be less than 5MB');
                    avatarFileInput.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.match('image.*')) {
                    alert('Please select an image file');
                    avatarFileInput.value = '';
                    return;
                }
                
                selectedAvatarFile = file;
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (avatarPreview) {
                        avatarPreview.src = e.target.result;
                    }
                    if (saveAvatarBtn) {
                        saveAvatarBtn.disabled = false;
                        saveAvatarBtn.innerHTML = 'Save Changes';
                    }
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Use default avatar
    if (useDefaultBtn) {
        useDefaultBtn.addEventListener('click', function() {
            avatarPreview.src = 'assets/img/default-avatar.jpg';
            selectedAvatarFile = null;
            saveAvatarBtn.disabled = false;
        });
    }
    
    // Save avatar - ensure form submits properly
    // The form will submit and PHP will handle the upload
    const avatarUploadForm = document.getElementById('avatarUploadForm');
    if (avatarUploadForm && saveAvatarBtn) {
        // Don't prevent default - let form submit naturally
        // Just ensure file is selected
        avatarUploadForm.addEventListener('submit', function(e) {
            if (!avatarFileInput || !avatarFileInput.files || !avatarFileInput.files[0]) {
                e.preventDefault();
                alert('Please select an image file first.');
                return false;
            }
            // Show loading state
            saveAvatarBtn.disabled = true;
            saveAvatarBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
            // Form will submit normally, PHP handles the rest
        });
    }
    
    // Edit Personal Information
    const editPersonalBtn = document.getElementById('editPersonalBtn');
    const editInfoModal = new bootstrap.Modal(document.getElementById('editInfoModal'));
    const editModalTitle = document.getElementById('editModalTitle');
    const editFullName = document.getElementById('editFullName');
    const editDob = document.getElementById('editDob');
    const editGender = document.getElementById('editGender');
    const editAddress = document.getElementById('editAddress');
    const saveInfoBtn = document.getElementById('saveInfoBtn');
    
    // Personal info elements
    const infoFullName = document.getElementById('infoFullName');
    const infoDob = document.getElementById('infoDob');
    const infoGender = document.getElementById('infoGender');
    const infoAddress = document.getElementById('infoAddress');
    const mainProfileName = document.getElementById('mainProfileName');
    const profileName = document.getElementById('profileName');
    const dropdownName = document.getElementById('dropdownName');
    
    // Edit personal info
    if (editPersonalBtn) {
        editPersonalBtn.addEventListener('click', function() {
            editModalTitle.textContent = 'Edit Personal Information';
            
            // Set current values
            editFullName.value = infoFullName.textContent;
            editDob.value = formatDateForInput(infoDob.textContent);
            editGender.value = infoGender.textContent;
            editAddress.value = infoAddress.textContent;
            
            editInfoModal.show();
        });
    }
    
    // Edit Contact Information
    const editContactBtn = document.getElementById('editContactBtn');
    const infoEmail = document.getElementById('infoEmail');
    const infoPhone = document.getElementById('infoPhone');
    const infoEmergencyContact = document.getElementById('infoEmergencyContact');
    const infoEmergencyPhone = document.getElementById('infoEmergencyPhone');
    
    if (editContactBtn) {
        editContactBtn.addEventListener('click', function() {
            editModalTitle.textContent = 'Edit Contact Information';
            
            // Clear form and rebuild for contact info
            const form = document.getElementById('editInfoForm');
            form.innerHTML = `
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="editEmail" value="${infoEmail.textContent}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="editPhone" value="${infoPhone.textContent}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Emergency Contact Name</label>
                    <input type="text" class="form-control" id="editEmergencyContact" value="${infoEmergencyContact.textContent}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Emergency Contact Phone</label>
                    <input type="tel" class="form-control" id="editEmergencyPhone" value="${infoEmergencyPhone.textContent}">
                </div>
            `;
            
            editInfoModal.show();
        });
    }
    
    // Save information
    if (saveInfoBtn) {
        saveInfoBtn.addEventListener('click', function() {
            if (editModalTitle.textContent.includes('Personal')) {
                // Save personal info
                infoFullName.textContent = editFullName.value;
                infoDob.textContent = formatDateForDisplay(editDob.value);
                infoGender.textContent = editGender.value;
                infoAddress.textContent = editAddress.value;
                
                // Update header name
                mainProfileName.textContent = editFullName.value;
                profileName.textContent = editFullName.value;
                dropdownName.textContent = editFullName.value;
                
                showToast('Personal information updated successfully!', 'success');
            } else {
                // Save contact info
                const editEmail = document.getElementById('editEmail');
                const editPhone = document.getElementById('editPhone');
                const editEmergencyContact = document.getElementById('editEmergencyContact');
                const editEmergencyPhone = document.getElementById('editEmergencyPhone');
                
                if (editEmail) infoEmail.textContent = editEmail.value;
                if (editPhone) infoPhone.textContent = editPhone.value;
                if (editEmergencyContact) infoEmergencyContact.textContent = editEmergencyContact.value;
                if (editEmergencyPhone) infoEmergencyPhone.textContent = editEmergencyPhone.value;
                
                // Restore form for next use
                restoreEditForm();
                showToast('Contact information updated successfully!', 'success');
            }
            
            editInfoModal.hide();
        });
    }
    
    // Restore edit form to personal info version
    function restoreEditForm() {
        const form = document.getElementById('editInfoForm');
        form.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" id="editFullName">
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="editDob">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Gender</label>
                        <select class="form-select" id="editGender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control" id="editAddress" rows="2"></textarea>
            </div>
        `;
    }
    
    // Print Profile
    const printProfileBtn = document.getElementById('printProfileBtn');
    if (printProfileBtn) {
        printProfileBtn.addEventListener('click', function() {
            window.print();
        });
    }
    
    // Mobile menu toggle
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle');
    const navmenu = document.getElementById('navmenu');
    
    if (mobileNavToggle) {
        mobileNavToggle.addEventListener('click', function() {
            navmenu.classList.toggle('mobile-nav-active');
            this.classList.toggle('bi-list');
            this.classList.toggle('bi-x');
        });
    }
    
    // Helper Functions
    function formatDateForInput(dateString) {
        // Convert "May 15, 2002" to "2002-05-15"
        const months = {
            'January': '01', 'February': '02', 'March': '03', 'April': '04',
            'May': '05', 'June': '06', 'July': '07', 'August': '08',
            'September': '09', 'October': '10', 'November': '11', 'December': '12'
        };
        
        const parts = dateString.split(' ');
        if (parts.length === 3) {
            const month = months[parts[0]];
            const day = parts[1].replace(',', '').padStart(2, '0');
            const year = parts[2];
            return `${year}-${month}-${day}`;
        }
        return '';
    }
    
    function formatDateForDisplay(dateString) {
        // Convert "2002-05-15" to "May 15, 2002"
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
    
    function showToast(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        // Add to container
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 3000
        });
        bsToast.show();
        
        // Remove after hiding
        toast.addEventListener('hidden.bs.toast', function() {
            toastContainer.remove();
        });
    }
    
    // Initialize date formatting
    const currentDob = infoDob ? infoDob.textContent : '';
    if (currentDob && !currentDob.includes('-') && currentDob !== '') {
        // If not already formatted, format it
        infoDob.textContent = formatDateForDisplay(currentDob);
    }
});