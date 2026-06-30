(function ($) {
    var form = document.getElementById("admin_payment_settings_form");
    var toggle = document.getElementById("admin_payment_enabled");

    if (!form || !toggle) {
        return;
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        $.post("/fonctions/admin_payment_settings.php", {
            enabled: toggle.checked ? 1 : 0
        }, function (data) {
            Swal.fire({
                icon: data.result === "ok" ? "success" : "error",
                title: data.msg,
                confirmButtonColor: "#6775d6"
            }).then(function () {
                if (data.result === "ok") {
                    window.location.reload();
                }
            });
        }, "json");
    });
})(jQuery);
