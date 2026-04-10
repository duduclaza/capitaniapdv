<?php
/**
 * Root-level entry point for shared hosting environments
 * where the document root points to the project root instead of /public
 *
 * This file simply bootstraps and delegates to public/index.php
 */

// Define the real document root as the project root
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['DOCUMENT_ROOT']   = __DIR__;

// Run the public index
require __DIR__ . '/public/index.php';
