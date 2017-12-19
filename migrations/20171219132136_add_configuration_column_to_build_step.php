<?php


use Phinx\Migration\AbstractMigration;

class AddConfigurationColumnToBuildStep extends AbstractMigration
{
    public function change()
    {
        $this->table('project_build_steps')->addColumn('ref_configurations_identifier', 'string', array('null' => true, 'after' => 'ref_users_id'))->update();
    }
}
