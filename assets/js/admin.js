// Admin Dashboard JavaScript (wired to PHP backend)

let students = [];
let currentPage = 1;
const itemsPerPage = 10;
let currentStudentId = null;

document.addEventListener('DOMContentLoaded', function() {
    initProfileDropdown();
    fetchStudents();
    setupEventListeners();
});

// Initialize profile dropdown
function initProfileDropdown() {
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    
    if (profileTrigger && profileDropdown) {
        profileTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            profileDropdown.classList.remove('show');
        });
        
        // Prevent dropdown from closing when clicking inside it
        profileDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
}

async function fetchStudents(searchTerm = '') {
    const tableBody = document.getElementById('studentsTableBody');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const paginationContainer = document.getElementById('paginationContainer');

    if (loadingState) {
        loadingState.classList.remove('d-none');
        loadingState.style.display = 'block';
    }
    tableBody.innerHTML = '';
    if (emptyState) {
        emptyState.classList.add('d-none');
        emptyState.style.display = 'none';
    }

    const url = searchTerm
        ? `students_api.php?action=search&q=${encodeURIComponent(searchTerm)}`
        : 'students_api.php?action=list';

    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to load students');
        }
        
        students = Array.isArray(data.data) ? data.data : [];
        
        console.log(`Loaded ${students.length} students${searchTerm ? ' (search: ' + searchTerm + ')' : ''}`);
    } catch (err) {
        console.error('Error fetching students:', err);
        console.error('URL:', url);
        if (typeof showNotification === 'function') {
            showNotification('Error loading students: ' + err.message, 'error');
        } else {
            alert('Error loading students: ' + err.message);
        }
        if (loadingState) {
            loadingState.classList.add('d-none');
            loadingState.style.display = 'none';
        }
        if (emptyState) {
            emptyState.classList.remove('d-none');
            emptyState.style.display = 'block';
        }
        if (paginationContainer) paginationContainer.classList.add('d-none');
        return;
    }

    if (loadingState) {
        loadingState.classList.add('d-none');
        loadingState.style.display = 'none';
    }

    if (students.length === 0) {
        if (emptyState) {
            emptyState.classList.remove('d-none');
            emptyState.style.display = 'block';
        }
        if (paginationContainer) paginationContainer.classList.add('d-none');
        return;
    }

    if (emptyState) {
        emptyState.classList.add('d-none');
        emptyState.style.display = 'none';
    }
    if (paginationContainer) paginationContainer.classList.remove('d-none');

    const totalPages = Math.ceil(students.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageStudents = students.slice(startIndex, endIndex);

    pageStudents.forEach(student => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${student.studentId}</td>
            <td>${student.firstName} ${student.lastName}</td>
            <td>${student.program}</td>
            <td>${student.yearLevel}</td>
            <td>${student.email}</td>
            <td>
                <span class="status-badge ${student.isActive ? 'status-active' : 'status-inactive'}">
                    ${student.isActive ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn view" onclick="viewStudent(${student.id})" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="action-btn edit" onclick="editStudent(${student.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="action-btn delete" onclick="confirmDeleteStudent(${student.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tableBody.appendChild(row);
    });

    generatePagination(totalPages);
    updateStats();
}

// Generate pagination buttons
function generatePagination(totalPages) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';
    
    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
            <i class="bi bi-chevron-left"></i>
        </a>
    `;
    pagination.appendChild(prevLi);
    
    // Page buttons
    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${currentPage === i ? 'active' : ''}`;
        li.innerHTML = `
            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        `;
        pagination.appendChild(li);
    }
    
    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
            <i class="bi bi-chevron-right"></i>
        </a>
    `;
    pagination.appendChild(nextLi);
}

// Change page
function changePage(page) {
    currentPage = page;
    const searchInput = document.getElementById('searchInput');
    fetchStudents(searchInput ? searchInput.value : '');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const clearSearch = document.getElementById('clearSearch');
    
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        fetchStudents(this.value);
    });
    
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        currentPage = 1;
        fetchStudents();
    });
    
    // Add student button
    const addStudentBtn = document.getElementById('addStudentBtn');
    const addStudentModal = new bootstrap.Modal(document.getElementById('addStudentModal'));
    
    addStudentBtn.addEventListener('click', function() {
        document.getElementById('addStudentForm').reset();
        addStudentModal.show();
    });
    
    // Save student button
    const saveStudentBtn = document.getElementById('saveStudentBtn');
    saveStudentBtn.addEventListener('click', saveStudent);
    
    // Update student button
    const updateStudentBtn = document.getElementById('updateStudentBtn');
    updateStudentBtn.addEventListener('click', updateStudent);
    
    // Delete confirmation button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    confirmDeleteBtn.addEventListener('click', deleteStudent);
    
    // Quick action buttons
    document.getElementById('refreshDataBtn').addEventListener('click', function() {
        currentPage = 1;
        fetchStudents();
        updateStats();
        showNotification('Data refreshed successfully!', 'success');
    });
    
    document.getElementById('exportDataBtn').addEventListener('click', function() {
        exportData();
    });
    
    document.getElementById('bulkUploadBtn').addEventListener('click', function() {
        showNotification('Bulk upload feature coming soon!', 'info');
    });
    
    document.getElementById('manageProgramsBtn').addEventListener('click', function() {
        showNotification('Program management feature coming soon!', 'info');
    });
    
    document.getElementById('reportsBtn').addEventListener('click', function() {
        showNotification('Report generation feature coming soon!', 'info');
    });
    
    // Edit from view button
    document.getElementById('editFromViewBtn').addEventListener('click', function() {
        const viewStudentModal = bootstrap.Modal.getInstance(document.getElementById('viewStudentModal'));
        viewStudentModal.hide();
        
        if (currentStudentId) {
            setTimeout(() => editStudent(currentStudentId), 300);
        }
    });
}

// Update stats cards
function updateStats() {
    const total = students.length;
    const active = students.filter(s => s.isActive).length;
    const thisMonth = students.filter(s => {
        const enrollmentDate = new Date(s.enrollmentDate);
        const now = new Date();
        return enrollmentDate.getMonth() === now.getMonth() && 
               enrollmentDate.getFullYear() === now.getFullYear();
    }).length;
    const pending = students.filter(s => !s.isActive).length;
    
    document.getElementById('totalStudents').textContent = total;
    document.getElementById('activeStudents').textContent = active;
    document.getElementById('newStudents').textContent = thisMonth;
    document.getElementById('pendingStudents').textContent = pending;
}

// Save new student
async function saveStudent() {
    const form = document.getElementById('addStudentForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const newStudent = {
        firstName: document.getElementById('firstName').value,
        lastName: document.getElementById('lastName').value,
        studentId: document.getElementById('studentId').value,
        email: document.getElementById('email').value,
        program: document.getElementById('program').value,
        yearLevel: document.getElementById('yearLevel').value,
        address: document.getElementById('address').value,
        contact: document.getElementById('phoneNumber').value
    };

    try {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('firstName', newStudent.firstName);
        formData.append('lastName', newStudent.lastName);
        formData.append('course', newStudent.program);
        formData.append('yearLevel', newStudent.yearLevel);
        formData.append('email', newStudent.email);
        formData.append('contact', newStudent.contact);
        formData.append('address', newStudent.address);
        const res = await fetch('students_api.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Create failed');
    } catch (err) {
        showNotification(err.message, 'error');
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('addStudentModal'));
    modal.hide();
    currentPage = 1;
    fetchStudents();
    addRecentActivity(`New student added: ${newStudent.firstName} ${newStudent.lastName}`);
    showNotification('Student added successfully!', 'success');
}

// Edit student
async function editStudent(id) {
    currentStudentId = id;
    
    try {
        const res = await fetch(`students_api.php?action=view&id=${id}`, { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load student details');
        
        const student = data.data;
        const profileImageSrc = student.profileImage || student.profile_image ? 
            `data:image/jpeg;base64,${student.profileImage || student.profile_image}` : 
            'assets/img/student-avatar.jpg';
        
        // Populate all form fields
        document.getElementById('editStudentIdHidden').value = student.id || student.studentId;
        document.getElementById('editStudentId').value = student.studentId || student.id;
        document.getElementById('editUsername').value = student.username || '';
        document.getElementById('editFirstName').value = student.firstName || '';
        document.getElementById('editLastName').value = student.lastName || '';
        document.getElementById('editEmail').value = student.email || '';
        document.getElementById('editPhoneNumber').value = student.contact || '';
        document.getElementById('editProgram').value = student.course || student.program || '';
        document.getElementById('editYearLevel').value = student.yearLevel || student.year_level || '';
        document.getElementById('editDateOfBirth').value = student.dateOfBirth || student.date_of_birth || '';
        document.getElementById('editGender').value = student.gender || '';
        document.getElementById('editNationality').value = student.nationality || '';
        document.getElementById('editMaritalStatus').value = student.maritalStatus || student.marital_status || '';
        document.getElementById('editBloodType').value = student.bloodType || student.blood_type || '';
        document.getElementById('editAddress').value = student.address || '';
        document.getElementById('editEmergencyContactName').value = student.emergencyContactName || student.emergency_contact_name || '';
        document.getElementById('editEmergencyContactRelationship').value = student.emergencyContactRelationship || student.emergency_contact_relationship || '';
        document.getElementById('editEmergencyContactPhone').value = student.emergencyContactPhone || student.emergency_contact_phone || '';
        
        // Set profile image preview and base64
        document.getElementById('editProfileImagePreview').src = profileImageSrc;
        if (student.profileImage || student.profile_image) {
            document.getElementById('editProfileImageBase64').value = student.profileImage || student.profile_image;
        }
        
        const modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
        modal.show();
    } catch (err) {
        console.error('Error loading student for edit:', err);
        if (typeof showNotification === 'function') {
            showNotification('Error loading student: ' + err.message, 'error');
        } else {
            alert('Error loading student: ' + err.message);
        }
    }
}

// Update student
async function updateStudent() {
    const form = document.getElementById('editStudentForm');
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
    }
    
    const id = parseInt(document.getElementById('editStudentIdHidden').value);
    
    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('firstName', document.getElementById('editFirstName').value);
        formData.append('lastName', document.getElementById('editLastName').value);
        formData.append('username', document.getElementById('editUsername').value);
        formData.append('course', document.getElementById('editProgram').value);
        formData.append('yearLevel', document.getElementById('editYearLevel').value);
        formData.append('email', document.getElementById('editEmail').value);
        formData.append('contact', document.getElementById('editPhoneNumber').value);
        formData.append('address', document.getElementById('editAddress').value);
        formData.append('dateOfBirth', document.getElementById('editDateOfBirth').value);
        formData.append('gender', document.getElementById('editGender').value);
        formData.append('nationality', document.getElementById('editNationality').value);
        formData.append('maritalStatus', document.getElementById('editMaritalStatus').value);
        formData.append('bloodType', document.getElementById('editBloodType').value);
        formData.append('emergencyContactName', document.getElementById('editEmergencyContactName').value);
        formData.append('emergencyContactRelationship', document.getElementById('editEmergencyContactRelationship').value);
        formData.append('emergencyContactPhone', document.getElementById('editEmergencyContactPhone').value);
        
        // Handle profile image
        const profileImageFile = document.getElementById('editProfileImage').files[0];
        if (profileImageFile) {
            formData.append('profileImage', profileImageFile);
        } else {
            // If no new file, send existing base64 if available
            const existingImage = document.getElementById('editProfileImageBase64').value;
            if (existingImage) {
                formData.append('profileImageBase64', existingImage);
            }
        }

        const res = await fetch('students_api.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Update failed');
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('editStudentModal'));
        modal.hide();
        fetchStudents();
        const firstName = document.getElementById('editFirstName').value;
        const lastName = document.getElementById('editLastName').value;
        if (typeof addRecentActivity === 'function') {
            addRecentActivity(`Student updated: ${firstName} ${lastName}`);
        }
        if (typeof showNotification === 'function') {
            showNotification('Student updated successfully!', 'success');
        } else {
            alert('Student updated successfully!');
        }
    } catch (err) {
        console.error('Update error:', err);
        if (typeof showNotification === 'function') {
            showNotification(err.message, 'error');
        } else {
            alert('Error: ' + err.message);
        }
    }
}

// View student details
async function viewStudent(id) {
    currentStudentId = id;
    
    try {
        const res = await fetch(`students_api.php?action=view&id=${id}`, { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Failed to load student details');
        
        const student = data.data;
        const profileImageSrc = student.profileImage ? 
            `data:image/jpeg;base64,${student.profileImage}` : 
            'assets/img/student-avatar.jpg';
        
        const studentDetails = document.getElementById('studentDetails');
        studentDetails.innerHTML = `
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <img src="${profileImageSrc}" alt="Profile" 
                         style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #667eea;">
                </div>
                <div class="col-md-8">
                    <h4 class="mb-3">${student.firstName} ${student.lastName}</h4>
                    <p class="text-muted">Student ID: ${student.studentId}</p>
                </div>
            </div>
            
            <hr>
            
            <h6 class="mb-3 text-primary"><i class="bi bi-person"></i> Basic Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Student ID:</strong> ${student.studentId || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Username:</strong> ${student.username || '—'}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>First Name:</strong> ${student.firstName || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Last Name:</strong> ${student.lastName || '—'}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Email:</strong> ${student.email || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Contact:</strong> ${student.contact || '—'}
                </div>
            </div>
            
            <hr>
            
            <h6 class="mb-3 text-primary"><i class="bi bi-graduation-cap"></i> Academic Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Course/Program:</strong> ${student.course || student.program || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Year Level:</strong> ${student.yearLevel || student.year_level || '—'}
                </div>
            </div>
            
            <hr>
            
            <h6 class="mb-3 text-primary"><i class="bi bi-person-vcard"></i> Personal Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Date of Birth:</strong> ${student.dateOfBirth || student.date_of_birth || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Gender:</strong> ${student.gender || '—'}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Nationality:</strong> ${student.nationality || '—'}
                </div>
                <div class="col-md-6">
                    <strong>Marital Status:</strong> ${student.maritalStatus || student.marital_status || '—'}
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Blood Type:</strong> ${student.bloodType || student.blood_type || '—'}
                </div>
            </div>
            <div class="mb-3">
                <strong>Address:</strong> ${student.address || '—'}
            </div>
            
            <hr>
            
            <h6 class="mb-3 text-primary"><i class="bi bi-person-exclamation"></i> Emergency Contact</h6>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Contact Person:</strong> ${student.emergencyContactName || student.emergency_contact_name || '—'}
                </div>
                <div class="col-md-4">
                    <strong>Relationship:</strong> ${student.emergencyContactRelationship || student.emergency_contact_relationship || '—'}
                </div>
                <div class="col-md-4">
                    <strong>Phone Number:</strong> ${student.emergencyContactPhone || student.emergency_contact_phone || '—'}
                </div>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('viewStudentModal'));
        modal.show();
    } catch (err) {
        console.error('Error loading student details:', err);
        if (typeof showNotification === 'function') {
            showNotification('Error loading student details: ' + err.message, 'error');
        } else {
            alert('Error loading student details: ' + err.message);
        }
    }
}

// Confirm delete student
function confirmDeleteStudent(id) {
    const student = students.find(s => s.id === id);
    if (!student) return;
    
    currentStudentId = id;
    
    const deleteStudentInfo = document.getElementById('deleteStudentInfo');
    deleteStudentInfo.innerHTML = `
        <h6>${student.firstName} ${student.lastName}</h6>
        <p>Student ID: ${student.studentId}</p>
        <p>Program: ${student.program}</p>
        <p>Email: ${student.email}</p>
    `;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Delete student
async function deleteStudent() {
    const studentIndex = students.findIndex(s => s.id === currentStudentId);
    
    if (studentIndex === -1) return;
    
    const deletedStudent = students[studentIndex];

    try {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', currentStudentId);
        const res = await fetch('students_api.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Delete failed');
    } catch (err) {
        showNotification(err.message, 'error');
        return;
    }

    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
    modal.hide();

    // Reset page if needed
    const totalPages = Math.ceil(students.length / itemsPerPage);
    if (currentPage > totalPages) {
        currentPage = Math.max(1, totalPages);
    }

    fetchStudents();
    addRecentActivity(`Student deleted: ${deletedStudent.firstName} ${deletedStudent.lastName}`);
    showNotification('Student deleted successfully!', 'success');
    
    currentStudentId = null;
}

// Export data as CSV
function exportData() {
    if (students.length === 0) {
        showNotification('No data to export!', 'warning');
        return;
    }
    
    const headers = ['Student ID', 'First Name', 'Last Name', 'Email', 'Program', 'Year Level', 'Status'];
    const csvRows = [headers.join(',')];
    
    students.forEach(student => {
        const row = [
            student.studentId,
            student.firstName,
            student.lastName,
            student.email,
            student.program,
            student.yearLevel,
            student.isActive ? 'Active' : 'Inactive'
        ].map(field => `"${field}"`).join(',');
        csvRows.push(row);
    });
    
    const csvString = csvRows.join('\n');
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `students_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showNotification('Data exported successfully!', 'success');
}

// Add recent activity
function addRecentActivity(text) {
    const activityList = document.getElementById('recentActivity');
    const now = new Date();
    const timeString = formatTimeAgo(now);
    
    const activityItem = document.createElement('div');
    activityItem.className = 'activity-item';
    activityItem.innerHTML = `
        <div class="activity-icon blue">
            <i class="bi bi-person"></i>
        </div>
        <div class="activity-content">
            <h6>${text}</h6>
            <small>${timeString}</small>
        </div>
    `;
    
    activityList.insertBefore(activityItem, activityList.firstChild);
    
    // Keep only 5 most recent activities
    while (activityList.children.length > 5) {
        activityList.removeChild(activityList.lastChild);
    }
}

// Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Format time ago
function formatTimeAgo(date) {
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins === 1 ? '' : 's'} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
    
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.custom-notification');
    existingNotifications.forEach(n => n.remove());
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="bi ${type === 'success' ? 'bi-check-circle' : type === 'error' ? 'bi-exclamation-circle' : 'bi-info-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="bi bi-x"></i>
        </button>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        max-width: 400px;
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    // Add inner content styles
    const content = notification.querySelector('.notification-content');
    content.style.cssText = `
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    `;
    
    // Add close button styles
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.style.cssText = `
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
        padding: 0;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    `;
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Add slideOut animation
const slideOutStyle = document.createElement('style');
slideOutStyle.textContent = `
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(slideOutStyle);