<?php
$esEdicion = isset($categoria);
$categoriasListadoUrl = url('/admin/categorias');
$formAction = $esEdicion
    ? url("/admin/categorias/actualizar/{$categoria['id']}")
    : url('/admin/categorias/guardar');
$stats = $stats ?? ['total_productos' => 0];
$categoria = $categoria ?? [];

include __DIR__ . '/form.php';
?>