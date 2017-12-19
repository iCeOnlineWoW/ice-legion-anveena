<?php


use Phinx\Migration\AbstractMigration;

class AddBuildLogColumn extends AbstractMigration
{
    public function change()
    {
        $this->table('builds')->addColumn('log', 'text')->update();
    }
}
