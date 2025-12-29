/* afficher panier */
let
afficher_panier = document.querySelectorAll("#afficher_panier"),
sortie_panier = document.querySelectorAll("#sortie_panier"),
div_slide_panier = document.getElementById("div_slide_panier");

afficher_panier.forEach(function(element){
    element.addEventListener("click",function(e){
        e.preventDefault();
        if(!div_slide_panier.classList.contains("active")){
            div_slide_panier.classList.add("active");
        }
        else
        {
            div_slide_panier.classList.remove("active");
        }
    })
})
sortie_panier.forEach(function(element){
    element.addEventListener("click",function(e){
        div_slide_panier.classList.remove("active");
    })
})
/* recalculer la taille de l'image pour lui donner un style */
function recalculateImageStyle(imgUrl) {
    return new Promise((resolve) => {
        const img = new Image();
        img.src = imgUrl;

        img.onload = () => {
            if (img.width > img.height) {
                resolve('width: 100%; height: auto;');
            } else if (img.width < img.height) {
                resolve('width: auto; height: 100%;');
            } else {
                resolve('width: 100%; height: 100%;');
            }
        };
    });
}
/* toutes les images de produit */
document.querySelectorAll(".img_affiche").forEach(async function(imgElement){
    let imgUrl = imgElement.getAttribute("data-src");
    let style = await recalculateImageStyle(imgUrl);
    imgElement.setAttribute("style", style);
});