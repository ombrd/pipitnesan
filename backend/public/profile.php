<?php
$start = microtime(true);
$log = function($m) use ($start) { echo sprintf("%.2f %s\n", microtime(true)-$start, $m); };

require __DIR__.'/../vendor/autoload.php';
$log('autoload');

$app = require_once __DIR__.'/../bootstrap/app.php';
$log('app_bootstrap');

$app->booting(function() use($log){ $log('app_booting'); });
$app->booted(function() use($log){ $log('app_booted'); });

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$log('kernel_make');

$request = Illuminate\Http\Request::capture();
$log('request_capture');

$response = $kernel->handle($request);
$log('kernel_handle');
