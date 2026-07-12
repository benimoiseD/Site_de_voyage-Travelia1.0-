
        //NAV-NAR ANIMEE

    const nav=document.querySelector('nav');
    window.addEventListener('scroll',()=>{
    if (window.scrollY>0){
        nav.classList.add('anim-nav');
    } else{
        nav.classList.remove('anim-nav');
    }
})






document.addEventListener('DOMContentLoaded', () => {
    const blocks = document.querySelectorAll('.generale .img');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.2 });

    blocks.forEach(block => {
        block.classList.add('hidden'); // Initial state
        observer.observe(block);
    });
});




// Validation et gestion du formulaire de contact
   document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const nom = document.getElementById('nom').value.trim();
    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();
    const formMessage = document.getElementById('formMessage');
    let error = "";

    // Validation simple
    if (nom.length < 2) {
        error = "Veuillez entrer un nom valide.";
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        error = "Veuillez entrer un email valide.";
    } else if (message.length < 5) {
        error = "Le message est trop court.";
    }

    if (error) {
        formMessage.textContent = error;
        formMessage.style.color = "red";
    } else {
        formMessage.textContent = "Merci pour votre message, " + nom + " ! Nous vous répondrons rapidement.";
        formMessage.style.color = "green";
        this.reset();
        // Faire disparaître le message après 3 secondes
        setTimeout(() => {
            formMessage.textContent = "";
        }, 3000);
    }
});

