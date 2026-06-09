// Kontactanos - Main JavaScript

// ===== Toast Notifications =====
function showToast(message, type = 'success', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
        error:   '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
        info:    '<svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = (icons[type] || '') + `<span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(20px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

// ===== Copy to Clipboard =====
function copyToClipboard(text, feedback = 'Copiado al portapapeles') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(feedback, 'success');
    }).catch(() => {
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showToast(feedback, 'success');
    });
}

// ===== Share Profile =====
function shareProfile(url, title, platform) {
    const encoded  = encodeURIComponent(url);
    const encTitle = encodeURIComponent(title);

    const urls = {
        whatsapp:  `https://wa.me/?text=${encTitle}%20${encoded}`,
        facebook:  `https://www.facebook.com/sharer/sharer.php?u=${encoded}`,
        twitter:   `https://twitter.com/intent/tweet?url=${encoded}&text=${encTitle}`,
        linkedin:  `https://www.linkedin.com/sharing/share-offsite/?url=${encoded}`,
        telegram:  `https://t.me/share/url?url=${encoded}&text=${encTitle}`,
    };

    if (platform === 'copy') {
        copyToClipboard(url, '¡Enlace copiado! Compártelo donde quieras.');
        return;
    }

    if (urls[platform]) {
        window.open(urls[platform], '_blank', 'width=600,height=400,noopener,noreferrer');
    }
}

// ===== Image Preview on Upload =====
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => { preview.src = e.target.result; preview.classList.remove('hidden'); };
    reader.readAsDataURL(input.files[0]);
}

// ===== Smooth scroll for anchor links =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ===== Character Counter =====
function initCharCounters() {
    document.querySelectorAll('[data-maxlength]').forEach(el => {
        const max = parseInt(el.dataset.maxlength);
        const counter = document.createElement('div');
        counter.className = 'text-xs text-gray-400 text-right mt-1';
        el.parentNode.appendChild(counter);

        const update = () => {
            const left = max - el.value.length;
            counter.textContent = `${el.value.length}/${max}`;
            counter.className = `text-xs mt-1 text-right ${left < 20 ? 'text-red-400' : 'text-gray-400'}`;
        };
        el.addEventListener('input', update);
        update();
    });
}

// ===== Star Rating Input =====
function initStarRating(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const input = container.querySelector('input[type=hidden]');
    const stars  = container.querySelectorAll('[data-value]');

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const val = parseInt(star.dataset.value);
            if (input) input.value = val;
            stars.forEach((s, i) => {
                s.classList.toggle('text-amber-400', i < val);
                s.classList.toggle('text-gray-300', i >= val);
            });
        });
        star.addEventListener('mouseenter', () => {
            const val = parseInt(star.dataset.value);
            stars.forEach((s, i) => {
                s.classList.toggle('text-amber-300', i < val);
            });
        });
        star.addEventListener('mouseleave', () => {
            const current = parseInt(input?.value || 0);
            stars.forEach((s, i) => {
                s.classList.toggle('text-amber-400', i < current);
                s.classList.toggle('text-gray-300', i >= current);
                s.classList.remove('text-amber-300');
            });
        });
    });
}

// ===== Lazy Images =====
function initLazyImages() {
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            }
        });
    }, { rootMargin: '100px' });

    document.querySelectorAll('img[data-src]').forEach(img => observer.observe(img));
}

// ===== Form validation helper =====
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    let valid = true;

    form.querySelectorAll('[required]').forEach(field => {
        const error = field.parentNode.querySelector('.form-error');
        if (!field.value.trim()) {
            field.classList.add('border-red-400');
            if (error) error.textContent = 'Este campo es requerido';
            valid = false;
        } else {
            field.classList.remove('border-red-400');
            if (error) error.textContent = '';
        }
    });

    return valid;
}

// ===== Show flash messages from URL =====
function checkFlashMessages() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('success')) showToast(decodeURIComponent(params.get('success')), 'success');
    if (params.get('error'))   showToast(decodeURIComponent(params.get('error')), 'error');
    if (params.get('info'))    showToast(decodeURIComponent(params.get('info')), 'info');
}

// ===== Init on DOM ready =====
document.addEventListener('DOMContentLoaded', () => {
    initCharCounters();
    initLazyImages();
    checkFlashMessages();
});
