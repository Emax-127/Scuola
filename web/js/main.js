// Main JavaScript for Sistema Gestione Ticket Riparazione

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Confirm before delete
function confirmDelete(message) {
    return confirm(message || 'Sei sicuro di voler eliminare questo elemento?');
}

// Format date to Italian format
function formatDateIT(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('it-IT');
}

// Calculate days between dates
function daysBetween(date1, date2) {
    const oneDay = 24 * 60 * 60 * 1000;
    const firstDate = new Date(date1);
    const secondDate = new Date(date2);
    return Math.round(Math.abs((firstDate - secondDate) / oneDay));
}

// Show loading spinner
function showLoading() {
    const overlay = document.createElement('div');
    overlay.className = 'spinner-overlay';
    overlay.id = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner-border text-light" role="status"><span class="visually-hidden">Caricamento...</span></div>';
    document.body.appendChild(overlay);
}

// Hide loading spinner
function hideLoading() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        overlay.remove();
    }
}

// Auto-update timestamp fields
function updateTimestamps() {
    const timestamps = document.querySelectorAll('[data-timestamp]');
    timestamps.forEach(function(element) {
        const timestamp = element.getAttribute('data-timestamp');
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000);
        
        let display;
        if (diff < 60) {
            display = 'pochi secondi fa';
        } else if (diff < 3600) {
            const minutes = Math.floor(diff / 60);
            display = minutes + ' minut' + (minutes === 1 ? 'o' : 'i') + ' fa';
        } else if (diff < 86400) {
            const hours = Math.floor(diff / 3600);
            display = hours + ' or' + (hours === 1 ? 'a' : 'e') + ' fa';
        } else {
            display = formatDateIT(timestamp);
        }
        
        element.textContent = display;
    });
}

// Initialize tooltips
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(function(field) {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initTooltips();
    updateTimestamps();
    
    // Update timestamps every minute
    setInterval(updateTimestamps, 60000);
});
