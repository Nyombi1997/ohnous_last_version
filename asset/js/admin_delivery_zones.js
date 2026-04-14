(function ($) {
    var settingsForm = document.getElementById("delivery_settings_form");
    var zoneForm = document.getElementById("delivery_zone_form");
    var resetButton = document.getElementById("reset_delivery_zone_form");

    function resetZoneForm() {
        if (!zoneForm) {
            return;
        }

        zoneForm.reset();
        document.getElementById("delivery_zone_id").value = "0";
        document.getElementById("delivery_zone_active").checked = true;
    }

    if (settingsForm) {
        settingsForm.addEventListener("submit", function (e) {
            e.preventDefault();

            $.post("/fonctions/admin_delivery_zones.php", $(settingsForm).serialize() + "&action=save_settings", function (data) {
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
    }

    if (zoneForm) {
        zoneForm.addEventListener("submit", function (e) {
            e.preventDefault();

            $.post("/fonctions/admin_delivery_zones.php", $(zoneForm).serialize() + "&action=save_zone", function (data) {
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
    }

    if (resetButton) {
        resetButton.addEventListener("click", resetZoneForm);
    }

    $(document).on("click", ".js-edit-delivery-zone", function () {
        document.getElementById("delivery_zone_id").value = this.getAttribute("data-zone-id") || "0";
        document.getElementById("delivery_zone_name").value = this.getAttribute("data-zone-name") || "";
        document.getElementById("delivery_zone_price").value = this.getAttribute("data-zone-price") || "0.00";
        document.getElementById("delivery_zone_active").checked = this.getAttribute("data-zone-active") === "1";
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

    $(document).on("click", ".js-toggle-delivery-zone", function () {
        var zoneId = this.getAttribute("data-zone-id");
        $.post("/fonctions/admin_delivery_zones.php", {
            action: "toggle_zone",
            zone_id: zoneId
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
