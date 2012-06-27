<?php

class Migration_Forum_Create extends HMVC_Migration {

    public function up() {

        $this->dbforge->add_field(array(
            'forum_id' => array(
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true
            ),
            'forum_slug' => array(
                'type'           => 'VARCHAR',
                'constraint'     => 64
            ),
            'forum_name' => array(
                'type'           => 'VARCHAR',
                'constraint'     => 64
            ),
            'forum_description' => array(
                'type'           => 'TEXT',
                'null'           => true,
            ),
            'forum_left' => array(
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true
            ),
            'forum_right' => array(
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true
            )
        ));
        
        // Create Table
        $this->dbforge->create_table('forum');

        // Create Keys
        $this->dbforge->add_key('forum_id', true);
        $this->dbforge->add_key('forum_slug');
    }

    public function down() {
        $this->dbforge->drop_table('forum');
    }
}