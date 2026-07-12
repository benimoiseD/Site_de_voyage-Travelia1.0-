document.addEventListener("DOMContentLoaded",()=>{

    const btn=document.getElementById("logoutBtn");

    const modal=document.getElementById("logoutModal");

    const cancel=document.getElementById("cancelLogout");

    if(!btn || !modal) return;

    btn.addEventListener("click",(e)=>{

        e.preventDefault();

        modal.classList.add("active");

    });

    cancel.addEventListener("click",()=>{

        modal.classList.remove("active");

    });

    modal.addEventListener("click",(e)=>{

        if(e.target===modal){

            modal.classList.remove("active");

        }

    });

    document.addEventListener("keydown",(e)=>{

        if(e.key==="Escape"){

            modal.classList.remove("active");

        }

    });

});