	<!-- barre de recherche -->
	<div class="div_search_bar all" id="div_search_bar_all">
		<div class="search_bar">
			<form action="recherche" method="GET">
				<input type="text" class="input_search_bar" id="input_search_bar" name="query" placeholder="Rechercher un article..." required>
				<button type="submit" class="button_search_bar"><i class="fa fa-search"></i></button>
			</form>
		</div>
	</div>
	<!-- intro -->
	<div class="intro-hero">
		<div class="blob-bg"></div>
		<div class="intro-text">
			<h1>Découvrez nos <span id="changing-word-container"><span id="changing-word">Accessoires</span></span></h1>
		</div>
		<!-- bouton decouvrir en bas -->
		<!-- <button class="scroll-down-btn visible">
			<i class="fa fa-arrow-down"></i>
		</button> -->

		<!-- barre de recherche -->
		<div class="div_search_bar" id="div_search_bar">
			<div class="search_bar" id="search_bar">
				<form action="recherche" method="GET">
					<input type="text" class="input_search_bar" id="input_search_bar" name="query" placeholder="Rechercher un article..." required>
					<button type="submit" class="button_search_bar"><i class="fa fa-search"></i></button>
				</form>
			</div>
		</div>
		<!-- script banniere -->
		<script>
			const words = ["Parfums", "Perruques", "Produits de beauté","Accessoires"];
			let index = 0;
			const span = document.getElementById("changing-word");
			const input_search_bar = document.getElementById("input_search_bar");

			setInterval(() => {
				index = (index + 1) % words.length;
				span.style.opacity = 0;
				setTimeout(() => {
				span.textContent = words[index];
				span.style.opacity = 1;
				//input_search_bar.setAttribute("placeholder","Rechercher des "+words[index]+"...");
				}, 400);
			}, 3000);
		</script>
	</div>

	<!-- div categories -->
	<div class="parent_div_section_categorie">
		<!-- Swiper -->
		<div class="swiper section_categorie">
			<div class="swiper-wrapper">
				<?php
					/* afficher les categories */
					$categories = select_bdd($bdd, "categorie_article", $where = null, $limit = null, $offset = 0, $order = null, $random = false);
					$category_ids = array();
					foreach ($categories as $category) {
						$detail_category = only_select("categorie", $where = "id = '".$category['categorie']."'", $order = null, $limit = null);
						$detail_article = select_bdd($bdd, "image_articles", $where = "article = '".$category['article']."'", $limit = null, $offset = 0, $order = null, $random = true);
						if(in_array($detail_category['id'], $category_ids)) {
							continue; // Passer à l'itération suivante si l'ID de catégorie a déjà été traité
						}
						echo '
							<!-- details -->
							<a href="'.$detail_category['slug'].'" class="swiper-slide">
								<div class="section_categorie_nom">
									<p>'.$detail_category['nom'].'</p>
								</div>
								<div class="section_categorie_img" style="background: '.$detail_article[0]['background'].';">
									<img 
										class="blur-up"
										src="'.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-50,blur-10" 
										srcset="
											'.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-400,q-80 400w,
											'.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-800,q-80 800w,
											'.$detail_article[0]['img'].'?updatedAt=1765131265242/image.webp?tr=w-1200,q-80 1200w"
										sizes="(max-width:768px) 90vw, 600px"
										loading="lazy"
										class="blur-up"
									/>
								</div>
							</a>';
						$category_ids[] = $detail_category['id'];
					}
				?>
			</div>
		</div>
		
        <script>
            var swiper = new Swiper('.section_categorie', {
                slidesPerView: "auto",
                spaceBetween: 10,
                freeMode: true,
                autoplay: {
                    delay: 1000,
                    disableOnInteraction: true,
                }
            });
        </script>
	</div>

	<style>

	</style>

	<div class="container_affiche_produit">
		<?php
			$donnee = select_bdd($bdd, "articles", $where = null, $limit = 12, $offset = 0, $order = null, $random = false);
			foreach($donnee as $data)
			{
				affiche_produit($data);
			}
		?>
	</div>
