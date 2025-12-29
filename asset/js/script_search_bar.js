
function setupIntersectionObserver() {
    const searchBar = document.getElementById('search_bar');
    const DivSearchBar = document.getElementById('div_search_bar');
    const div_search_bar_all = document.getElementById('div_search_bar_all');
    
    if(searchBar != undefined && searchBar != null){
        const observer = new IntersectionObserver(
            (entries) => {
            entries.forEach(entry => {
                if (entry.boundingClientRect.top <= 90) {
                    DivSearchBar.classList.add('active');
                    if(div_search_bar_all !=undefined)
                    {
                        div_search_bar_all.classList.add('active');
                    }
                } else {
                    DivSearchBar.classList.remove('active');
                    if(div_search_bar_all !=undefined)
                    {
                        div_search_bar_all.classList.remove('active');                        
                    }
                }
            });
            },
            {
            // Pas de root spécifié = viewport
            threshold: Array.from({length: 101}, (_, i) => i / 100) // Tous les 1%
            }
        );
        
        observer.observe(searchBar);
    }
    else
    {
        if(div_search_bar_all !=undefined)
        {
            div_search_bar_all.classList.add('active');                        
        }
    }
}

// Initialiser quand le DOM est chargé
document.addEventListener('DOMContentLoaded', setupIntersectionObserver);