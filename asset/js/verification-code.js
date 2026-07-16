let 
code = document.getElementById("code"),
btn = document.querySelector('button[type="submit"]'),
form = document.getElementById("form");
/* si on soumet le formulaire */
form.addEventListener("submit",function(e){
    e.preventDefault();
    btn.setAttribute("disabled","");
    let temp_btn = btn.innerHTML;
    btn.innerHTML = `<i class="fa-solid fa-circle-notch rotate"></i>`;


    /* si c'est une incription boutique */
    $.post("fonctions/code_password_recovery.php",
        $(form).serialize() + "&code=" + encodeURIComponent(code.value.trim()),
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
                window.location = '/nouveau-mot-de-passe';
            }
        }
    )
})
