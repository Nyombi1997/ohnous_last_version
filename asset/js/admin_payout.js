(function ($) {
    var form=document.getElementById('admin_payout_form'), input=document.getElementById('payout_phone'), hidden=document.getElementById('payout_phone_international');
    if(!form||!input) return;
    var iti=window.intlTelInput(input,{initialCountry:'cd',preferredCountries:['cd'],separateDialCode:true});
    form.addEventListener('submit',function(e){e.preventDefault(); if(!iti.isValidNumber()){Swal.fire({icon:'error',title:'Numéro Mobile Money invalide.'});return;} hidden.value=iti.getNumber(); var button=form.querySelector('button[type="submit"]'), old=button.innerHTML; button.disabled=true; button.innerHTML='<i class="fa-solid fa-circle-notch rotate"></i>';
        $.post('/payout-demarrer',$(form).serialize(),function(data){var redirect=data.redirect||(data.reference?'/admin-payout-suivi?reference='+encodeURIComponent(data.reference):'');if(data.result==='ok'){window.location.href=redirect;}else{Swal.fire({icon:'error',title:'PayOut refusé',text:data.msg||'PayOut impossible.'}).then(function(){if(redirect)window.location.href=redirect;});}},'json').fail(function(xhr){var data=xhr.responseJSON||{},redirect=data.redirect||(data.reference?'/admin-payout-suivi?reference='+encodeURIComponent(data.reference):'');Swal.fire({icon:'error',title:'PayOut impossible',text:data.msg||data.technical_error||'FreshPay ne répond pas.'}).then(function(){if(redirect)window.location.href=redirect;});}).always(function(){button.disabled=false;button.innerHTML=old;});
    });
})(jQuery);
