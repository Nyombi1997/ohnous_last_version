<?php
    ohnous_require_admin_or_redirect();

    $token = html_entity_decode(filter_var($_GET['token'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $request = null;
    $boutique = null;

    if($token !== '')
    {
        ohnous_ensure_store_activation_request_schema();
        $request = only_select("boutique_activation_requests", "token = '".addslashes($token)."'", null, null);
        $boutique = $request ? only_select("boutiques", "id = ".(int)$request['boutique_id'], null, null) : null;
    }

    $requests = $token === '' ? ohnous_admin_fetch_store_activation_requests() : [];
?>
<script>
    let home_page = true;
</script>

<?php if($token !== ''): ?>
    <div class="content_page">
        <section class="liquid-panel activation-panel activation-panel--admin">
            <div class="activation-panel__icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="activation-panel__content">
                <h1>Activation boutique</h1>
                <?php if(!$request || !$boutique): ?>
                    <p>Le lien d’activation est invalide ou expiré.</p>
                    <div class="activation-panel__details">
                        <a href="/admin-activation-boutique" class="btn_ohnous second">Voir les demandes</a>
                    </div>
                <?php else: ?>
                    <p>Définissez la durée d’activation de la boutique.</p>
                    <div class="activation-panel__details vertical">
                        <span><strong>Boutique :</strong> <?= htmlspecialchars($boutique['nom'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Email :</strong> <?= htmlspecialchars((string)$boutique['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Statut compte :</strong> <?= ohnous_is_store_active($boutique) ? 'Active' : 'Non activée' ?></span>
                        <span><strong>WhatsApp :</strong> <?= htmlspecialchars((string)($request['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Appel :</strong> <?= htmlspecialchars((string)($request['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Instagram :</strong> <?= htmlspecialchars((string)($request['instagram'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Facebook :</strong> <?= htmlspecialchars((string)($request['facebook'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>TikTok :</strong> <?= htmlspecialchars((string)($request['tiktok'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Soumission :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$request['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></span>
                        <span><strong>Traitement :</strong> <?= ohnous_get_store_activation_status_label($request['statut'] ?? 'en_attente') ?></span>
                    </div>
                    <?php if(($request['statut'] ?? '') === 'en_attente'): ?>
                        <form id="admin_store_activation_form" class="activation-admin-form">
                            <input type="hidden" id="activation_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="form_ohnous">
                                <i class="fa-regular fa-calendar"></i>
                                <input type="number" min="0" id="activation_months" placeholder="Nombre de mois">
                            </div>
                            <div class="form_ohnous">
                                <i class="fa-regular fa-clock"></i>
                                <input type="number" min="0" id="activation_days" placeholder="Nombre de jours">
                            </div>
                            <button type="submit" class="btn_ohnous">Activer la boutique</button>
                            <a href="/admin-activation-boutique" class="btn_ohnous second">Voir les demandes</a>
                        </form>
                    <?php else: ?>
                        <a href="/admin-activation-boutique" class="btn_ohnous second">Voir les demandes</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <?php if($request && $boutique && ($request['statut'] ?? '') === 'en_attente'): ?>
        <script src="/asset/js/admin_store_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_store_activation.js") ?>" defer></script>
    <?php endif; ?>
<?php else: ?>
    <div class="content_page admin-page-shell">
        <section class="admin-page-head liquid-panel">
            <div>
                <h1>Activations boutiques</h1>
                <p>Consultez les demandes d’activation des comptes boutiques.</p>
            </div>
            <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
        </section>

        <?= ohnous_render_admin_nav('boutiques') ?>

        <?php if(empty($requests)): ?>
            <div class="empty-liquid-state">
                <div class="empty-liquid-state__icon"><i class="fa-solid fa-store"></i></div>
                <p>Aucune demande d’activation boutique.</p>
            </div>
        <?php else: ?>
            <div class="admin-article-table liquid-panel">
                <table>
                    <thead>
                        <tr>
                            <th>Boutique</th>
                            <th>Compte</th>
                            <th>WhatsApp</th>
                            <th>Appel</th>
                            <th>Instagram</th>
                            <th>Facebook</th>
                            <th>TikTok</th>
                            <th>Soumission</th>
                            <th>Traitement</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($requests as $request): ?>
                            <tr>
                                <td>
                                    <div class="admin-account-card__identity compact">
                                        <img src="<?= htmlspecialchars(ohnous_get_profile_picture($request['profile'] ?? '', 'boutique'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($request['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                        <div>
                                            <strong><?= htmlspecialchars($request['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            <span><?= htmlspecialchars((string)$request['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="status <?= (int)$request['activer'] === 1 ? 'active' : 'inactive' ?>"><?= (int)$request['activer'] === 1 ? 'Active' : 'Non activée' ?></span></td>
                                <td><?= htmlspecialchars((string)($request['whatsapp'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($request['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($request['instagram'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($request['facebook'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($request['tiktok'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$request['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="status"><?= ohnous_get_store_activation_status_label($request['statut'] ?? 'en_attente') ?></span></td>
                                <td>
                                    <?php if(($request['statut'] ?? '') === 'en_attente'): ?>
                                        <a href="/admin-activation-boutique?token=<?= urlencode((string)$request['token']) ?>" class="btn_ohnous">Traiter</a>
                                    <?php else: ?>
                                        <span><?= !empty($request['date_traitement']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string)$request['date_traitement'])), ENT_QUOTES, 'UTF-8') : '-' ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
