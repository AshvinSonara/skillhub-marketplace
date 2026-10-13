<?php
require_once __DIR__ . '/../includes/functions.php';

$_SESSION = [];
session_destroy();

// start a fresh session just to carry the flash message
session_start();
redirectWithMessage('index.php', 'You have been logged out.');
