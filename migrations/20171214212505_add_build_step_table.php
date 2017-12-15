<?php


use Phinx\Migration\AbstractMigration;

class AddBuildStepTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('project_build_steps', array('id' => false, 'primary_key' => array('projects_id', 'step')));

        $tbl->addColumn('projects_id', 'integer', array('null' => false))
            ->addColumn('step', 'integer', array('null' => false))
            ->addColumn('type', 'string', array('null' => false))
            ->addColumn('ref_credentials_identifier', 'string', array('null' => true))
            ->addColumn('ref_projects_id', 'integer', array('null' => true))
            ->addColumn('ref_users_id', 'integer', array('null' => true))
            ->addColumn('additional_params', 'string', array('null' => true));

        $tbl->addForeignKey('projects_id', 'projects', 'id')
            ->addForeignKey('ref_credentials_identifier', 'credentials', 'identifier')
            ->addForeignKey('ref_projects_id', 'projects', 'id')
            ->addForeignKey('ref_users_id', 'users', 'id');

        $tbl->create();
    }
}
