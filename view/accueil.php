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
		<div class="div_search_bar">
			<div class="search_bar">
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