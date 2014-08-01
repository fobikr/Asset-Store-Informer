<?php

require_once 'utils.php';
require_once 'settings.php';

$GLOBALS['tables'] = array(
    'assets' => table_prefix . "assets",
    'charges' => table_prefix . "charges",
    'events' => table_prefix . "events",
    'informer' => table_prefix . "informer",
    'months' => table_prefix . "months",
    'publisher' => table_prefix . "publisher",
    'ratings' => table_prefix . "ratings",
    'refundings' => table_prefix . "refundings",
    'reviews' => table_prefix . "reviews",
    'sales' => table_prefix . "sales",
    'settings' => table_prefix . "settings",
    'tasks' => table_prefix . "tasks",
    'updates' => table_prefix . "updates",
    'users' => table_prefix . "users"
);