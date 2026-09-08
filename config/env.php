<?php

// Load local settings without overriding variables supplied by the hosting environment.
function ohnous_load_env($path)
{
    if (!is_file($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) throw new RuntimeException('Fichier .env illisible.');
    foreach ($lines as $index => $line) {
        $line = trim($index === 0 ? preg_replace('/^\xEF\xBB\xBF/', '', $line) : $line);
        if ($line === '' || $line[0] === '#') continue;
        if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/D', $line, $matches)) {
            throw new RuntimeException('Syntaxe .env invalide à la ligne '.($index + 1).'.');
        }
        $key = $matches[1];
        $value = trim($matches[2]);
        if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
            if (strlen($value) < 2 || substr($value, -1) !== $value[0]) throw new RuntimeException('Guillemets .env invalides à la ligne '.($index + 1).'.');
            $value = substr($value, 1, -1);
        }
        if (strpos($value, "\0") !== false) throw new RuntimeException('Valeur .env invalide à la ligne '.($index + 1).'.');
        if (getenv($key) !== false) continue;
        if (!putenv($key.'='.$value)) throw new RuntimeException('Impossible de charger une variable .env.');
        $_ENV[$key] = $value;
    }
}

ohnous_load_env(dirname(__DIR__).'/.env');
