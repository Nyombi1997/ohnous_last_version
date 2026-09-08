<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit(); }
require_once __DIR__.'/../config/env.php';
$prefix = 'OHNOUS_ENV_TEST_'.strtoupper(bin2hex(random_bytes(6))).'_';
$file = tempnam(sys_get_temp_dir(), 'ohnous_env_');
$keys = ['VALUE','EMPTY','QUOTED','PRIORITY','INVALID'];
try {
    putenv($prefix.'PRIORITY=server');
    file_put_contents($file, "\xEF\xBB\xBF# comment\n\n".$prefix."VALUE=1\n".$prefix."EMPTY=\n".$prefix.'QUOTED="literal # = $value"'."\n".$prefix."PRIORITY=file\n");
    ohnous_load_env($file);
    foreach (['VALUE'=>'1','EMPTY'=>'','QUOTED'=>'literal # = $value','PRIORITY'=>'server'] as $key=>$expected) {
        if (getenv($prefix.$key) !== $expected) throw new RuntimeException('Environment test failed: '.$key);
    }
    file_put_contents($file, $prefix.'INVALID="unterminated');
    $rejected = false;
    try { ohnous_load_env($file); } catch (RuntimeException $e) { $rejected = true; }
    if (!$rejected) throw new RuntimeException('Invalid quoting accepted.');
    echo "PASS: .env values, quotes, empty values, BOM, server precedence and invalid syntax.\n";
} finally {
    unlink($file);
    foreach ($keys as $key) { putenv($prefix.$key); unset($_ENV[$prefix.$key]); }
}
