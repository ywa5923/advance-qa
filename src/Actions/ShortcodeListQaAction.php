<?php

namespace YWA\Actions;

use YWA\Dao\AnswerDao;
use YWA\Helpers\Core\PathFinder;

class ShortcodeListQaAction implements ActionInterface
{
    protected $answerTypeKey;
    protected $lastAnswerMessageKey;
    protected $questionOrderKey;
    protected $questionPostType;
    protected $questionTaxonomy;
    //protected $questionTaxonomy2;
    private $allowAnonimousAjax;
    private $ajaxActionUrl = 'ywa_get_qresponses';

    protected $pathFinder;

    private $wpdb;
    private $questionsTableName;


    public function __construct()
    {
        $this->answerTypeKey = YWAAQ_METAKEYS_answer_type_metakey;
        $this->lastAnswerMessageKey = YWAAQ_METAKEYS_last_answer_metakey;
        $this->questionOrderKey = YWAAQ_METAKEYS_question_order_metakey;
        $this->questionTaxonomy = YWAAQ_CPT_question_taxonomy;
        //$this->questionTaxonomy2 = YWAAQ_CPT_question_taxonomy2;
        $this->questionPostType = YWAAQ_CPT_question_post_type;
        $this->allowAnonimousAjax = YWAAQ_ALLOW_ANONIMOUS_AJAX;
        $this->pathFinder = new PathFinder();

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->questionsTableName = YWAAQ_CUSTOM_TABLES_answers_table;
    }

    public function init()
    {
        add_action('init', array($this, 'registerQuestionShortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
        add_action("wp_ajax_{$this->ajaxActionUrl}", array($this, 'getQuestionResponses'));
    }

    public function enqueue()
    {
        $config_array = array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxActionURL' => $this->ajaxActionUrl,
            'ajaxNonce' => wp_create_nonce('ywa-list-ques-nonce')
        );
        wp_enqueue_script('questions-list-js', $this->pathFinder->getAssetsUrl() . 'js/questions-list-v2.js', ['jquery']);
        wp_localize_script('questions-list-js', 'config', $config_array);
    }

    public function getQuestionResponses()
    {

        if (!wp_verify_nonce($_POST['nonce'], "ywa-list-ques-nonce")) {

            header('HTTP/1.1 500 Internal Server Booboo');
            header('Content-Type: application/json; charset=UTF-8');
            $result = array();
            $result['status'] = 'error';
            $result['messages'] = "Nonce field doesn;t pass";

            die(json_encode($result));
        }

        $answers = new AnswerDao();


        echo json_encode(array(
            'status' => "success",
            "answers" => $answers->getRowsBtUserAndQuestion(
                $_POST['user_id'],
                $_POST['question_id']
            )
        ));
        exit;
        // $_POST['data']['user_id'],
        //$_POST['data']['question_id']
    }


    public function registerQuestionShortcode()
    {
        add_shortcode('questions-list', array($this, 'shortCodeCallback'));
    }

    public function shortCodeCallback($atts = array(), $content = null, $tag = '')
    {
        $userId = get_current_user_id();
        if ($userId == 0) {
            wp_die('Only allowed for logged in users');
        }


        query_posts(array(
            'post_type'      => $this->questionPostType,
            'post_status'    => 'publish',
            'tax_query' => array(
                // 'relation' => 'AND',
                array(
                    'taxonomy' => $this->questionTaxonomy,
                    'field' => 'name',
                    'terms' => array($atts['topic']),
                    'operator' => 'IN'
                )
            ),
            'meta_query' => array(
                array(
                    'key' => $this->questionOrderKey,
                    'compare' => '!=',
                    'value' => '1000'
                ),
            ),
            'orderby'  => array('meta_value_num' => 'ASC', 'post_date' => 'DESC'),
            'meta_key' => $this->questionOrderKey,
            'posts_per_page' => -1,

        ));

        $questions = array();

        if (have_posts()) {
            while (have_posts()) {
                the_post();
                $id = get_the_ID();
                $title = get_the_title();
                $questions[$id] = $title;
            }
            wp_reset_query();
        }

        $questionsTableWithPrefix = $this->wpdb->prefix . $this->questionsTableName;
        $ids = implode(",", array_keys($questions));

        // $sql = "select * from wp_processed_feedback where car_part_id=%d and brand_id IN ({$brandsIDArray}) order by total_score desc";
        $sql = "Select * from {$questionsTableWithPrefix} where user_id={$userId} and question_id in ({$ids}) order by created_at DESC";
        $answerRows = $this->wpdb->get_results($sql, ARRAY_A);

        $answers = array();
        foreach ($answerRows  as $res) {
            if (array_key_exists($res['question_id'], $answers)) {
                array_push($answers[$res['question_id']], $res['answer']);
            } else {
                $answers[$res['question_id']] = array($res['answer']);
            }
        }
        /* $answers = array_combine(
            array_column($responses, 'question_id'),
            array_column($responses, 'answer')
        );
        echo "<pre>";
        print_r($answers);
        echo "</pre>";*/

        ob_start();
        echo "<div class='questions-wrapper' style='display:block'>";
        $questionNo = 1;
        foreach ($questions as $k => $v) {
            echo "<div class='question-wrapper'><h1><span>{$questionNo}</span> {$v}</h1>";
            if (is_array($answers[$k]) && count($answers[$k]) > 1) {
                $index = 0;
                foreach ($answers[$k] as $answer) {
                    if ($index == 0) {
                        echo "<h3 class='first'>- {$answer}</h3>
                        <div class='show-all-btn'><span>+</span> Show all</div>";
                    } else {
                        echo "<h3 style='display:none' >- {$answer}</h3>";
                    }

                    $index++;
                }
            } else {
                echo "<h3>- {$answers[$k][0]}</h3>";
            }
            echo "</div>";
            $questionNo++;
        }
        echo "</div>";
        return ob_get_clean();
    }




    public function shortCodeCallbackWithAjax($atts = array(), $content = null, $tag = '')
    {

        //$atts = array_change_key_case((array) $atts, CASE_LOWER);
        $userId = get_current_user_id();
        if ($userId == 0) {
            wp_die('Only allowed for logged in users');
        }


        query_posts(array(
            'post_type'      => $this->questionPostType,
            'post_status'    => 'publish',
            'tax_query' => array(
                // 'relation' => 'AND',
                array(
                    'taxonomy' => $this->questionTaxonomy,
                    'field' => 'name',
                    'terms' => array($atts['topic']),
                    'operator' => 'IN'
                )
            ),
            'meta_query' => array(
                array(
                    'key' => $this->questionOrderKey,
                    'compare' => '!=',
                    'value' => '1000'
                ),
            ),
            'orderby'  => array('meta_value_num' => 'ASC', 'post_date' => 'DESC'),
            'meta_key' => $this->questionOrderKey,
            'posts_per_page' => -1,

        ));

        if (have_posts()) {
            ob_start();
            $inc = 1;
            $result = '';

            while (have_posts()) {
                the_post();
                $id = get_the_ID();
                $title = get_the_title();

                echo "<div class='question-wrapper' style='display:block' data-id='{$id}' data-user-id={$userId}>" .
                    "<h2>{$inc}) {$title}</h2></div>";

                $inc++;
            }
            wp_reset_query();
            // return $result;
            return ob_get_clean();
        }
    }
}
