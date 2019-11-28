<?php

require_once 'random_compat/lib/byte_safe_strings.php';
require_once 'random_compat/lib/cast_to_int.php';
require_once 'random_compat/lib/error_polyfill.php';
//require_once 'random_compat/other/ide_stubs/libsodium.php';
require_once 'random_compat/lib/random.php';

$int = random_int(0, 65536);
