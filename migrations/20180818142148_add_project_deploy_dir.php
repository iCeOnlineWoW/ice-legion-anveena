<?php


use Phinx\Migration\AbstractMigration;

class AddProjectDeployDir extends AbstractMigration
{
    public function change()
    {
        $this->table('projects')->addColumn('local_deploy_dir', 'string')->update();
    }
}
