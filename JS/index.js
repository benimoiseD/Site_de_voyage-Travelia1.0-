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

setTimeout(()=>{

    const msg=document.querySelector(".logout-success");

    if(msg){

        msg.style.transition="opacity .5s";

        msg.style.opacity="0";

        setTimeout(()=>msg.remove(),500);

    }

},5000);