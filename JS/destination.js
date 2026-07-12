// Navigation animation on scroll
const nav = document.querySelector('nav');
window.addEventListener('scroll', () => {
    if (window.scrollY > 0) {
        nav.classList.add('anim-nav');
    } else {
        nav.classList.remove('anim-nav');
    }
});

// Scroll animations for destination cards
document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.destination-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => {
        card.classList.add('hidden');
        observer.observe(card);
    });
});
