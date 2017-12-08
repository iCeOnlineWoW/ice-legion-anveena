<?php


use Phinx\Migration\AbstractMigration;

class ChangeCredentialsTable extends AbstractMigration
{
    public function change()
    {
        $this->dropTable('project_credentials');

        $tbl = $this->table('credentials', array('id' => false, 'primary_key' => array('identifier')));

        $tbl->addColumn('identifier', 'string', array('null' => false))
            ->addColumn('type', 'string', array('null' => false))
            ->addColumn('username', 'string', array('null' => true))
            ->addColumn('auth_ref', 'string', array('null' => true));

        $tbl->create();
    }
}
