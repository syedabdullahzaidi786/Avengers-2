/**
 * Main JavaScript - Gym Management System
 * Global functions and utilities
 */

const APP = {
    baseUrl: '/Gym System',

    /**
     * Initialize the application
     */
    init: function () {
        this.setupNavbarToggle();
        this.setupDropdowns();
        this.setupFormValidation();
        this.setupDataTables();
    },

    /**
     * Setup dropdown menus (User Profile & Settings)
     */
    setupDropdowns: function () {
        const dropdowns = [
            { triggerId: 'userDropdownTrigger', menuContainerClass: '.user-dropdown' },
            { triggerId: 'settingsDropdownTrigger', menuContainerClass: '.nav-item-dropdown' }
        ];

        dropdowns.forEach(dropdownConfig => {
            const trigger = document.getElementById(dropdownConfig.triggerId);
            const container = trigger ? trigger.closest(dropdownConfig.menuContainerClass) : null;

            if (trigger && container) {
                trigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    // Close other open dropdowns first
                    document.querySelectorAll('.user-dropdown, .nav-item-dropdown').forEach(el => {
                        if (el !== container) el.classList.remove('active');
                    });
                    container.classList.toggle('active');
                });
            }
        });

        // Close all dropdowns when clicking outside
        document.addEventListener('click', function (e) {
            document.querySelectorAll('.user-dropdown, .nav-item-dropdown').forEach(container => {
                if (!container.contains(e.target)) {
                    container.classList.remove('active');
                }
            });
        });
    },

    /**
     * Setup navbar toggle for mobile
     */
    setupNavbarToggle: function () {
        const toggleBtn = document.getElementById('toggleNavbar');
        const navbarMenu = document.getElementById('navbarMenu');

        if (toggleBtn && navbarMenu) {
            toggleBtn.addEventListener('click', function () {
                navbarMenu.classList.toggle('active');
                // Change icon based on state
                const icon = toggleBtn.querySelector('i');
                if (navbarMenu.classList.contains('active')) {
                    icon.classList.replace('fa-bars', 'fa-times');
                } else {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            });
        }
    },

    /**
     * Setup form validation
     */
    setupFormValidation: function () {
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    },

    /**
     * Setup DataTables
     */
    setupDataTables: function () {
        const tables = document.querySelectorAll('.datatable');
        if (tables.length > 0 && typeof $ !== 'undefined' && $.fn.dataTable) {
            tables.forEach(table => {
                if (!$.fn.DataTable.isDataTable(table)) {
                    $(table).DataTable({
                        responsive: true,
                        pageLength: 10,
                        lengthMenu: [10, 25, 50, 100],
                        searching: false, // hide built-in filter/search box
                        language: {
                            search: 'Search:',
                            lengthMenu: 'Show _MENU_ entries',
                            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                            paginate: {
                                first: 'First',
                                last: 'Last',
                                next: 'Next',
                                previous: 'Previous'
                            }
                        }
                    });
                }
            });
        }
    },

    /**
     * Show success message
     */
    showSuccess: function (message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    },

    /**
     * Show error message
     */
    showError: function (message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#dc3545'
        });
    },

    /**
     * Show warning message
     */
    showWarning: function (message) {
        Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: message,
            confirmButtonColor: '#ffc107'
        });
    },

    /**
     * Show confirmation dialog
     */
    showConfirm: function (title, message, callback) {
        Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback();
            }
        });
    },

    /**
     * Make AJAX request
     */
    ajax: function (options) {
        const defaultOptions = {
            url: '',
            type: 'POST',
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function () {
                // Show loading indicator if needed
            },
            complete: function () {
                // Hide loading indicator if needed
            }
        };

        const config = Object.assign(defaultOptions, options);
        return $.ajax(config);
    },

    /**
     * Format date
     */
    formatDate: function (date) {
        const d = new Date(date);
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        const year = d.getFullYear();
        return `${year}-${month}-${day}`;
    },

    /**
     * Format currency
     */
    formatCurrency: function (amount) {
        return new Intl.NumberFormat('en-PK', {
            style: 'currency',
            currency: 'PKR'
        }).format(amount);
    },

    /**
     * Get query parameter
     */
    getQueryParam: function (name) {
        const url = new URL(window.location.href);
        return url.searchParams.get(name);
    },

    /**
     * Redirect to URL
     */
    redirect: function (url) {
        window.location.href = url;
    },

    /**
     * Reload page
     */
    reload: function () {
        window.location.reload();
    },

    /**
     * Export table to CSV
     */
    exportTableToCSV: function (filename, tableElement) {
        let csv = [];
        let rows = tableElement.querySelectorAll("tr");

        rows.forEach(row => {
            let csvRow = [];
            row.querySelectorAll("td, th").forEach(cell => {
                csvRow.push('"' + cell.innerText.replace(/"/g, '""') + '"');
            });
            csv.push(csvRow.join(","));
        });

        let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        let downloadLink = document.createElement("a");
        downloadLink.href = URL.createObjectURL(csvFile);
        downloadLink.download = filename;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    },

    /**
     * Validate email
     */
    isValidEmail: function (email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    /**
     * Validate phone (basic validation)
     */
    isValidPhone: function (phone) {
        const phoneRegex = /^[\d\+\-\(\) ]{10,}$/;
        return phoneRegex.test(phone);
    },

    /**
     * Validate numeric
     */
    isNumeric: function (value) {
        return !isNaN(parseFloat(value)) && isFinite(value);
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    APP.init();
});

// Make APP global
window.APP = APP;

// SweetAlert2 default config
if (typeof Swal !== 'undefined') {
    Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-primary ms-2',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    });
}
