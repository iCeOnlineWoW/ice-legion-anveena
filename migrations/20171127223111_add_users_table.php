<?php


use Phinx\Migration\AbstractMigration;

class AddUsersTable extends AbstractMigration
{
    public function change()
    {
        $tbl = $this->table('users');

        $tbl->addColumn('username', 'string', array('null' => false))
            ->addColumn('password', 'string', array('null' => false))
            ->addColumn('email', 'string', array('null' => true))
            ->addColumn('ip_address', 'string', array('null' => true))
            ->addColumn('ip_locked', 'boolean', array('null' => false, 'default' => false))
            ->addColumn('blocked', 'boolean', array('null' => false, 'default' => false))
            ->addColumn('admin', 'boolean', array('null' => false, 'default' => false));

        $tbl->create();
    }
}
