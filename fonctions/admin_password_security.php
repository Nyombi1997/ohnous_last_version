<?php

function ohnous_complete_admin_password_reset(PDO $bdd, $token, $password)
{
    if ($token === '' || strlen($password) < 6) throw new InvalidArgumentException('Données de réinitialisation invalides.');
    $stmt = $bdd->prepare('SELECT id, admin_id FROM admin_password_resets WHERE token = ? AND used_at IS NULL AND expire_at > NOW() ORDER BY id DESC LIMIT 1');
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$reset) throw new InvalidArgumentException('Code ou lien expiré ou déjà utilisé.');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $bdd->beginTransaction();
    try {
        // Verrouiller le compte puis consommer le code dans la même transaction.
        $stmt = $bdd->prepare('SELECT id FROM admins WHERE id = ? FOR UPDATE');
        $stmt->execute([(int)$reset['admin_id']]);
        if (!$stmt->fetchColumn()) throw new RuntimeException('Compte introuvable.');
        $stmt = $bdd->prepare('UPDATE admin_password_resets SET used_at = NOW() WHERE id = ? AND used_at IS NULL AND expire_at > NOW()');
        $stmt->execute([(int)$reset['id']]);
        if ($stmt->rowCount() !== 1) throw new RuntimeException('Code expiré ou déjà utilisé.');
        $bdd->prepare('UPDATE admins SET mdp = ? WHERE id = ?')->execute([$hash, (int)$reset['admin_id']]);
        $bdd->prepare('UPDATE admin_password_resets SET used_at = NOW() WHERE admin_id = ? AND used_at IS NULL')->execute([(int)$reset['admin_id']]);
        $bdd->commit();
        return (int)$reset['admin_id'];
    } catch (Throwable $e) {
        if ($bdd->inTransaction()) $bdd->rollBack();
        throw $e;
    }
}
