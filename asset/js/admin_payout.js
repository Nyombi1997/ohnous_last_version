(function ($) {
    var form=document.getElementById('admin_payout_form'), input=document.getElementById('payout_phone'), hidden=document.getElementById('payout_phone_international');
    if(!form||!input) return;
    var iti=window.intlTelInput(input,{initialCountry:'cd',preferredCountries:['cd'],separateDialCode:true});
    form.addEventListener('submit',function(e){e.preventDefault(); if(!iti.isValidNumber()){Swal.fire({icon:'error',title:'Numéro Mobile Money invalide.'});return;} hidden.value=iti.getNumber(); var button=form.querySelector('button[type="submit"]'), old=button.innerHTML; button.disabled=true; button.innerHTML='<i class="fa-solid fa-circle-notch rotate"></i>';
        $.post('/payout-demarrer',$(form).serialize(),function(data){if(data.result==='ok'){window.location.href=data.redirect||('/admin-payout-suivi?reference='+encodeURIComponent(data.reference));}else{Swal.fire({icon:'error',title:data.msg||'PayOut impossible.'});}},'json').fail(function(xhr){Swal.fire({icon:'error',title:(xhr.responseJSON||{}).msg||'PayOut impossible.'});}).always(function(){button.disabled=false;button.innerHTML=old;});
    });
})(jQuery);
