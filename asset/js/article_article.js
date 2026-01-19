/* gestion des tritres de l'article en vue */
document.querySelectorAll(".js_titre_details_article").forEach(function(element,index){
    element.addEventListener("click",function(){
        let i = index + 1;
        document.querySelectorAll(".js_background_details_article").forEach(function(el){
            i = i * 50;
            el.setAttribute("style","left:"+(i - 50)+"%;");
        })
        /* si c'est le premier index */
        if(index == 0){
            document.querySelectorAll(".js_description_vu_article").forEach(function(el){
                el.classList.remove("null");
            })
        }else{
            document.querySelectorAll(".js_description_vu_article").forEach(function(el){
                el.classList.add("null");
            })
        }
        /* si c'est le deuxieme index */
        if(index == 1){
            document.querySelectorAll(".js_note_vu_article").forEach(function(el){
                el.classList.remove("null");
            })
        }else{
            document.querySelectorAll(".js_note_vu_article").forEach(function(el){
                el.classList.add("null");
            })
        }
    })
})

/* gestion des avis */
const stars = document.querySelectorAll('.star');
const ratingText = document.getElementById('rating-value');
const submitBtn = document.getElementById('submit-rating');
const commentInput = document.getElementById('comment-text');
const reviewsList = document.getElementById('reviews-list');

let selectedRating = 0;

// 1. Gestion du clic sur les étoiles
stars.forEach(star => {
star.addEventListener('click', () => {
    selectedRating = star.getAttribute('data-value');
    updateStars(selectedRating);
    ratingText.innerText = `Note : ${selectedRating}/5`;
});
});

function updateStars(rating) {
stars.forEach(star => {
    // On colorie l'étoile si sa valeur est <= à la note choisie
    star.style.color = star.getAttribute('data-value') <= rating ? '#ffcc00' : '#ccc';
});
}

// 2. Sauvegarde et affichage du commentaire
submitBtn.addEventListener('click', () => {
const comment = commentInput.value.trim();

if (selectedRating === 0) {
    alert("Veuillez choisir une note !");
    return;
}

if (comment === "") {
    alert("Le commentaire ne peut pas être vide.");
    return;
}

// Création de l'affichage du commentaire
const reviewDiv = document.createElement('div');
reviewDiv.classList.add('review');
reviewDiv.innerHTML = `
    <strong>Note: ${selectedRating}/5</strong>
    <p>${comment}</p>
    <small>Posté le ${new Date().toLocaleDateString()}</small>
    <hr>
`;

// Ajout à la liste
reviewsList.prepend(reviewDiv);

// Réinitialisation
commentInput.value = "";
selectedRating = 0;
updateStars(0);
ratingText.innerText = "Note : 0/5";
});