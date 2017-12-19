<?php


use Phinx\Migration\AbstractMigration;

class ModifyWorkerTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('workers');

        $this->query('TRUNCATE TABLE workers');

        $tbl->dropForeignKey('projects_id');
        $tbl->removeColumn('projects_id');

        $tbl->addColumn('builds_id', 'integer', array('null' => true));
        $tbl->addForeignKey('builds_id', 'builds', 'id');

        $tbl->update();
    }
}
