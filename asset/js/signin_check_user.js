let 
nom = document.getElementById("nom"),
email = document.getElementById("email"),
password = document.querySelectorAll("#password"),
form = document.getElementById("form"),
choix_form_ohnous = document.getElementById("choix_form_ohnous");
let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
let
btn = form.querySelector('button[type="submit"]');

function getAuthRedirect(fallbackPath = '/compte')
{
    const redirect = localStorage.getItem('ohnous_after_auth_redirect');
    if(redirect && redirect.trim() !== '')
    {
        localStorage.removeItem('ohnous_after_auth_redirect');
        return redirect;
    }
    return fallbackPath;
}
/* si on soumet le formulaire */
form.addEventListener("submit",function(e){
    e.preventDefault();
    btn.setAttribute("disabled","");
    let temp_btn = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
    /* checker le nom */
    if(nom != undefined)
    {
        let
        value_nom = nom.value.trim();
        value_nom = nom.value.replace(/ +/g,"");
        let erreur = "Entrez un nom d'utilisateur";
        let link = "check_nom_utilisateur";
        /* si le text est vide */
        if(value_nom == "")
        {
                Swal.fire({
                icon: "error",
                title: erreur,
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
            $.post("fonctions/"+link+".php",
                {nom: nom.value.trim()},
                function(data){
                    /* si le text exist */
                    if(data.result == "error")
                    {
                        Swal.fire({
                            icon: "error",
                            title: "Ce nom est déjà utiliser",
                            text: "",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#6775d6"
                        })
                        choix_form_ohnous.classList.remove("null");
                        choix_form_ohnous.innerHTML = data.msg;
                        btn.removeAttribute("disabled");
                        btn.innerHTML = temp_btn;
                        return;
                    }
                }
            )
        }
    }
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
        else
        {
            $.post("fonctions/check_email_user.php",
                {email: email.value.trim()},
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
                        email.focus();
                        btn.removeAttribute("disabled");
                        btn.innerHTML = temp_btn;
                        return;
                    }
                }
            )
        }
    }
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
    $.post("fonctions/signin_user.php",
        $(form).serialize() + "&email=" + encodeURIComponent(email.value.trim()) + "&user_name=" + encodeURIComponent(nom.value.trim()) + "&mdp=" + encodeURIComponent(password[0].value),
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
                window.location = getAuthRedirect('/compte');
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

/* mettre un nom */
function changeName(newNom = "")
{
    choix_form_ohnous.classList.add("null");
    choix_form_ohnous.innerHTML = "";
    nom.value = newNom;
}
/* lire l'écriture du nom */
nom.addEventListener("input",function(){
    choix_form_ohnous.classList.add("null");
    choix_form_ohnous.innerHTML = "";
})
