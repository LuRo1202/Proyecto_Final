<?php
// Evitar cache en navegadores
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Mostrar contenido del HTML
readfile("../index.html");
