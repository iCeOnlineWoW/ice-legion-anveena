<?php


use Phinx\Migration\AbstractMigration;

class AddBuildTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('builds');

        $tbl->addColumn('projects_id', 'integer', array('null' => false))
            ->addColumn('build_number', 'integer', array('null' => false))
            ->addColumn('start_at', 'datetime', array('null' => false))
            ->addColumn('end_at', 'datetime', array('null' => true))
            ->addColumn('status', 'string', array('null' => false, 'default' => 'none'));

        $tbl->addForeignKey('projects_id', 'projects', 'id');

        $tbl->create();
    }
}
