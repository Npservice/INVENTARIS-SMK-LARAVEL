<?php

return [
    'dashboard' => ['index'],
    'instansi' => ['select', 'index', 'store', 'update', 'destroy'],
    'lokasi' => ['select', 'index', 'store', 'update', 'destroy'],
    'jenis' => ['select', 'index', 'store', 'update', 'destroy'],
    'pendanaan' => ['select', 'index', 'store', 'update', 'destroy'],
    'inventaris' => [
        'select', 'index', 'show', 'store', 'update', 'destroy',
        'next-kode', 'export', 'import-template', 'import',
    ],
    'perawatan' => ['index', 'show', 'store', 'update', 'destroy'],
    'peminjaman' => [
        'index', 'show', 'store', 'update', 'destroy', 'find-by-kode', 'kembali',
    ],
    'user' => ['select', 'index', 'show', 'store', 'update', 'destroy'],
    'kritik-saran' => ['index', 'show', 'destroy'],
    'role' => ['select', 'index', 'show', 'store', 'update', 'destroy'],
    'permission' => ['index'],
];
