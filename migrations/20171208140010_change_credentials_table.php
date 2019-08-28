<?php


use Phinx\Migration\AbstractMigration;

class ChangeCredentialsTable extends AbstractMigration
{
    public function change()
    {
        $this->table('project_credentials')->drop()->save();

        $tbl = $this->table('credentials', array('id' => false, 'primary_key' => array('identifier')));

        $tbl->addColumn('identifier', 'string', array('null' => false))
            ->addColumn('type', 'string', array('null' => false))
            ->addColumn('username', 'string', array('null' => true))
            ->addColumn('auth_ref', 'string', array('null' => true));

        $tbl->create();
    }
}
