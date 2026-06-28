<?php
    ohnous_require_admin_or_redirect();
    $requests = ohnous_admin_fetch_user_activation_requests();
?>
<div class="content_page admin-page-shell">
    <section class="admin-page-head liquid-panel">
        <div>
            <h1>Activations utilisateurs</h1>
            <p>Consultez les demandes et traitez les comptes utilisateur.</p>
        </div>
        <img src="/asset/images/icons/logo-2.png" alt="Logo OhNous">
    </section>

    <?= ohnous_render_admin_nav('utilisateurs') ?>

    <?php if(!ohnous_table_exists('user_activation_requests')): ?>
        <div class="empty-liquid-state">
            <div class="empty-liquid-state__icon"><i class="fa-solid fa-database"></i></div>
            <p>La table des demandes d’activation utilisateur n’est pas encore installée.</p>
        </div>
    <?php elseif(empty($requests)): ?>
        <div class="empty-liquid-state">
            <div class="empty-liquid-state__icon"><i class="fa-regular fa-circle-check"></i></div>
            <p>Aucune demande d’activation utilisateur.</p>
        </div>
    <?php else: ?>
        <div class="admin-table-scroll liquid-panel">
            <table class="admin-data-table">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Statut compte</th>
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
                                    <img src="<?= htmlspecialchars(ohnous_get_profile_picture($request['profile'] ?? '', 'utilisateur'), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($request['nom'], ENT_QUOTES, 'UTF-8') ?>">
                                    <div>
                                        <strong><?= htmlspecialchars($request['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span><?= htmlspecialchars((string)$request['adresse_email'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="status <?= (int)$request['activer'] === 1 ? 'active' : 'inactive' ?>"><?= (int)$request['activer'] === 1 ? 'Activé' : 'Non activé' ?></span></td>
                            <td><?= htmlspecialchars((string)$request['whatsapp'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$request['telephone'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$request['instagram'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$request['facebook'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$request['tiktok'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$request['date_ajout'])), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="status"><?= ohnous_get_user_activation_status_label($request['statut'] ?? 'en_attente') ?></span></td>
                            <td>
                                <?php if(($request['statut'] ?? '') === 'en_attente'): ?>
                                    <div class="admin-store-card__actions nowrap">
                                        <button type="button" class="btn_ohnous admin-user-activation-action" data-id="<?= (int)$request['id'] ?>" data-action="accepter">Accepter</button>
                                        <button type="button" class="btn_ohnous second admin-user-activation-action" data-id="<?= (int)$request['id'] ?>" data-action="refuser">Refuser</button>
                                    </div>
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
<script src="/asset/js/admin_user_activation.js?<?= filemtime($_SERVER['DOCUMENT_ROOT']."/asset/js/admin_user_activation.js") ?>" defer></script>
