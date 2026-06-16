/* aller vers modifier profile */
let
edit_profil = document.getElementById("edit_profil");
edit_profil.addEventListener("click",function(){
    window.location = '/editer-profile-boutique';
})

/* valider le nouveau nom */
let
form_nom = document.getElementById("form_nom"),
new_boutique = document.getElementById("new_boutique"),
choix_form_ohnous = document.getElementById("choix_form_ohnous");
nom = document.getElementById("nom"),
valide_nom = document.getElementById("valide_nom");

form_nom.addEventListener("submit",function(e){
    e.preventDefault();
    let
    value_nom = nom.value.trim();
    value_nom = nom.value.replace(/ +/g,"");
    let erreur = "Entrez un nom d'utilisateur";
    let link = "check_nom_utilisateur";
    /* si c'est une boutique */
    if(new_boutique != undefined)
    {
        erreur = "Entrez un nom de boutique";
        link = "check_nom_boutique_online";
    }
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
        return;
    }
    else
    {
        valide_nom.setAttribute("disabled","");
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
                    valide_nom.removeAttribute("disabled");
                    return;
                }
                else
                {
                    Swal.fire({
                        icon: "success",
                        title: "Le nom a été modifier",
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#6775d6",
                        iconColor: "#6775d6",
                        timer: 1000
                    })
                    valide_nom.removeAttribute("disabled");
                }
            }
        )
    }
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

/* checking adresse email */
let
form_email = document.getElementById("form_email"),
email = document.getElementById("email"),
valide_email = document.getElementById("valide_email");
let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

form_email.addEventListener("submit",function(e){
    e.preventDefault();
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
        return;
    }
    else
    {
        valide_email.setAttribute("disabled","");
        $.post("fonctions/check_edit_email_boutique.php",
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
                    valide_email.removeAttribute("disabled");
                    return;
                }
                else
                {
                    Swal.fire({
                        icon: "success",
                        title: "L'adresse email a été modifier",
                        text: "",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#6775d6",
                        iconColor: "#6775d6",
                        timer: 1000
                    })
                    valide_email.removeAttribute("disabled");
                }
            }
        )
    }
})

/* let check new password */
let form_password = document.getElementById("form_password"),
password = document.querySelectorAll("#password"),
valid_password = document.getElementById("valid_password");

form_password.addEventListener("submit",function(e){
    e.preventDefault();
    /* checker password */
    if(password.length>1)
    {
        if(password[1].value.length < 6)
        {
            Swal.fire({
                icon: "error",
                title: "Le mot de passe doit avoir au moins 6 caractères",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            }).then(() => {
                password[1].focus();
            })
            return;
        }
        else if(password[0].value == password[1].value)
        {
            Swal.fire({
                icon: "error",
                title: "Veuillez entrer un nouveau mot de passe",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            }).then(() => {
                password[1].focus();
            })
            return;
        }
        else if(password[2].value != password[1].value)
        {
            Swal.fire({
                icon: "error",
                title: "Les mots de passe ne sont pas identique.",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            }).then(() => {
                password[1].focus();
            })
            return;
        }
    }
    /* checker si le password actuelle est correcte */
    valid_password.setAttribute("disabled","");
    $.post("fonctions/check_edit_password_boutique.php",
        {
            mdp: password[0].value,
            mdp1: password[1].value,
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
                email.focus();
                valid_password.removeAttribute("disabled");
                return;
            }
            else
            {
                Swal.fire({
                    icon: "success",
                    title: "Le mot de passe a été changé avec succès !",
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6",
                    iconColor: "#6775d6",
                    timer: 1000
                })
                valid_password.removeAttribute("disabled");
                password.forEach(function(element){
                    element.value = "";
                })
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

/* lire l'écriture de la description */
let
description = document.getElementById("description"),
form_description = document.getElementById("form_description"),
valid_description = document.getElementById("valid_description");
form_description.addEventListener("submit",function(e){
    e.preventDefault();
    valid_description.setAttribute("disabled","");
    let temp_btn = valid_description.innerHTML;
    valid_description.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;
    $.post("fonctions/check_edit_description_boutique.php",
        {
            description: description.value.trim(),
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
                valid_description.removeAttribute("disabled");
                valid_description.innerHTML = temp_btn;
                return;
            }
            else
            {
                Swal.fire({
                    icon: "success",
                    title: "La descriprion de la boutique a été changer !",
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6",
                    iconColor: "#6775d6",
                    timer: 1000
                })
                valid_description.removeAttribute("disabled");
                valid_description.innerHTML = temp_btn;
            }
        }
    )
})

/* liens sociaux */
let form_socials = document.getElementById("form_socials");
if(form_socials){
    const socialsErrors = document.getElementById("store_socials_errors");
    const validSocials = document.getElementById("valid_socials");
    const socialFields = {
        facebook: document.getElementById("facebook"),
        instagram: document.getElementById("instagram"),
        twitter: document.getElementById("twitter"),
        trends: document.getElementById("trends"),
        tiktok: document.getElementById("tiktok")
    };

    const socialPatterns = {
        facebook: /^$|^(https?:\/\/)?(www\.)?(facebook\.com|fb\.com)\/[A-Za-z0-9._\-/?=&%]+$/i,
        instagram: /^$|^(https?:\/\/)?(www\.)?instagram\.com\/[A-Za-z0-9._\-/?=&%]+$/i,
        twitter: /^$|^(https?:\/\/)?(www\.)?(x\.com|twitter\.com)\/[A-Za-z0-9._\-/?=&%]+$/i,
        trends: /^$|^(https?:\/\/)?(www\.)?threads\.net\/@[A-Za-z0-9._\-/?=&%]+$/i,
        tiktok: /^$|^(https?:\/\/)?(www\.)?(tiktok\.com|vm\.tiktok\.com)\/[A-Za-z0-9._\-/?=&%]+$/i
    };

    function normalizeUrl(value){
        const trimmed = value.trim();
        if(trimmed === '' || /^https?:\/\//i.test(trimmed)){
            return trimmed;
        }
        return 'https://' + trimmed;
    }

    function renderSocialErrors(errors){
        socialsErrors.innerHTML = '';
        if(errors.length === 0){
            socialsErrors.classList.remove('is-visible');
            return;
        }

        socialsErrors.classList.add('is-visible');
        socialsErrors.innerHTML = errors.map(function(error){
            return `<p>${error}</p>`;
        }).join('');
    }

    function validateSocials(){
        const errors = [];

        Object.keys(socialFields).forEach(function(key){
            const field = socialFields[key];
            if(!field){
                return;
            }

            let value = normalizeUrl(field.value.trim());
            field.value = value;

            if(!socialPatterns[key].test(value)){
                const labels = {
                    facebook: "Le lien Facebook n'est pas valide.",
                    instagram: "Le lien Instagram n'est pas valide.",
                    twitter: "Le lien X / Twitter n'est pas valide.",
                    trends: "Le lien Threads n'est pas valide.",
                    tiktok: "Le lien TikTok n'est pas valide."
                };
                errors.push(labels[key]);
                field.classList.add('is-invalid');
            }else{
                field.classList.remove('is-invalid');
            }
        });

        renderSocialErrors(errors);
        return errors;
    }

    Object.keys(socialFields).forEach(function(key){
        if(socialFields[key]){
            socialFields[key].addEventListener('input', validateSocials);
        }
    });

    form_socials.addEventListener("submit", function(e){
        e.preventDefault();

        const errors = validateSocials();
        if(errors.length > 0){
            Swal.fire({
                icon: "error",
                title: "Corrigez les liens",
                text: "Certaines informations de contact ne sont pas valides.",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            });
            return;
        }

        validSocials.setAttribute("disabled", "");
        const tempBtn = validSocials.innerHTML;
        validSocials.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;

        $.post("fonctions/save_store_socials.php", {
            facebook: socialFields.facebook.value.trim(),
            instagram: socialFields.instagram.value.trim(),
            twitter: socialFields.twitter.value.trim(),
            trends: socialFields.trends.value.trim(),
            tiktok: socialFields.tiktok.value.trim()
        }, function(data){
            if(data.result === "error"){
                Swal.fire({
                    icon: "error",
                    title: data.msg,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                });
            }else{
                Swal.fire({
                    icon: "success",
                    title: "Les liens ont été enregistrés",
                    timer: 1400,
                    showConfirmButton: false
                });
            }
        }, "json").always(function(){
            validSocials.removeAttribute("disabled");
            validSocials.innerHTML = tempBtn;
        });
    });
}
