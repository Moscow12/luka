<?php

namespace App\Support;

use InvalidArgumentException;

/** An outbound URL failed the SSRF checks. The message is safe to show to administrators. */
class UnsafeUrlException extends InvalidArgumentException {}
