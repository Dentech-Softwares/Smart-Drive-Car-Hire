document.addEventListener('DOMContentLoaded', () => {
    // Page Loader and Smooth Entrance
    const loader = document.querySelector('.page-loader');
    const body = document.body;

    window.addEventListener('load', () => {
        if (loader) {
            loader.classList.add('fade-out');
        }
        body.classList.remove('loading');
        body.classList.add('loaded');
    });

    // Fallback if window load doesn't fire
    setTimeout(() => {
        if (loader && !loader.classList.contains('fade-out')) {
            loader.classList.add('fade-out');
            body.classList.add('loaded');
        }
    }, 2000);

    // Navbar scroll effect
    const nav = document.querySelector('nav');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    // Initialize AOS if present
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Utility for SweetAlert notifications
const notify = (type, message) => {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: message,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: '#161a1d',
            color: '#f1faee'
        });
    } else {
        alert(message);
    }
};

// Form submission helper using AJAX
const ajaxForm = async (formId, url, callback) => {
    const form = document.getElementById(formId);
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const text = await response.text(); // Get raw response first
            try {
                const data = JSON.parse(text);
                callback(data);
            } catch (jsonError) {
                console.error('Invalid JSON response:', text);
                notify('error', 'Server returned an invalid response. Please check the logs.');
            }
        } catch (error) {
            console.error('Network or Execution Error:', error);
            notify('error', 'Connection lost or server error. Please try again.');
        }
    });
};
