<?php

namespace YWA\Actions;

class Uninstall
{

    private $wpdb;
    private $questionTableName;

    private $customTables;
    private $postsMetaKeys;
    private $customPostTypes;
    private $tablesPrefix;
    private $questionTaxonomy;
    private $mbOptionsName;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        //  $this->questionTableName = YWAAQ_CUSTOM_TABLES['question_table'];

        $this->customTables = array(YWAAQ_CUSTOM_TABLES_answers_table);
        $this->postsMetaKeys = array(
            YWAAQ_METAKEYS_answer_type_metakey,
            YWAAQ_METAKEYS_last_answer_metakey,
            YWAAQ_METAKEYS_question_order_metakey

        );
        $this->customPostTypes = array(YWAAQ_CPT_question_post_type);
        $this->tablesPrefix = $wpdb->prefix;
        $this->questionTaxonomy = YWAAQ_CPT_question_taxonomy;
        $this->mbOptionsName = YWAAQ_OPTIONS_memberpress_options;
    }


    public function run()
    {
        $this->dropCustomTables();
        $this->deleteCustomPostTypes();
        $this->deleteTaxonomy();
        $this->deleteOtions();
    }

    public function deleteOtions()
    {
        delete_option($this->mbOptionsName);
    }

    public function dropCustomTables()
    {
        foreach ($this->customTables as $table) {
            $tableName = $this->tablesPrefix . $table;
            $sql = "DROP TABLE IF EXISTS {$tableName}";
            $this->wpdb->query($sql);
        }
    }

    public function deleteCustomPostTypes()
    {
        //delete posts meta
        $postMetaTable = $this->wpdb->postmeta;
        foreach ($this->postsMetaKeys as $metaKey) {
            $this->wpdb->query("DELETE FROM {$postMetaTable} WHERE meta_key = '{$metaKey}'");
        }

        //delete all CPT
        $postsTable = $this->wpdb->posts;
        foreach ($this->customPostTypes as $cpt) {
            $this->wpdb->query("DELETE FROM {$postsTable} WHERE post_type = '{$cpt}'");
        }
    }

    public function deleteTaxonomy()
    {
        $terms = get_terms([$this->questionTaxonomy, 'hide_empty' => false]);
        foreach ($terms as $term) {
            wp_delete_term($term->term_id, $this->questionTaxonomy);
        }

        // $this->wpdb->get_results($this->wpdb->prepare("DELETE t.*, tt.* FROM $this->wpdb->terms AS t INNER JOIN $this->wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s')", $this->questionTaxonomy));
        $this->wpdb->delete($this->wpdb->term_taxonomy, array('taxonomy' => $this->questionTaxonomy), array('%s'));
    }
}
//https://wordpress.stackexchange.com/questions/167848/select-from-wpdb-posts-where-id-160
