<?php

namespace App\Services\Attendance;

use RuntimeException;

/** A device action could not be completed through the attendance bridge API. Messages are safe to show users. */
class AttendanceBridgeException extends RuntimeException {}
