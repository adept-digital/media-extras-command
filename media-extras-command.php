<?php

namespace AdeptDigital\MediaCommands;

use WP_CLI;

if (!class_exists('WP_CLI')) {
    return;
}

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

WP_CLI::add_command('media search', MediaSearchCommand::class);