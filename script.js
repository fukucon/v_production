// ===== Opening Animation =====
(function() {
    const openingAnimation = document.querySelector('.opening-animation');
    const openingImageContainer = document.querySelector('.opening-image-container');
    const openingImage = document.querySelector('.opening-image');

    if (!openingAnimation || !openingImageContainer || !openingImage) return;

    // Wait for image to load before starting animation
    const startAnimation = () => {
        openingImageContainer.classList.add('loaded');

        // Total animation duration after image loads:
        // - Image float: 0-1.0s
        // - Image fadeout: 1.0-1.3s
        const animationDuration = 1400;

        setTimeout(() => {
            openingAnimation.classList.add('hidden');
        }, animationDuration);
    };

    // Check if image is already cached/loaded
    if (openingImage.complete) {
        startAnimation();
    } else {
        openingImage.addEventListener('load', startAnimation);
    }
})();

// ===== Kaleidoscope Particle Animation =====
const canvas = document.getElementById('particles');
if (!canvas) {
    console.log('Canvas not found - skipping particle animation');
}
const ctx = canvas ? canvas.getContext('2d') : null;

if (canvas) {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}

// Kaleidoscope particle class inspired by reference code
class KaleidoParticle {
    constructor(type = 'star', centerX = 0, centerY = 0, colorPalette = 'red') {
        this.centerX = centerX;
        this.centerY = centerY;

        // VERY slow speed for large kaleidoscopes
        const speedMultiplier = 0.15; // Very slow and elegant

        // LARGE orbit radius for big kaleidoscopes
        this.orbitRadius = Math.random() * 200 + 150; // 150-350px
        this.orbitSpeed = (Math.random() * 0.01 + 0.005) * speedMultiplier * (Math.random() > 0.5 ? 1 : -1);
        this.angle = Math.random() * Math.PI * 2;

        // Rotation parameters
        this.rotation = 0;
        this.rotationSpeed = (Math.random() * 0.025 + 0.01) * speedMultiplier * (Math.random() > 0.5 ? 1 : -1);

        // LARGER particle size
        this.size = Math.random() * 20 + 10; // 10-30px (much bigger)
        this.type = type;
        this.opacity = Math.random() * 0.6 + 0.3; // More visible

        // Color based on palette
        this.setColorPalette(colorPalette);
    }

    setColorPalette(palette) {
        // Colorful palette - mix of many vibrant colors
        const colorRand = Math.random();

        if (palette === 'left') {
            // Left side: warm colors (red, orange, yellow, pink)
            if (colorRand < 0.2) {
                this.color = { r: 220, g: 20, b: 60 }; // Crimson Red
            } else if (colorRand < 0.4) {
                this.color = { r: 255, g: 69, b: 0 }; // Orange Red
            } else if (colorRand < 0.6) {
                this.color = { r: 255, g: 215, b: 0 }; // Gold
            } else if (colorRand < 0.8) {
                this.color = { r: 255, g: 105, b: 180 }; // Hot Pink
            } else {
                this.color = { r: 255, g: 255, b: 255 }; // White
            }
        } else {
            // Right side: cool colors (blue, green, cyan, purple)
            if (colorRand < 0.2) {
                this.color = { r: 30, g: 144, b: 255 }; // Dodger Blue
            } else if (colorRand < 0.4) {
                this.color = { r: 50, g: 205, b: 50 }; // Lime Green
            } else if (colorRand < 0.6) {
                this.color = { r: 0, g: 255, b: 255 }; // Cyan
            } else if (colorRand < 0.8) {
                this.color = { r: 138, g: 43, b: 226 }; // Blue Violet
            } else {
                this.color = { r: 255, g: 255, b: 255 }; // White
            }
        }
    }

    update() {
        this.angle += this.orbitSpeed;
        this.rotation += this.rotationSpeed;
    }

    // Draw with 6-fold symmetry (kaleidoscope effect)
    drawSymmetric() {
        const segments = 6;

        for (let i = 0; i < segments; i++) {
            const segmentAngle = (Math.PI * 2 / segments) * i;

            ctx.save();
            ctx.translate(this.centerX, this.centerY);
            ctx.rotate(segmentAngle);

            // Calculate position
            const x = Math.cos(this.angle) * this.orbitRadius;
            const y = Math.sin(this.angle) * this.orbitRadius;

            // Draw original
            this.drawShape(x, y);

            // Draw mirrored (for kaleidoscope effect)
            ctx.scale(-1, 1);
            this.drawShape(x, y);

            ctx.restore();
        }
    }

    drawShape(x, y) {
        ctx.save();
        ctx.translate(x, y);
        ctx.rotate(this.rotation);
        ctx.globalAlpha = this.opacity;

        // Glow effect
        ctx.shadowBlur = 20;
        ctx.shadowColor = `rgb(${this.color.r}, ${this.color.g}, ${this.color.b})`;

        if (this.type === 'star') {
            this.drawStar();
        } else if (this.type === 'diamond') {
            this.drawDiamond();
        } else {
            this.drawCircle();
        }

        ctx.restore();
    }

    drawStar() {
        const spikes = 5;
        const outerRadius = this.size;
        const innerRadius = this.size / 2;

        ctx.beginPath();
        for (let i = 0; i < spikes * 2; i++) {
            const radius = i % 2 === 0 ? outerRadius : innerRadius;
            const angle = (Math.PI / spikes) * i;
            const px = Math.cos(angle) * radius;
            const py = Math.sin(angle) * radius;

            if (i === 0) ctx.moveTo(px, py);
            else ctx.lineTo(px, py);
        }
        ctx.closePath();

        const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, outerRadius);
        gradient.addColorStop(0, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 1)`);
        gradient.addColorStop(1, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0.3)`);
        ctx.fillStyle = gradient;
        ctx.fill();
    }

    drawDiamond() {
        ctx.beginPath();
        ctx.moveTo(0, -this.size);
        ctx.lineTo(this.size, 0);
        ctx.lineTo(0, this.size);
        ctx.lineTo(-this.size, 0);
        ctx.closePath();

        const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, this.size);
        gradient.addColorStop(0, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 1)`);
        gradient.addColorStop(1, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0.2)`);
        ctx.fillStyle = gradient;
        ctx.fill();
    }

    drawCircle() {
        ctx.beginPath();
        ctx.arc(0, 0, this.size, 0, Math.PI * 2);

        const gradient = ctx.createRadialGradient(0, 0, 0, 0, 0, this.size);
        gradient.addColorStop(0, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 1)`);
        gradient.addColorStop(1, `rgba(${this.color.r}, ${this.color.g}, ${this.color.b}, 0.1)`);
        ctx.fillStyle = gradient;
        ctx.fill();
    }
}

// Create kaleidoscope particles
const kaleidoParticles = [];
const particleTypes = ['star', 'diamond', 'circle'];

function initKaleidoParticles() {
    kaleidoParticles.length = 0;

    // Only 2 LARGE kaleidoscopes - one on each side
    const centerY = canvas.height / 2;

    // Responsive positioning based on screen size
    const screenWidth = window.innerWidth;
    let leftOffset, rightOffset;

    if (screenWidth < 768) {
        // Mobile: keep them far out (half visible)
        leftOffset = -0.15;
        rightOffset = 1.15;
    } else if (screenWidth < 1024) {
        // Tablet: bring them closer
        leftOffset = 0.05;
        rightOffset = 0.95;
    } else {
        // Desktop: bring them even closer to center
        leftOffset = 0.15;
        rightOffset = 0.85;
    }

    // Left kaleidoscope
    const leftX = canvas.width * leftOffset;

    // Right kaleidoscope
    const rightX = canvas.width * rightOffset;

    // More particles per kaleidoscope for richness (but only 2 kaleidoscopes total!)
    const particlesPerKaleidoscope = window.innerWidth < 768 ? 8 : 12;

    // Create LEFT kaleidoscope (warm colors)
    for (let i = 0; i < particlesPerKaleidoscope; i++) {
        const type = particleTypes[Math.floor(Math.random() * particleTypes.length)];
        kaleidoParticles.push(new KaleidoParticle(type, leftX, centerY, 'left'));
    }

    // Create RIGHT kaleidoscope (cool colors)
    for (let i = 0; i < particlesPerKaleidoscope; i++) {
        const type = particleTypes[Math.floor(Math.random() * particleTypes.length)];
        kaleidoParticles.push(new KaleidoParticle(type, rightX, centerY, 'right'));
    }
}

function animateKaleidoParticles() {
    if (!isAnimating) return; // Stop animation when tab is hidden

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    for (let particle of kaleidoParticles) {
        particle.update();
        particle.drawSymmetric();
    }

    requestAnimationFrame(animateKaleidoParticles);
}

// Global animation flag (defined later in Performance Optimization section)
let isAnimating = true;

// Initialize and start animation
if (canvas) {
    initKaleidoParticles();
    animateKaleidoParticles();
}

// Handle window resize
window.addEventListener('resize', () => {
    if (canvas) {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        initKaleidoParticles();
    }
});

// ===== Navigation =====
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navOverlay = document.querySelector('.nav-overlay');
const navLinks = document.querySelectorAll('.nav-link');

// Function to close menu
function closeMenu() {
    navMenu.classList.remove('active');
    hamburger.classList.remove('active');
    if (navOverlay) navOverlay.classList.remove('active');
    document.body.style.overflow = '';
}

// Function to open menu
function openMenu() {
    navMenu.classList.add('active');
    hamburger.classList.add('active');
    if (navOverlay) navOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Toggle mobile menu
if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        if (navMenu.classList.contains('active')) {
            closeMenu();
        } else {
            openMenu();
        }
    });
}

// Close mobile menu when clicking overlay
if (navOverlay) {
    navOverlay.addEventListener('click', () => {
        closeMenu();
    });
}

// Close mobile menu when clicking a link
if (navLinks.length > 0 && navMenu && hamburger) {
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            closeMenu();
        });
    });
}

// Change nav background on scroll
let navAnimationComplete = false;

// Wait for nav fade-in animation to complete (1.54s + 0.8s = 2.34s)
setTimeout(() => {
    navAnimationComplete = true;
}, 2340);

window.addEventListener('scroll', () => {
    const nav = document.querySelector('.nav');
    if (nav && navAnimationComplete) {
        if (window.scrollY > 100) {
            nav.style.background = 'rgba(10, 10, 10, 0.98)';
        } else {
            nav.style.background = 'rgba(10, 10, 10, 0.95)';
        }
    }
});

// ===== Check Page Floating Button =====
const checkFloatingBtn = document.getElementById('check-floating-btn');
const recruitmentSection = document.getElementById('recruitment-section');

if (checkFloatingBtn && recruitmentSection) {
    let isEnabled = false;

    // スクロールイベントでセクションの表示を監視
    window.addEventListener('scroll', () => {
        const rect = recruitmentSection.getBoundingClientRect();
        const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;

        if (isVisible && !isEnabled) {
            isEnabled = true;
            checkFloatingBtn.classList.remove('disabled');
            checkFloatingBtn.classList.add('enabled');
            checkFloatingBtn.innerHTML = '<span>応募フォームはこちら</span>';
            checkFloatingBtn.style.cursor = 'pointer';
        }
    });

    // クリックイベント
    checkFloatingBtn.addEventListener('click', () => {
        if (isEnabled) {
            window.open('https://forms.office.com/r/N1cAFSeNu0', '_blank');
        }
    });
}

// ===== Smooth Scrolling =====
const smoothScrollAnchors = document.querySelectorAll('a[href^="#"]');
if (smoothScrollAnchors.length > 0) {
    smoothScrollAnchors.forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offsetTop = target.offsetTop - 70;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===== Scroll Animations =====
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe elements
const observeElements = document.querySelectorAll('.feature-item, .talent-card, .contact-info, .contact-form');
if (observeElements.length > 0) {
    observeElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(el);
    });
}

// ===== Talent Cards Interaction =====
const talentCards = document.querySelectorAll('.talent-card');

if (talentCards.length > 0) {
    talentCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.zIndex = '10';
    });

    card.addEventListener('mouseleave', function() {
        this.style.zIndex = '1';
    });

    // Add 3D tilt effect
    card.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;

        this.style.transform = `translateY(-10px) perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.05)`;
    });

    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) perspective(1000px) rotateX(0) rotateY(0) scale(1)';
    });
    });
}

// ===== Contact Form =====
const contactForm = document.getElementById('contactForm');

if (contactForm) {
    contactForm.addEventListener('submit', (e) => {
    e.preventDefault();

    // Create success message
    const successMessage = document.createElement('div');
    successMessage.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, var(--red), var(--bright-red));
        color: white;
        padding: 30px 50px;
        border-radius: 10px;
        font-size: 18px;
        text-align: center;
        z-index: 10000;
        box-shadow: 0 20px 60px rgba(220, 20, 60, 0.5);
        animation: fadeInUp 0.5s ease-out;
    `;
    successMessage.innerHTML = `
        <div style="font-size: 48px; margin-bottom: 15px;">✨</div>
        <div style="font-weight: 600; margin-bottom: 10px;">送信完了！</div>
        <div style="font-size: 14px; opacity: 0.9;">お問い合わせありがとうございます</div>
    `;

    document.body.appendChild(successMessage);

    // Reset form
    contactForm.reset();

    // Remove message after 3 seconds
    setTimeout(() => {
        successMessage.style.animation = 'fadeOut 0.5s ease-out';
        setTimeout(() => {
            document.body.removeChild(successMessage);
        }, 500);
    }, 3000);
    });
}

// ===== Cursor Effect =====
const createRipple = (x, y) => {
    const ripple = document.createElement('div');
    ripple.style.cssText = `
        position: fixed;
        width: 10px;
        height: 10px;
        border: 2px solid transparent;
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        left: ${x - 5}px;
        top: ${y - 5}px;
        animation: ripple 1s ease-out forwards;
    `;

    document.body.appendChild(ripple);

    setTimeout(() => {
        document.body.removeChild(ripple);
    }, 1000);
};

// Add ripple effect on mouse move (throttled)
let lastRippleTime = 0;
document.addEventListener('mousemove', (e) => {
    const now = Date.now();
    if (now - lastRippleTime > 100) {
        if (Math.random() > 0.7) {
            createRipple(e.clientX, e.clientY);
        }
        lastRippleTime = now;
    }
});

// Add ripple effect on click
document.addEventListener('click', (e) => {
    // Create multiple ripples for click
    createRipple(e.clientX, e.clientY);
    setTimeout(() => createRipple(e.clientX, e.clientY), 100);
    setTimeout(() => createRipple(e.clientX, e.clientY), 200);
});

// Add ripple animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes ripple {
        0% {
            width: 10px;
            height: 10px;
            opacity: 1;
            border-color: rgba(220, 20, 60, 0.8);
            border-width: 2px;
        }
        50% {
            border-color: rgba(255, 23, 68, 0.6);
        }
        100% {
            width: 80px;
            height: 80px;
            opacity: 0;
            border-color: rgba(220, 20, 60, 0);
            border-width: 1px;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        to {
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.9);
        }
    }
`;
document.head.appendChild(style);

// ===== Parallax Effect =====
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const prismContainers = document.querySelectorAll('.prism-container');

    if (prismContainers.length > 0) {
        prismContainers.forEach(el => {
            const speed = 0.5;
            el.style.transform = `translateY(${scrolled * speed}px)`;
        });
    }
});

// ===== Loading Animation =====
// Removed to prevent flickering on page transitions

// ===== Performance Optimization =====
// Pause canvas animations when tab is not visible
document.addEventListener('visibilitychange', () => {
    isAnimating = !document.hidden;
    if (isAnimating && canvas) {
        animateKaleidoParticles();
    }
});

// ===== Cookie Consent Banner =====
(function() {
    const COOKIE_CONSENT_KEY = 'kaleidochrome_cookie_consent';
    const cookieBanner = document.getElementById('cookie-consent-banner');

    if (!cookieBanner) return;

    // Check if user has already given consent
    const hasConsent = localStorage.getItem(COOKIE_CONSENT_KEY);

    if (!hasConsent) {
        // Check if this is the index page
        const isIndexPage = window.location.pathname === '/' ||
                           window.location.pathname.endsWith('/index.html') ||
                           window.location.pathname === '/index.html';

        if (isIndexPage) {
            // トップページ: 応募ボタンのアニメーション完了後（4.5秒後）からスクロール検知開始
            setTimeout(() => {
                let bannerShown = false;
                window.addEventListener('scroll', () => {
                    if (!bannerShown && window.scrollY > 300) {
                        bannerShown = true;
                        cookieBanner.classList.add('show');
                    }
                });

                // すでに300px以上スクロールしている場合は即座に表示
                if (window.scrollY > 300) {
                    cookieBanner.classList.add('show');
                }
            }, 4500);
        } else {
            // その他のページ: 1秒後に自動表示
            setTimeout(() => {
                cookieBanner.classList.add('show');
            }, 1000);
        }
    }

    // Accept button
    const acceptBtn = document.getElementById('cookie-accept');
    if (acceptBtn) {
        acceptBtn.addEventListener('click', () => {
            localStorage.setItem(COOKIE_CONSENT_KEY, 'accepted');
            cookieBanner.classList.remove('show');

            // Remove from DOM after animation completes
            setTimeout(() => {
                cookieBanner.style.display = 'none';
            }, 400);
        });
    }

    // Decline button
    const declineBtn = document.getElementById('cookie-decline');
    if (declineBtn) {
        declineBtn.addEventListener('click', () => {
            localStorage.setItem(COOKIE_CONSENT_KEY, 'declined');
            cookieBanner.classList.remove('show');

            // Remove from DOM after animation completes
            setTimeout(() => {
                cookieBanner.style.display = 'none';
            }, 400);
        });
    }
})();
