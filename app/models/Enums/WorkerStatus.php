<?php

namespace App\Models;

/**
 * Enumeration of worker statuses
 */
class WorkerStatus extends BaseEnum
{
    const IDLE = 'idle';
    const WORKING = 'working';
}
