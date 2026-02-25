<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$request = Illuminate\Http\Request::create('/api/data-pegawai/list?page=1&per_page=20&search=&jenis_kelamin=&jabatan=&sort=nama_pegawai&order=asc', 'GET');
$request->headers->set('Accept', 'application/json');
try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    $content = $response->getContent();
    $json = json_decode($content, true);
    if ($json) {
        echo "Data count: " . count($json['data'] ?? []) . "\n";
        echo "Total: " . ($json['total'] ?? 'N/A') . "\n";
    } else {
        echo "NOT JSON! First 500 chars:\n";
        echo substr($content, 0, 500) . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
