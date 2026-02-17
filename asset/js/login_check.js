let 
email = document.getElementById("email"),
password = document.querySelectorAll("#password"),
form = document.getElementById("form");
let
btn = form.querySelector("button[type='submit']");
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
            email.focus();
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
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
            email.focus();
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
            return;
        }
    }
    /* checker password */
    if(password[0].value.length == 0)
    {
        Swal.fire({
            icon: "error",
            title: "Veuillez entrer le mot de passe",
            text: "",
            confirmButtonText: "OK",
            confirmButtonColor: "#6775d6"
        }).then(() => {
            password[0].focus();
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
        })
        return;
    }

    /* si c'est une incription boutique */
    $.post("fonctions/login.php",
        {
            email: email.value.trim(),
            mdp: password[0].value,
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
                window.location = '/'+data.msg;
            }
        }
    )
})

/* afficher password */
document.querySelectorAll(".vu_password_form_ohnous").forEach(function(element){
    element.addEventListener("click",function(){
        let parentElement = element.parentElement;
        let passwordInput = parentElement.querySelector("input");
        element.classList.toggle("fa-eye-slash");
        element.classList.toggle("fa-eye");
        if(element.classList.contains("fa-eye-slash"))
        {
            passwordInput.type = "password";
        }
        else
        {
            passwordInput.type = "text";
        }
    })
})
