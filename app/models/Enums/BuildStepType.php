<?php

namespace App\Models;

/**
 * Enumeration of build steps
 */
class BuildStepType extends BaseEnum
{
    const DUMMY = 'dummy';
    const CLONE_REPOSITORY = 'clone_repository';
    const COMPOSER = 'composer';
    const UPLOAD_FTP = 'upload_ftp';
    const UPLOAD_SFTP = 'upload_sftp';
    const NOTIFY_BUILD_STATUS = 'notify_build_status';
    const PREPARE_CONFIG = 'prepare_config';
    const SSH_COMMAND = 'ssh_command';
}
