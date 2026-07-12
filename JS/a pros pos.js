        // NAVBAR ANIMÉE
        const nav = document.querySelector('nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 0) {
                nav.classList.add('anim-nav');
            } else {
                nav.classList.remove('anim-nav');
            }
        });