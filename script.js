// Modern Barber Shop JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeNavigation();
    initializeSmoothScrolling();
    initializeAnimations();
    setCurrentYear();
    initializeBookingWizard();
});

// Navigation functionality
function initializeNavigation() {
    const navbar = document.getElementById('navbar');
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Mobile menu toggle
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }

    // Close menu when clicking on a link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navMenu) {
                navMenu.classList.remove('active');
            }
            if (hamburger) {
                hamburger.classList.remove('active');
            }
        });
    });

    // Update active link on scroll
    const sections = document.querySelectorAll('section[id]');
    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (window.scrollY >= sectionTop - 200) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === `#${current}`) {
                link.classList.add('active');
            }
        });
    });
}

// Smooth scrolling for navigation links
function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Initialize animations and interactions
function initializeAnimations() {
    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                
                // Animate counters
                if (entry.target.classList.contains('stat-number')) {
                    animateCounter(entry.target);
                }
            }
        });
    }, observerOptions);

    // Observe elements for scroll animations
    const animateElements = document.querySelectorAll('.service-card, .stat-card, .barber-card');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.6s ease-out';
        observer.observe(el);
    });

    // Observe stat numbers for counter animation
    document.querySelectorAll('.stat-number').forEach(counter => {
        observer.observe(counter);
    });
}

// Animate counter numbers
function animateCounter(element) {
    const target = parseInt(element.dataset.target);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Set current year in footer
function setCurrentYear() {
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'error' ? '#EF4444' : type === 'success' ? '#10B981' : '#3B82F6'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        z-index: 9999;
        animation: slideInRight 0.3s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Booking Wizard Functionality
function initializeBookingWizard() {
    let currentStep = 1;
    const totalSteps = 5;
    let bookingData = {};
    let services = [];
    let staff = [];

    const steps = document.querySelectorAll('.booking-step');
    const stepIndicators = document.querySelectorAll('.step');
    const progressFill = document.querySelector('.progress-fill');
    const prevBtn = document.getElementById('prev-step');
    const nextBtn = document.getElementById('next-step');
    const confirmBtn = document.getElementById('confirm-booking');

    if (!steps.length) return; // Exit if booking wizard not found

    // Fetch services and staff
    Promise.all([
        fetch('api_services.php').then(res => res.json()),
        fetch('api_staff.php').then(res => res.json())
    ]).then(([servicesData, staffData]) => {
        services = servicesData;
        staff = staffData;
        populateServices();
        populateStaff();
    }).catch(error => {
        console.error('Error fetching initial data:', error);
        showNotification('Could not load booking options. Please refresh the page.', 'error');
    });

    function populateServices() {
        const container = document.querySelector('.service-options');
        container.innerHTML = services.map(service => `
            <div class="service-option" data-service-id="${service.id}" data-price="${service.price}" data-service-name="${service.name}">
                <i class="fas fa-cut"></i>
                <span>${service.name} - $${service.price}</span>
            </div>
        `).join('');

        // Re-attach event listeners
        document.querySelectorAll('.service-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.service-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                bookingData.service_id = option.dataset.serviceId;
                bookingData.price_charged = option.dataset.price;
                bookingData.service_name = option.dataset.serviceName;
            });
        });
    }

    function populateStaff() {
        const container = document.querySelector('.barber-options');
        container.innerHTML = staff.map(member => `
            <div class="barber-option" data-staff-id="${member.id}" data-staff-name="${member.name}">
                <div class="barber-avatar"><i class="fas fa-user"></i></div>
                <span>${member.name}</span>
            </div>
        `).join('');

        // Re-attach event listeners
        document.querySelectorAll('.barber-option').forEach(option => {
            option.addEventListener('click', () => {
                document.querySelectorAll('.barber-option').forEach(o => o.classList.remove('selected'));
                option.classList.add('selected');
                bookingData.staff_id = option.dataset.staffId;
                bookingData.staff_name = option.dataset.staffName;
            });
        });
    }


    // Date selection
    const dateInput = document.getElementById('booking-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.setAttribute('min', today);
        dateInput.addEventListener('change', () => {
            bookingData.booking_date = dateInput.value;
        });
    }

    // Time slot selection
    document.querySelectorAll('.time-slot').forEach(slot => {
        slot.addEventListener('click', () => {
            document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
            slot.classList.add('selected');
            bookingData.booking_time = slot.dataset.time;
        });
    });

    // Customer details
    const customerInputs = ['customer-name', 'customer-phone', 'customer-email', 'customer-notes'];
    customerInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', () => {
                let key = inputId.replace('customer-', '');
                if (key === 'name') key = 'customer_name';
                if (key === 'phone') key = 'customer_phone';
                if (key === 'email') key = 'customer_email';
                if (key === 'notes') key = 'notes';
                bookingData[key] = input.value;
            });
        }
    });

    // Navigation buttons
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                nextStep();
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            prevStep();
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            confirmBooking();
        });
    }

    function nextStep() {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizard();
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            currentStep--;
            updateWizard();
        }
    }

    function updateWizard() {
        // Update steps
        steps.forEach((step, index) => {
            step.classList.toggle('active', index + 1 === currentStep);
        });

        // Update step indicators
        stepIndicators.forEach((indicator, index) => {
            indicator.classList.toggle('active', index + 1 === currentStep);
            indicator.classList.toggle('completed', index + 1 < currentStep);
        });

        // Update progress bar
        if (progressFill) {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            progressFill.style.width = `${progress}%`;
        }

        // Update buttons
        if (prevBtn) prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        if (nextBtn) nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
        if (confirmBtn) confirmBtn.style.display = currentStep === totalSteps ? 'block' : 'none';

        // Update summary on last step
        if (currentStep === totalSteps) {
            updateSummary();
        }
    }

    function validateStep(step) {
        switch (step) {
            case 1:
                if (!bookingData.service_id) {
                    showNotification('Please select a service', 'error');
                    return false;
                }
                break;
            case 2:
                if (!bookingData.staff_id) {
                    showNotification('Please choose a barber', 'error');
                    return false;
                }
                break;
            case 3:
                if (!bookingData.booking_date || !bookingData.booking_time) {
                    showNotification('Please select date and time', 'error');
                    return false;
                }
                break;
            case 4:
                if (!bookingData.customer_name || !bookingData.customer_phone || !bookingData.customer_email) {
                    showNotification('Please fill in all required fields', 'error');
                    return false;
                }
                break;
        }
        return true;
    }

    function updateSummary() {
        const summaryService = document.getElementById('summary-service');
        const summaryBarber = document.getElementById('summary-barber');
        const summaryDatetime = document.getElementById('summary-datetime');
        const summaryTotal = document.getElementById('summary-total');

        if (summaryService) summaryService.textContent = bookingData.service_name || '-';
        if (summaryBarber) summaryBarber.textContent = bookingData.staff_name || '-';

        if (bookingData.booking_date && bookingData.booking_time && summaryDatetime) {
            const date = new Date(bookingData.booking_date);
            const formattedDate = date.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const time = formatTime(bookingData.booking_time);
            summaryDatetime.textContent = `${formattedDate} at ${time}`;
        }

        if (summaryTotal) summaryTotal.textContent = `$${bookingData.price_charged || '0'}`;
    }

    function confirmBooking() {
        fetch('api_save_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const bookingId = 'HCH' + Date.now().toString().slice(-6);

                const bookingIdElement = document.getElementById('booking-id');
                const successModal = document.getElementById('success-modal');

                if (bookingIdElement) bookingIdElement.textContent = bookingId;
                if (successModal) successModal.style.display = 'block';

                showNotification('Booking successful!', 'success');

                setTimeout(() => {
                    resetWizard();
                    if (successModal) successModal.style.display = 'none';
                }, 4000);

            } else {
                showNotification('Booking failed: ' + data.error, 'error');
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            showNotification('An error occurred. Please try again.', 'error');
        });
    }
    
    function resetWizard() {
        currentStep = 1;
        bookingData = {};
        updateWizard();
        
        // Clear all selections and inputs
        document.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
        // Re-populate services and staff in case they were removed
        populateServices();
        populateStaff();
    }

    function formatTime(time) {
        const [hours, minutes] = time.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }

    // Initialize wizard
    updateWizard();
}

// Close modal function
function closeModal() {
    const modal = document.getElementById('success-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}