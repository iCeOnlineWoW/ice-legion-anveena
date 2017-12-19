<?php


use Phinx\Migration\AbstractMigration;

class AddConfigurationTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('configurations', array('id' => false, 'primary_key' => array('identifier')));

        $tbl->addColumn('identifier', 'string', array('null' => false))
            ->addColumn('configuration', 'text');

        $tbl->create();
    }
}
