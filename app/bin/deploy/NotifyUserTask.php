<?php

use Nette\Mail\Message,
    Nette\Mail\SendmailMailer;

/**
 * Task for sending notifications to users
 */
class NotifyUserTask extends DeployTask
{
    /** @var \App\Models\UserModel */
    protected $users;

    /** @var \Nette\Database\Table\ActiveRow */
    protected $targetUser;

    /** @var \Nette\Database\Table\ActiveRow */
    protected $project;

    public function Setup($container, $args)
    {
        if (!parent::Setup($container, $args))
            return false;

        $this->container = $container;

        if (!isset($args['step']->ref_users_id) || !$args['step']->ref_users_id)
        {
            $this->log("No user specified!");
            return false;
        }

        $this->users = $container->getByType('App\Models\UserModel');

        $this->targetUser = $this->users->getUserById($args['step']->ref_users_id);

        if (!$this->targetUser)
        {
            $this->log("Specified user does not exist!");
            return false;
        }

        $this->project = $this->projects->getProjectById($args['projects_id']);

        if (!$this->project)
            return false;

        return true;
    }

    public function Run()
    {
        $this->log("Notifying user ".$this->targetUser->username." on ".$this->targetUser->email);

        $mailtemplate = "";
        $subject = $this->project->name.": ";
        if (!isset($this->parameters['build_status']) || ($this->parameters['build_status'] == 'running' && !$this->parameters['consider_successful']))
        {
            $mailtemplate = 'build_running';
            $subject .= "build in progress";
        }
        else if ($this->parameters['build_status'] == 'success' || ($this->parameters['build_status'] == 'running' && $this->parameters['consider_successful']))
        {
            $mailtemplate = 'build_success';
            $subject .= "build successful";
        }
        else if ($this->parameters['build_status'] == 'fail')
        {
            $mailtemplate = 'build_fail';
            $subject .= "build failed";
        }

        try
        {
            $plainText = file_get_contents(__DIR__.'/../../presenters/templates/_email/'.$mailtemplate.'.txt');
            $htmlText = file_get_contents(__DIR__.'/../../presenters/templates/_email/'.$mailtemplate.'.html');

            $replaceMap = array(
                'USERNAME' => $this->targetUser->username,
                'PROJECT_NAME' => $this->project->name,
                'DATE' => date('j. n. Y, H:i:s')
            );

            foreach ($replaceMap as $sym => $repl)
            {
                $plainText = str_replace('${{'.$sym.'}}', $repl, $plainText);
                $htmlText = str_replace('${{'.$sym.'}}', $repl, $htmlText);
            }

            $mail = new Message;
            // TODO: make source email configurable
            $mail->setFrom('Anveena <anveena@ice-wow.eu>')
                ->addTo($this->targetUser->email)
                ->setSubject($subject)
                ->setBody($plainText)
                ->setHtmlBody($htmlText);

            $mailer = new SendmailMailer;
            $mailer->send($mail);
        }
        catch (Exception $e)
        {
            $this->log("Could not send email to: ".$this->targetUser->email.", reason: ".$e->getMessage());
        }

        return true;
    }
}
