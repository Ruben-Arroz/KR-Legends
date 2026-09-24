/**
 * KR Legends - Profile Edit Custom Enhancements
 * Adds interactive elements and visual enhancements to the profile edit page
 */

document.addEventListener('DOMContentLoaded', function () {
    // ===== IMAGE PREVIEW FUNCTIONALITY =====
    const avatarInput = document.getElementById('avatar');
    const bannerInput = document.getElementById('banner');
    const avatarPreview = document.querySelector('.perfil-avatar');
    const bannerPreview = document.querySelector('.perfil-banner');

    // Enhanced Avatar Preview with animation
    avatarInput.addEventListener('change', function () {
        handleImagePreview(this, avatarPreview);
    });

    // Enhanced Banner Preview with animation
    bannerInput.addEventListener('change', function () {
        handleImagePreview(this, bannerPreview);
    });

    function handleImagePreview(input, previewElement) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            // Add loading state
            previewElement.style.opacity = '0.5';

            reader.onload = function (e) {
                // Remove loading state and apply new image with animation
                previewElement.style.opacity = '1';
                previewElement.src = e.target.result;
                previewElement.classList.add('fade-in');

                // Remove animation class after it completes
                setTimeout(() => {
                    previewElement.classList.remove('fade-in');
                }, 500);
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    // ===== FORM INTERACTION ENHANCEMENTS =====

    // Character counter for biography
    const bioTextarea = document.getElementById('bio');
    const charCount = document.getElementById('char-count');

    function updateCharCount() {
        const currentLength = bioTextarea.value.length;
        charCount.textContent = currentLength;

        // Visual feedback based on length
        if (currentLength > 400) {
            charCount.style.color = '#ffc107'; // Warning yellow
        } else if (currentLength > 450) {
            charCount.style.color = '#dc3545'; // Danger red
        } else {
            charCount.style.color = '#FFD700'; // Normal gold
        }
    }

    // Initialize character count on page load
    updateCharCount();

    // Update on input
    bioTextarea.addEventListener('input', updateCharCount);

    // ===== FORM SECTION ANIMATIONS =====

    // Add staggered fade-in animations to form sections
    const formSections = document.querySelectorAll('.form-section');
    formSections.forEach((section, index) => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(20px)';
        section.style.transition = 'opacity 0.5s ease, transform 0.5s ease';

        setTimeout(() => {
            section.style.opacity = '1';
            section.style.transform = 'translateY(0)';
        }, 100 * index); // Stagger the animations
    });

    // ===== INPUT VISUAL ENHANCEMENTS =====

    // Add focus effects to all inputs
    const allInputs = document.querySelectorAll('input, textarea, select');
    allInputs.forEach(input => {
        // Add transition class
        input.classList.add('transition-all');

        // Add focus and blur events for parent highlighting
        input.addEventListener('focus', function () {
            this.parentElement.classList.add('input-focused');
        });

        input.addEventListener('blur', function () {
            this.parentElement.classList.remove('input-focused');
        });
    });

    // ===== FORM VALIDATION ENHANCEMENT =====

    // Simple inline validation for required fields
    const form = document.querySelector('form');
    const requiredInputs = form.querySelectorAll('[required]');

    requiredInputs.forEach(input => {
        input.addEventListener('blur', function () {
            if (!this.value.trim()) {
                this.classList.add('is-invalid');

                // Create or update error message
                let errorMsg = this.parentElement.querySelector('.invalid-feedback');
                if (!errorMsg) {
                    errorMsg = document.createElement('div');
                    errorMsg.className = 'invalid-feedback';
                    this.parentElement.appendChild(errorMsg);
                }
                errorMsg.textContent = 'Este campo é obrigatório';
            } else {
                this.classList.remove('is-invalid');
                const errorMsg = this.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.remove();
            }
        });

        // Remove validation styling on input
        input.addEventListener('input', function () {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
                const errorMsg = this.parentElement.querySelector('.invalid-feedback');
                if (errorMsg) errorMsg.remove();
            }
        });
    });

    // ===== ROBLOX USERNAME VALIDATION =====

    const robloxUsernameField = document.getElementById('roblox_username');
    if (robloxUsernameField) {
        const originalValue = robloxUsernameField.getAttribute('data-original-value');

        robloxUsernameField.addEventListener('change', function () {
            const newValue = this.value.trim();

            // Visual indication if changed from original
            if (newValue !== originalValue) {
                this.style.borderColor = '#ffc107';
                this.style.boxShadow = '0 0 0 0.2rem rgba(255, 193, 7, 0.25)';
            } else {
                this.style.borderColor = '';
                this.style.boxShadow = '';
            }
        });
    }

    // ===== ENHANCE FORM LAYOUT =====

    // Wrap form content in structural divs for better styling
    function enhanceFormLayout() {
        // Create wrapper for profile images
        const avatarField = document.querySelector('#avatar').parentElement;
        const bannerField = document.querySelector('#banner').parentElement;

        avatarField.classList.add('media-preview-container');
        bannerField.classList.add('media-preview-container');

        // Create grid layout for form (on larger screens)
        const formSectionsToWrap = [
            document.querySelector('#name').parentElement,
            document.querySelector('#user_name').parentElement,
            document.querySelector('#country').parentElement,
            document.querySelector('#city').parentElement,
            document.querySelector('#genero').parentElement,
            document.querySelector('#date_of_birth').parentElement
        ];

        // Create a grid wrapper
        const gridWrapper = document.createElement('div');
        gridWrapper.className = 'form-grid';

        // Move elements into grid
        formSectionsToWrap.forEach(section => {
            const parent = section.parentElement;
            gridWrapper.appendChild(section);
            if (parent === form) {
                form.insertBefore(gridWrapper, document.querySelector('#bio').parentElement);
            }
        });

        // Bio should span full width
        document.querySelector('#bio').parentElement.classList.add('form-grid-full');
    }

    // Apply layout enhancements if appropriate (check if elements exist)
    if (document.querySelector('#name') && document.querySelector('#user_name')) {
        //enhanceFormLayout(); // Commented out to keep original structure intact
    }
});