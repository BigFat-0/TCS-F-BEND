// admin-script.js

document.addEventListener('DOMContentLoaded', function() {
    // Initial data load
    loadAllData();

    // Setup navigation
    setupNavigation();

    // Setup modals and forms
    setupModal('add-service-btn', 'service-modal');
    setupModal('add-staff-btn', 'staff-modal');
    
    setupForms();
});

function loadAllData() {
    Promise.all([
        fetch('api_get_bookings.php').then(res => res.json()),
        fetch('api_services.php').then(res => res.json()),
        fetch('api_staff.php').then(res => res.json()),
    ]).then(([bookings, services, staff]) => {
        renderBookings(bookings);
        renderServices(services);
        renderStaff(staff);
        updateDashboard(bookings);
    }).catch(error => {
        console.error('Error loading data:', error);
        alert('Could not load admin data. Please check the connection or try again.');
    });
}

function updateDashboard(bookings) {
    const totalBookings = bookings.length;
    const totalRevenue = bookings.reduce((sum, b) => sum + parseFloat(b.price_charged), 0);

    document.getElementById('total-bookings').textContent = totalBookings;
    document.getElementById('total-revenue').textContent = `$${totalRevenue.toFixed(2)}`;
}

function renderBookings(bookings) {
    const tbody = document.getElementById('bookings-table');
    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td>#${booking.id}</td>
            <td>${booking.customer_name}</td>
            <td>${booking.service_name}</td>
            <td>${booking.staff_name}</td>
            <td>${booking.booking_date}</td>
            <td>${booking.booking_time}</td>
            <td>$${booking.price_charged}</td>
            <td><span class="status-badge status-${booking.status}">${booking.status}</span></td>
            <td>
                <button class="btn-icon" onclick="editBooking(${booking.id})" title="Edit"><i class="fas fa-edit"></i></button>
            </td>
        </tr>
    `).join('');
}

function renderServices(services) {
    const tbody = document.getElementById('services-table');
    tbody.innerHTML = services.map(service => `
        <tr>
            <td>#${service.id}</td>
            <td>${service.name}</td>
            <td>$${service.price}</td>
            <td>${service.duration} mins</td>
            <td>
                <button class="btn-icon btn-danger" onclick="deleteService(${service.id})" title="Delete"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function renderStaff(staff) {
    const tbody = document.getElementById('staff-table');
    tbody.innerHTML = staff.map(member => `
        <tr>
            <td>#${member.id}</td>
            <td>${member.name}</td>
            <td>${member.email}</td>
            <td>
                <button class="btn-icon btn-danger" onclick="deleteStaff(${member.id})" title="Delete"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `).join('');
}

function setupNavigation() {
    const navItems = document.querySelectorAll('.nav-item[data-section]');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (this.getAttribute('href') !== 'logout.php') {
                e.preventDefault();
                const section = this.dataset.section;
                showSection(section);
                navItems.forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('page-title').textContent = this.querySelector('span').textContent;
            }
        });
    });
}

function showSection(sectionName) {
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    document.getElementById(sectionName + '-section').classList.add('active');
}

function setupModal(buttonId, modalId) {
    const button = document.getElementById(buttonId);
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const closeBtn = modal.querySelector('.close-btn');

    if (button) {
        button.addEventListener('click', () => modal.style.display = 'flex');
    }
    if (closeBtn) {
        closeBtn.addEventListener('click', () => modal.style.display = 'none');
    }
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

function setupForms() {
    // Add Service form
    const serviceForm = document.getElementById('service-form');
    if (serviceForm) {
        serviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
    
            fetch('api_services.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadAllData();
                    document.getElementById('service-modal').style.display = 'none';
                    this.reset();
                } else {
                    alert('Failed to add service: ' + result.error);
                }
            });
        });
    }


    // Add Staff form
    const staffForm = document.getElementById('staff-form');
    if(staffForm) {
        staffForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
    
            fetch('api_staff.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadAllData();
                    document.getElementById('staff-modal').style.display = 'none';
                    this.reset();
                } else {
                    alert('Failed to add staff: ' + result.error);
                }
            });
        });
    }
}

function deleteService(id) {
    if (confirm('Are you sure you want to delete this service?')) {
        fetch('api_services.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadAllData();
            } else {
                alert('Failed to delete service: ' + result.error);
            }
        });
    }
}

function deleteStaff(id) {
    // For now, this is not implemented in the backend.
    alert('Deleting staff is not implemented yet.');
}

function editBooking(id) {
    // For now, this is not implemented.
    alert('Editing bookings is not implemented yet.');
}