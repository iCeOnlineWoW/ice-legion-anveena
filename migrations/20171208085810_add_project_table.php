<?php


use Phinx\Migration\AbstractMigration;

class AddProjectTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('projects');

        $tbl->addColumn('name', 'string', array('null' => false))
            ->addColumn('description', 'text')
            ->addColumn('repository_type', 'string', array('null' => true))
            ->addColumn('repository_url', 'string', array('null' => true))
            ->addColumn('repository_branch', 'string', array('null' => true, 'default' => 'master'))
            ->addColumn('last_build_number', 'integer', array('null' => true))
            ->addColumn('last_build_status', 'string', array('null' => true));

        $tbl->create();

        $tbl = $this->table('project_credentials', array('primary_key' => array('projects_id', 'identifier')));

        $tbl->addColumn('projects_id', 'integer', array('null' => false))
            ->addColumn('identifier', 'string', array('null' => false))
            ->addColumn('type', 'string', array('null' => false))
            ->addColumn('username', 'string', array('null' => true))
            ->addColumn('auth_key', 'string', array('null' => true));

        $tbl->addForeignKey('projects_id', 'projects', 'id');
        $tbl->addIndex('identifier');

        $tbl->create();
    }
}
