<?php
$esEdicion = false;
$categoriasListadoUrl = url('/admin/categorias');
$formAction = url('/admin/categorias/guardar');
$stats = ['total_productos' => 0];
$categoria = [];

include __DIR__ . '/form.php';
?>