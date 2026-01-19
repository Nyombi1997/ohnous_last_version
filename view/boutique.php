<?php
    /* si boutique */
    if(isset($GLOBALS['boutique']))
    {
        $boutique = $GLOBALS['boutique'];
    }
    else
    {
        // Rediriger vers une page d'erreur ou afficher un message
        header("Location:/404");
        exit();
    }
?>
<script>
    let home_page = true;
</script>
<style>
    :root {
    --primary: #1a1a1a;
    --accent: #ff4757;
}

body {
    margin: 0;
    font-family: 'Inter', sans-serif;
    background-color: #f4f4f4;
    color: var(--primary);
}

/* Navigation Flottante */
.glass-nav {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    width: 90%;
    height: 60px;
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 30px;
    border-radius: 50px;
    z-index: 100;
    border: 1px solid rgba(255,255,255,0.3);
}

/* Grille Bento */
.grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    grid-auto-rows: 300px;
    grid-auto-flow: dense;
    gap: 15px;
    padding: 100px 20px 20px 20px;
}

.item {
    position: relative;
    background-size: cover;
    background-position: center;
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    cursor: pointer;
}

.item:hover {
    transform: scale(0.98);
}

/* Variantes de tailles */
.tall { grid-row: span 2; }
.wide { grid-column: span 2; }

/* Overlays */
.overlay {
    position: absolute;
    bottom: 0; padding: 40px;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    width: 100%;
}

.overlay-mini {
    position: absolute;
    bottom: 20px; left: 20px;
    background: white;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: bold;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
</style>

    <main class="grid-container">
        <section class="item tall" style="background-image: url('https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80')">
            <div class="overlay">
                <span>Nouvelle Collection</span>
                <h2>L'Élégance Minimaliste</h2>
                <button class="btn-shop">Découvrir</button>
            </div>
        </section>

        <section class="item" style="background-image: url('https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=500&q=80')">
            <div class="overlay-mini">
                <p>Sneakers Air</p>
                <span>120€</span>
            </div>
        </section>

        <section class="item wide" style="background-image: url('https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80')">
            <div class="overlay-mini">
                <p>Accessoires Tech</p>
            </div>
        </section>

        <section class="item wide" style="background-image: url('https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80')">
            <div class="overlay-mini">
                <p>Accessoires Tech</p>
            </div>
        </section>

        <section class="item" style="background-image: url('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80')">
            <div class="overlay-mini">
                <p>Audio Premium</p>
            </div>
        </section>
    </main>

    <script>
        const cards = document.querySelectorAll('.item');

        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                // Effet de brillance qui suit la souris
                card.style.setProperty('--mouse-x', `${x}px`);
                card.style.setProperty('--mouse-y', `${y}px`);
                
                // Légère inclinaison (Tilt effect)
                const xc = rect.width / 2;
                const yc = rect.height / 2;
                const dx = x - xc;
                const dy = y - yc;
                
                card.style.transform = `rotateY(${dx / 20}deg) rotateX(${-dy / 20}deg) scale(0.98)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = `rotateY(0deg) rotateX(0deg) scale(1)`;
            });
        });
    </script>