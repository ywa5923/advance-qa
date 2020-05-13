<?php

namespace YWA\Actions;



class Activate
{
    private $wpdb;
    private $questionTableName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->questionTableName = YWAAQ_CUSTOM_TABLES_answers_table;
    }

    public function run()
    {
        $this->createUserAnswersTable();
    }

    public function createUserAnswersTable()
    {
        $tableName = $this->wpdb->prefix . $this->questionTableName;
        $sql = "CREATE TABLE {$tableName} (
                id int(12) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                user_id int(12) NOT NULL,
                question_id mediumint(9) NOT NULL,
                answer varchar(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                );";
        $this->makeDeltaQuery($tableName, $sql);
    }

    public function makeDeltaQuery($tableName, $sql)
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        if ($this->wpdb->get_var("show tables like '{$tableName}'") != $tableName) {
            dbDelta($sql);
        }
    }
}
