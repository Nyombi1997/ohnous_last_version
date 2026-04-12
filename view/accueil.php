<?php
    ohnous_sync_test_store_activation();

    $visibleArticles = ohnous_get_visible_articles(null, 0, null, true);
    $visibleArticles = array_values($visibleArticles);

    $categoryCards = [];
    $all_categories = [];

    foreach($visibleArticles as $article)
    {
        $categoryLink = only_select("categorie_article", "article = '".(int)$article['id']."'", null, null);
        if(!$categoryLink)
        {
            continue;
        }

        $detail_category = only_select("categorie", "id = '".(int)$categoryLink['categorie']."'", null, null);
        $detail_article_image = ohnous_get_article_primary_image((int)$article['id']);

        if(!$detail_category || !$detail_article_image)
        {
            continue;
        }

        if(isset($categoryCards[(int)$detail_category['id']]))
        {
            continue;
        }

        $categoryCards[(int)$detail_category['id']] = [
            'categorie' => $detail_category,
            'image' => $detail_article_image,
        ];
        $all_categories[] = $detail_category['nom'];
    }

    if(empty($all_categories))
    {
        $all_categories[] = 'Articles';
    }
?>
<script>
    let home_page = true;
</script>
<div class="intro-hero">
    <div class="blob-bg"></div>
    <div class="intro-text">
        <h1>Découvrez <span id="changing-word-container"><span id="changing-word"><?= htmlspecialchars($all_categories[0], ENT_QUOTES, 'UTF-8') ?></span></span></h1>
    </div>

    <div class="div_search_bar" id="div_search_bar">
        <div class="search_bar" id="search_bar">
            <form action="/q" method="GET">
                <input type="text" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" class="input_search_bar" id="input_search_bar_2" name="query" placeholder="Rechercher un article..." required oninput="rechercheArticles(this.value)" onfocus="rechercheArticles(this.value)">
                <button type="submit" class="button_search_bar"><i class="fa fa-search"></i></button>
            </form>
            <div class="donnee_de_recherche null" id="donnee_de_recherche"></div>
        </div>
    </div>

    <script>
        const words = <?= json_encode(array_values($all_categories), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        let index = 0;
        const span = document.getElementById("changing-word");

        setInterval(() => {
            if(words.length === 0){
                return;
            }
            index = (index + 1) % words.length;
            span.style.opacity = 0;
            setTimeout(() => {
                span.textContent = words[index];
                span.style.opacity = 1;
            }, 400);
        }, 3000);
    </script>
</div>

<div class="parent_div_section_categorie">
    <div class="swiper section_categorie">
        <div class="swiper-wrapper">
            <?php foreach($categoryCards as $card): ?>
                <?php
                    $detail_category = $card['categorie'];
                    $detail_article = $card['image'];
                    $liquid_image = ohnous_prepare_liquid_image($detail_article['img'], '(max-width: 768px) 35vw, 180px');
                ?>
                <a href="categorie/<?= htmlspecialchars($detail_category['slug'], ENT_QUOTES, 'UTF-8') ?>" class="swiper-slide">
                    <div class="section_categorie_nom">
                        <p><?= htmlspecialchars($detail_category['nom'], ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="section_categorie_img" style="background: <?= htmlspecialchars((string)$detail_article['background'], ENT_QUOTES, 'UTF-8') ?>;">
                        <img
                            class="blur-up js-liquid-image"
                            src="<?= htmlspecialchars($liquid_image['placeholder'], ENT_QUOTES, 'UTF-8') ?>"
                            data-image-base="<?= htmlspecialchars($liquid_image['base'], ENT_QUOTES, 'UTF-8') ?>"
                            data-image-fallback="<?= htmlspecialchars($liquid_image['fallback'], ENT_QUOTES, 'UTF-8') ?>"
                            data-image-high="<?= htmlspecialchars($liquid_image['high'], ENT_QUOTES, 'UTF-8') ?>"
                            data-image-srcset="<?= htmlspecialchars($liquid_image['srcset'], ENT_QUOTES, 'UTF-8') ?>"
                            data-image-sizes="<?= htmlspecialchars($liquid_image['sizes'], ENT_QUOTES, 'UTF-8') ?>"
                            loading="lazy"
                            alt="<?= htmlspecialchars($detail_category['nom'], ENT_QUOTES, 'UTF-8') ?>"
                        />
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        var swiper = new Swiper('.section_categorie', {
            slidesPerView: "auto",
            spaceBetween: 10,
            freeMode: true,
            autoplay: {
                delay: 2500,
                disableOnInteraction: true,
            }
        });
    </script>
</div>

<div class="container_affiche_produit">
    <?php
        $homeArticles = array_slice($visibleArticles, 0, 12);
        foreach($homeArticles as $data)
        {
            affiche_produit($data);
        }
        echo '
            <div class="div_btn_voir_plus">
                <a href="/articles" class="btn_voir_plus" role="button">Voir plus  <i class="fa-solid fa-arrow-right-long"></i></a>
            </div>';
    ?>
</div>
