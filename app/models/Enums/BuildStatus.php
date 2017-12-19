<?php

namespace App\Models;

/**
 * Enumeration of build statuses
 */
class BuildStatus extends BaseEnum
{
    const NONE = 'none';
    const RUNNING = 'running';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const FAIL = 'fail';
}
