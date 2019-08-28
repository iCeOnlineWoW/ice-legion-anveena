<?php

// force argument count
if ($argc != 2 || !is_numeric($argv[1]))
{
    die("Usage:\n    php Worker.php <worker-id>\n");
}

$container = require __DIR__ . '/../../bootstrap.php';

$projectsDb = $container->getByType('App\Models\ProjectModel');
$workersDb = $container->getByType('App\Models\WorkerModel');
$buildStepsDb = $container->getByType('App\Models\BuildStepModel');
$buildsDb = $container->getByType('App\Models\BuildModel');

$worker_id = $argv[1];
$port = WORKER_BASE_PORT + $worker_id;

// create communicator socket
$sock = socket_create(AF_INET, SOCK_DGRAM, 0);
if ($sock === false)
    die("Could not create worker socket!\n");

// bind to local address
if (socket_bind($sock, "127.0.0.1", $port) === false)
    die("Could not bind to local port!\n");

// create worker record in database, so the worker could be selected
$workersDb->createWorker($worker_id);

echo "Started worker at 127.0.0.1:$port\n";

$buf = "";
$buildId = 0;

function logMsg($str)
{
    global $buildsDb;
    global $buildId;
    $buildsDb->appendLog($buildId, $str);
    echo $str."\n";
}

while (true)
{
    $build = $buildsDb->getNotStartedBuild();
    if (!$build)
    {
        // receive commands
        $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);

        // validate - must be numeric
        if (!is_numeric($buf))
        {
            echo "Worker $worker_id: Received invalid command from $remote_ip:$remote_port\n";
            continue;
        }

        $build = $buildsDb->getBuildById($buf);
        if (!$build)
        {
            echo "Worker $worker_id: Received invalid build id '".$buf."' from $remote_ip:$remote_port\n";
            continue;
        }
    }
    $buildId = $build->id;
    $projects_id = $build->projects_id;

    $projectRecord = $projectsDb->getProjectById($projects_id);

    logMsg("Worker $worker_id: Starting build of project $projects_id");
    
    // move to project local directory
    if ($projectRecord->local_deploy_dir && strlen($projectRecord->local_deploy_dir) > 1)
    {
        if (!file_exists($projectRecord->local_deploy_dir))
            mkdir($projectRecord->local_deploy_dir);
        chdir($projectRecord->local_deploy_dir);
    }
    else
    {
        // use system tmp dir (/tmp on unixes, or AppData temp dir on Windows)
        chdir(sys_get_temp_dir());
        if (!file_exists('p'.$projects_id))
            mkdir('p'.$projects_id);
        chdir('p'.$projects_id);
    }

    // check if that build is still "available" (not taken by another worker)
    $refreshedBuild = $buildsDb->getBuildById($build->id);
    if (!$refreshedBuild || $refreshedBuild->status !== \App\Models\BuildStatus::NONE)
        continue;

    // update build status
    $buildsDb->updateBuildStatus($build->id, \App\Models\BuildStatus::RUNNING);
    // update worker status, so it's not selected for builds for now
    $workersDb->updateWorkerStatus($worker_id, \App\Models\WorkerStatus::WORKING, $build->id);
    // update project build status
    $projectsDb->setProjectBuildInfo($projects_id, $build->build_number, \App\Models\BuildStatus::RUNNING);

    $build_status = 'running';
    $error = 0;
    
    // retrieve steps and perform build
    $steps = $buildStepsDb->getStepsForProject($projects_id);
    foreach ($steps as $step)
    {
        $task = null;

        // build steps performer when everything reports success
        if ($error === 0)
        {
            switch ($step->type)
            {
                case \App\Models\BuildStepType::DUMMY:
                    // no action, just continue to next step
                    continue;
                case \App\Models\BuildStepType::CLONE_REPOSITORY:
                    $task = new CloneTask();
                    break;
                case \App\Models\BuildStepType::COMPOSER:
                    $task = new ComposerTask();
                    break;
                case \App\Models\BuildStepType::UPLOAD_FTP:
                    $task = new UploadFtpTask();
                    break;
                case \App\Models\BuildStepType::UPLOAD_SFTP:
                    $task = new UploadSftpTask();
                    break;
                case \App\Models\BuildStepType::NOTIFY_BUILD_STATUS:
                    $task = new NotifyUserTask();
                    break;
                case \App\Models\BuildStepType::PREPARE_CONFIG:
                    $task = new PrepareConfigTask();
                    break;
                case \App\Models\BuildStepType::SSH_COMMAND:
                    $task = new SSHCommandTask();
                    break;
            }
        }
        else // build steps performed when build failed
        {
            switch ($step->type)
            {
                case \App\Models\BuildStepType::NOTIFY_BUILD_STATUS:
                    $task = new NotifyUserTask();
                    break;
                default:
                    continue;
            }
        }
        
        // task must be created before
        if (!$task)
        {
            // report unknown task only if error is 0 -> truly unknown task
            if ($error === 0)
                logMsg("Worker $worker_id: Unknown task ".$step->type." for project ".$projects_id);
            $build_status = 'fail';
            $error = 1;
            continue;
        }
        
        logMsg("Worker $worker_id: Project $projects_id, task ".$step->type);

        $success = false;

        $paramArray = array_merge(
                array('projects_id' => $projects_id, 'worker_id' => $worker_id, 'step' => $step, 'build_id' => $build->id, 'build_status' => $build_status),
                (array)json_decode($step->additional_params));

        // setup and run task
        if ($task->Setup($container, $paramArray))
            $success = $task->Run();
        else
            logMsg("Worker $worker_id: Could not initialize task ".$step->type." for project $projects_id");

        if (!$success)
        {
            $error = 1;
            $build_status = 'fail';
            continue;
        }
    }

    $status = ($error === 0) ? \App\Models\BuildStatus::SUCCESS : \App\Models\BuildStatus::FAIL;
    $statusText = ($error === 0) ? " successfully" : " with error(s)";

    logMsg("Worker $worker_id: Completed build of project $projects_id".$statusText);

    // update build status
    $buildsDb->updateBuildStatus($build->id, $status);
    // update project build status
    $projectsDb->setProjectBuildInfo($projects_id, $build->build_number, $status);
    // we are available again
    $workersDb->updateWorkerStatus($worker_id, \App\Models\WorkerStatus::IDLE, null);
}
