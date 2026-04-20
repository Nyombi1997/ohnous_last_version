/* gestion des titres de l'article en vue */
document.querySelectorAll(".js_titre_details_article").forEach(function(element, index){
    element.addEventListener("click", function(){
        let i = index + 1;
        document.querySelectorAll(".js_background_details_article").forEach(function(el){
            i = i * 50;
            el.setAttribute("style", "left:" + (i - 50) + "%;");
        });

        if (index === 0) {
            document.querySelectorAll(".js_description_vu_article").forEach(function(el){
                el.classList.remove("null");
            });
        } else {
            document.querySelectorAll(".js_description_vu_article").forEach(function(el){
                el.classList.add("null");
            });
        }

        if (index === 1) {
            document.querySelectorAll(".js_note_vu_article").forEach(function(el){
                el.classList.remove("null");
            });
        } else {
            document.querySelectorAll(".js_note_vu_article").forEach(function(el){
                el.classList.add("null");
            });
        }
    });
});

document.addEventListener("DOMContentLoaded", function(){
    const shareButton = document.querySelector(".js-article-share-trigger");
    const shareConfig = window.articleShareConfig || null;

    function openShareFallback() {
        if (!shareConfig) {
            return;
        }

        const url = encodeURIComponent(shareConfig.url || window.location.href);
        const text = encodeURIComponent(shareConfig.text || shareConfig.title || document.title);
        const title = encodeURIComponent(shareConfig.title || document.title);

        Swal.fire({
            title: "Partager cet article",
            html: `
                <div class="share-network-grid">
                    <a class="share-network-link" href="https://www.facebook.com/sharer/sharer.php?u=${url}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span></a>
                    <a class="share-network-link" href="https://wa.me/?text=${text}%20${url}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-whatsapp"></i><span>WhatsApp</span></a>
                    <a class="share-network-link" href="https://twitter.com/intent/tweet?text=${text}&url=${url}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-x-twitter"></i><span>X</span></a>
                    <a class="share-network-link" href="https://t.me/share/url?url=${url}&text=${text}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-telegram"></i><span>Telegram</span></a>
                    <a class="share-network-link" href="https://www.linkedin.com/sharing/share-offsite/?url=${url}" target="_blank" rel="noopener noreferrer"><i class="fa-brands fa-linkedin-in"></i><span>LinkedIn</span></a>
                    <a class="share-network-link" href="mailto:?subject=${title}&body=${text}%20${url}"><i class="fa-solid fa-envelope"></i><span>Email</span></a>
                </div>
                <button type="button" class="btn_ohnous share-copy-btn js-copy-share-link">Copier le lien</button>
            `,
            showConfirmButton: false,
            customClass: {
                popup: "swal-liquid-popup"
            },
            didOpen: function(){
                $(".js-copy-share-link").off("click").on("click", function(){
                    const link = shareConfig.url || window.location.href;
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(link).then(function(){
                            Swal.fire({
                                icon: "success",
                                title: "Lien copi\u00e9",
                                timer: 1400,
                                showConfirmButton: false
                            });
                        });
                        return;
                    }

                    const tempInput = document.createElement("input");
                    tempInput.value = link;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    document.execCommand("copy");
                    document.body.removeChild(tempInput);
                    Swal.fire({
                        icon: "success",
                        title: "Lien copi\u00e9",
                        timer: 1400,
                        showConfirmButton: false
                    });
                });
            }
        });
    }

    if (shareButton) {
        shareButton.addEventListener("click", async function(){
            if (!shareConfig) {
                return;
            }

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: shareConfig.title,
                        text: shareConfig.text,
                        url: shareConfig.url
                    });
                    return;
                } catch (error) {
                    if (error && error.name === "AbortError") {
                        return;
                    }
                }
            }

            openShareFallback();
        });
    }
});

(function($){
    const config = window.articleReviewsConfig || null;
    if (!config) {
        return;
    }

    const $stars = $("#star-rating .star");
    const $ratingText = $("#rating-value");
    const $submitBtn = $("#submit-rating");
    const $commentInput = $("#comment-text");
    const $reviewsList = $("#reviews-list");
    const $summary = $("#article-review-summary");
    const $reviewFeedCount = $("#review-feed-count");
    const $openReviewAuth = $("#open-review-auth");

    let selectedRating = 0;
    let isLoading = false;
    let latestReviewId = 0;

    function setReviewRedirect(){
        localStorage.setItem("ohnous_after_auth_redirect", window.location.pathname + window.location.search + window.location.hash);
    }

    function openAuthPopup(){
        Swal.fire({
            icon: "info",
            title: "Connexion requise",
            html: `
                <div class="swal-review-auth">
                    <p>Pour laisser un avis ou un commentaire, connectez-vous ou cr\u00e9ez votre compte OhNous.</p>
                    <div class="swal-review-auth__actions">
                        <button type="button" class="btn_ohnous js-review-auth-link" data-href="${config.loginUrl}">Se connecter</button>
                        <button type="button" class="btn_ohnous second js-review-auth-link" data-href="${config.signupUrl}">S'inscrire</button>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            customClass: {
                popup: "swal-liquid-popup"
            },
            didOpen: function(){
                $(".js-review-auth-link").off("click").on("click", function(){
                    setReviewRedirect();
                    window.location = $(this).data("href");
                });
            }
        });
    }

    function updateStars(rating) {
        $stars.each(function(){
            const starValue = Number($(this).data("value"));
            $(this).toggleClass("active", starValue <= rating);
        });
    }

    function resetForm(){
        selectedRating = 0;
        updateStars(0);
        $ratingText.text("Note : 0/5");
        $commentInput.val("");
    }

    function applyPayload(data){
        if (!data || data.result !== "ok") {
            return;
        }

        $summary.html(data.summary_html);
        $reviewsList.html(data.reviews_html);
        $reviewFeedCount.text(data.summary.count_formatted + " avis");
        latestReviewId = Number(data.latest_review_id || 0);

        const highlightValue = document.querySelector(".liquid-review-box__highlight span");
        const highlightCount = document.querySelector(".liquid-review-box__highlight small");
        if (highlightValue) {
            highlightValue.textContent = data.summary.count > 0 ? data.summary.average_formatted + "/5" : "Nouveau";
        }
        if (highlightCount) {
            highlightCount.textContent = data.summary.count_formatted + " avis";
        }
    }

    function loadReviews(silent){
        if (isLoading) {
            return;
        }

        isLoading = true;
        $.post("/fonctions/article_reviews.php", {
            action: "fetch",
            article_id: config.articleId
        }, function(data){
            if (data.result === "ok") {
                if (silent && latestReviewId !== 0 && Number(data.latest_review_id || 0) === latestReviewId) {
                    return;
                }
                applyPayload(data);
            }
        }, "json").always(function(){
            isLoading = false;
        });
    }

    function submitReview(){
        if (!config.isConnected) {
            openAuthPopup();
            return;
        }

        const commentaire = ($commentInput.val() || "").trim();

        if (selectedRating === 0) {
            Swal.fire({
                icon: "error",
                title: "Choisissez une note",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            });
            return;
        }

        if (commentaire === "") {
            Swal.fire({
                icon: "error",
                title: "Ajoutez un commentaire",
                text: "Votre avis a besoin d'un petit texte pour \u00eatre publi\u00e9.",
                confirmButtonText: "OK",
                confirmButtonColor: "#6775d6"
            });
            return;
        }

        $submitBtn.attr("disabled", "disabled");
        const tempBtn = $submitBtn.html();
        $submitBtn.html('<i class="fa-solid fa-circle-notch rotate"></i>');

        $.post("/fonctions/article_reviews.php", {
            action: "create",
            article_id: config.articleId,
            note: selectedRating,
            commentaire: commentaire
        }, function(data){
            if (data.result === "auth_required") {
                openAuthPopup();
                return;
            }

            if (data.result === "schema_required" || data.result === "error") {
                Swal.fire({
                    icon: "error",
                    title: data.msg,
                    confirmButtonText: "OK",
                    confirmButtonColor: "#6775d6"
                });
                return;
            }

            applyPayload(data);
            resetForm();

            Swal.fire({
                icon: "success",
                title: data.msg,
                timer: 1800,
                showConfirmButton: false
            });
        }, "json").always(function(){
            $submitBtn.removeAttr("disabled");
            $submitBtn.html(tempBtn);
        });
    }

    $stars.on("click", function(){
        if (!config.isConnected) {
            openAuthPopup();
            return;
        }

        selectedRating = Number($(this).data("value"));
        updateStars(selectedRating);
        $ratingText.text("Note : " + selectedRating + "/5");
    });

    $stars.on("mouseenter", function(){
        updateStars(Number($(this).data("value")));
    });

    $("#star-rating").on("mouseleave", function(){
        updateStars(selectedRating);
    });

    $commentInput.on("focus", function(){
        if (!config.isConnected) {
            $(this).blur();
            openAuthPopup();
        }
    });

    $submitBtn.on("click", function(){
        submitReview();
    });

    $openReviewAuth.on("click", function(){
        openAuthPopup();
    });

    loadReviews(true);

    window.setInterval(function(){
        loadReviews(true);
    }, 10000);
})(jQuery);
