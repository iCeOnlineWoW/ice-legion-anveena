<?php


use Phinx\Migration\AbstractMigration;

class AddWorkerTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('workers');
        
        $tbl->addColumn('status', 'string', array('null' => false))
            ->addColumn('job_start', 'datetime', array('null' => true))
            ->addColumn('projects_id', 'integer', array('null' => true));
        
        $tbl->addForeignKey('projects_id', 'projects', 'id');
        
        $tbl->create();
    }
}
