let 
email = document.getElementById("email"),
btn = document.querySelector('button[type="submit"]'),
form = document.getElementById("form");
let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
/* si on soumet le formulaire */
form.addEventListener("submit",function(e){
    e.preventDefault();
    btn.setAttribute("disabled","");
    let temp_btn = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
    /* checker l'email */
    if(email != undefined)
    {
        let
        value_email = email.value.trim();
        value_email = email.value.replace(/ +/g,"");
        if(value_email == "")
        {
                Swal.fire({
                icon: "error",
                title: "Entrez une adresse email",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            })
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
            email.focus();
            return;
        }
        else if(emailRegex.test(email.value.trim()) == false)
        {
                Swal.fire({
                icon: "error",
                title: "Entrez une adresse email correct",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            })
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
            email.focus();
            return;
        }
    }

    /* si c'est une incription boutique */
    $.post("fonctions/email_code_password_recovery.php",
        {
            email: email.value.trim()
        },
        function(data){
            if(data.result == "error")
            {
                Swal.fire({
                    icon: "error",
                    title: data.msg,
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                })
                btn.removeAttribute("disabled");
                btn.innerHTML = temp_btn;
                return;
            }
            else
            {
                window.location = '/code-mot-de-passe';
            }
        }
    )
})