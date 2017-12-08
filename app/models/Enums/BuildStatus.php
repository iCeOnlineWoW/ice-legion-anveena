<?php

namespace App\Models;

/**
 * Enumeration of build statuses
 */
class BuildStatus extends BaseEnum
{
    const NONE = 'none';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const FAIL = 'fail';
}
