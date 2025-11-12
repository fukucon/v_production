// ===== Kaleidoscope Particle Animation =====
const canvas = document.getElementById('particles');
const ctx = canvas.getContext('2d');

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

// Kaleidoscope particle class inspired by reference code
class KaleidoParticle {
    constructor(type = 'star', centerX = 0, centerY = 0, colorPalette = 'red') {
        this.centerX = centerX;
        this.centerY = centerY;

        // Slower speed for large kaleidoscopes
        const speedMultiplier = 0.5; // Slow and elegant

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
initKaleidoParticles();
animateKaleidoParticles();

// Handle window resize
window.addEventListener('resize', () => {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    initKaleidoParticles();
});

// ===== Navigation =====
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const navLinks = document.querySelectorAll('.nav-link');

// Toggle mobile menu
hamburger.addEventListener('click', () => {
    navMenu.classList.toggle('active');
    hamburger.classList.toggle('active');
});

// Close mobile menu when clicking a link
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// Change nav background on scroll
window.addEventListener('scroll', () => {
    const nav = document.querySelector('.nav');
    if (window.scrollY > 100) {
        nav.style.background = 'rgba(10, 10, 10, 0.98)';
    } else {
        nav.style.background = 'rgba(10, 10, 10, 0.95)';
    }
});

// ===== Smooth Scrolling =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
document.querySelectorAll('.feature-item, .talent-card, .contact-info, .contact-form').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
    observer.observe(el);
});

// ===== Talent Cards Interaction =====
const talentCards = document.querySelectorAll('.talent-card');

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

// ===== CTA Button Effect =====
const ctaButton = document.querySelector('.cta-button');

ctaButton.addEventListener('click', () => {
    const aboutSection = document.querySelector('#about');
    const offsetTop = aboutSection.offsetTop - 70;
    window.scrollTo({
        top: offsetTop,
        behavior: 'smooth'
    });
});

// ===== Contact Form =====
const contactForm = document.getElementById('contactForm');

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

// ===== Cursor Effect =====
const createSparkle = (x, y) => {
    const sparkle = document.createElement('div');
    sparkle.style.cssText = `
        position: fixed;
        width: 4px;
        height: 4px;
        background: linear-gradient(135deg, var(--red), var(--bright-red));
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        left: ${x}px;
        top: ${y}px;
        animation: sparkle 0.8s ease-out forwards;
    `;

    document.body.appendChild(sparkle);

    setTimeout(() => {
        document.body.removeChild(sparkle);
    }, 800);
};

// Add sparkle effect on mouse move (throttled)
let lastSparkleTime = 0;
document.addEventListener('mousemove', (e) => {
    const now = Date.now();
    if (now - lastSparkleTime > 100) {
        if (Math.random() > 0.7) {
            createSparkle(e.clientX, e.clientY);
        }
        lastSparkleTime = now;
    }
});

// Add sparkle animation to CSS dynamically
const style = document.createElement('style');
style.textContent = `
    @keyframes sparkle {
        0% {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
        100% {
            opacity: 0;
            transform: scale(0.5) translateY(-20px);
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

    prismContainers.forEach(el => {
        const speed = 0.5;
        el.style.transform = `translateY(${scrolled * speed}px)`;
    });
});

// ===== Loading Animation =====
window.addEventListener('load', () => {
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 1s ease-in';
        document.body.style.opacity = '1';
    }, 100);
});

// ===== Easter Egg: Konami Code =====
let konamiCode = [];
const konamiPattern = ['ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', 'b', 'a'];

document.addEventListener('keydown', (e) => {
    konamiCode.push(e.key);
    konamiCode = konamiCode.slice(-10);

    if (konamiCode.join(',') === konamiPattern.join(',')) {
        activateKaleidoscopeMode();
    }
});

function activateKaleidoscopeMode() {
    const body = document.body;
    body.style.animation = 'rainbow 2s linear infinite';

    const rainbowStyle = document.createElement('style');
    rainbowStyle.textContent = `
        @keyframes rainbow {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(360deg); }
        }
    `;
    document.head.appendChild(rainbowStyle);

    // Show easter egg message
    const easterEggMsg = document.createElement('div');
    easterEggMsg.textContent = '🌈 Kaleidoscope Mode Activated! 🌈';
    easterEggMsg.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #9400d3);
        background-size: 400% 400%;
        animation: gradient 2s ease infinite;
        color: white;
        padding: 30px 50px;
        border-radius: 10px;
        font-size: 24px;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    `;

    document.body.appendChild(easterEggMsg);

    setTimeout(() => {
        body.style.animation = '';
        document.body.removeChild(easterEggMsg);
        document.head.removeChild(rainbowStyle);
    }, 5000);
}

// ===== Performance Optimization =====
// Pause canvas animations when tab is not visible
document.addEventListener('visibilitychange', () => {
    isAnimating = !document.hidden;
    if (isAnimating) {
        animateKaleidoParticles();
    }
});

console.log('%c🌈 KaleidoChrome 🌈', 'font-size: 24px; font-weight: bold; background: linear-gradient(135deg, #dc143c, #ff1744); color: white; padding: 10px 20px; border-radius: 5px;');
console.log('%c個性が輝く無限の可能性', 'font-size: 14px; color: #dc143c; font-weight: bold;');
console.log('Try the Konami Code: ↑ ↑ ↓ ↓ ← → ← → B A');
