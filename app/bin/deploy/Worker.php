<?php

// force argument count
if ($argc != 2 || !is_numeric($argv[1]))
{
    die("Usage:\n    php Worker.php <worker-id>\n");
}

$container = require __DIR__ . '/../../bootstrap.php';

$workersDb = $container->getByType('App\Models\WorkerModel');
$buildStepsDb = $container->getByType('App\Models\BuildStepModel');

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

while (true)
{
    // receive commands
    $r = socket_recvfrom($sock, $buf, 512, 0, $remote_ip, $remote_port);
    
    // validate - must be numeric
    if (!is_numeric($buf))
    {
        echo "Worker $worker_id: Received invalid command from $remote_ip:$remote_port\n";
        continue;
    }
    
    echo "Worker $worker_id: Starting build of project $buf\n";
    
    // move to project local directory
    // TODO: make this configurable
    chdir('C:\Temp\deploydir');
    if (!file_exists('p'.$buf))
        mkdir('p'.$buf);
    chdir('p'.$buf);
    
    // update worker status, so it's not selected for builds for now
    $workersDb->updateWorkerStatus($worker_id, \App\Models\WorkerStatus::WORKING, (int)$buf);
    
    // retrieve steps and perform build
    $steps = $buildStepsDb->getStepsForProject($buf);
    foreach ($steps as $step)
    {
        $task = null;

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
                // TODO
                break;
            case \App\Models\BuildStepType::NOTIFY_BUILD_STATUS:
                // TODO
                break;
        }
        
        // task must be created before
        if (!$task)
        {
            echo "Worker $worker_id: Unknown task ".$step->type." for project ".$buf."\n";
            continue;
        }
        
        echo "Worker $worker_id: Project $buf, task ".$step->type."\n";

        // setup and run task
        if ($task->Setup($container, array('projects_id' => $buf, 'worker_id' => $worker_id)))
            $task->Run();
        else
            echo "Worker $worker_id: Could not initialize task ".$step->type." for project $buf\n";
    }
    
    // we are available again
    $workersDb->updateWorkerStatus($worker_id, \App\Models\WorkerStatus::IDLE, null);
    
    echo "Worker $worker_id: Completed build of project $buf\n";
}
