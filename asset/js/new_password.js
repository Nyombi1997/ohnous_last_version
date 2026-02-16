let 
password = document.querySelectorAll("#password"),
form = document.getElementById("form");
let
btn = form.querySelector('button[type="submit"]');
/* si on soumet le formulaire */
form.addEventListener("submit",function(e){
    e.preventDefault();
    btn.setAttribute("disabled","");
    let temp_btn = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
    /* checker password */
    if(password.length>1)
    {
        if(password[0].value.length < 6)
        {
            Swal.fire({
                icon: "error",
                title: "Le mot de passe doit avoir au moins 6 caractères",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            }).then(() => {
                password[0].focus();
            })
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
            return;
        }
        else if(password[0].value != password[1].value)
        {
            Swal.fire({
                icon: "error",
                title: "Les mots de passe ne sont pas identique.",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            }).then(() => {
                password[0].focus();
            })
            btn.removeAttribute("disabled");
            btn.innerHTML = temp_btn;
            return;
        }
    }

    /* si c'est une incription boutique */
    $.post("fonctions/new_password.php",
        {
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
            else if(data.result == "error1")
            {
                Swal.fire({
                    icon: "error",
                    title: data.msg,
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                }).then(()=>{
                    window.location = "/changer-mot-de-passe";
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
