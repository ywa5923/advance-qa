<?php

namespace YWA\Dao;

use YWA\Actions\Exception\DatabaseException;

class AnswerDao
{
    private $wpdb;
    private $tableName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->tableName = YWAAQ_CUSTOM_TABLES_answers_table;
    }

    public function saveOrUpdate($userId, $questionId, $answer)
    {

        $tableName = $this->wpdb->prefix . $this->tableName;
        $this->wpdb->query($this->wpdb->prepare("INSERT INTO {$tableName} (user_id, question_id,answer)
        VALUES (%d,%d,%s)", $userId, $questionId, $answer));

        if ($this->wpdb->last_error != '') {
            throw new DatabaseException($this->getSqlError());
        }
    }

    public function getRowByUserAndQuestion($userId, $questionId)
    {
        $tableName = $this->wpdb->prefix . $this->tableName;
        $sqlQuery = "SELECT * FROM {$tableName} where user_id={$userId} and question_id={$questionId}";
        $row = $this->wpdb->get_row($sqlQuery, ARRAY_A);
        if ($this->wpdb->last_error != '') {
            throw new DatabaseException($this->getSqlError());
        } else {
            return $row;
        }
    }

    public function getRowsBtUserAndQuestion($userId, $questionId)
    {
        global $wpdb;
        $tableName = $this->wpdb->prefix . $this->tableName;
        $sqlQuery = "SELECT * FROM {$tableName} where user_id={$userId} and question_id={$questionId} order by created_at DESC";
        $row = $wpdb->get_results($sqlQuery, ARRAY_A);

        if ($this->wpdb->last_error != '') {
            throw new DatabaseException($this->getSqlError());
        } else {
            return $row;
        }
    }

    public function getSQLError()
    {
        $str   = htmlspecialchars($this->wpdb->last_result, ENT_QUOTES);
        $query = htmlspecialchars($this->wpdb->last_query, ENT_QUOTES);

        return "<div id='error'>
        <p class='wpdberror'><strong>WordPress database error:</strong> [{$str}]<br />
        <code>{$query}</code></p>
        </div>";
    }
}
