<?php

namespace YWA\Actions;

use WP_Query;
use YWA\Actions\ActionInterface;
use YWA\Actions\Exception\DatabaseException;
use YWA\Dao\AnswerDao;

class QaShortCodeAction implements ActionInterface
{

    protected $answerTypeKey;
    protected $lastAnswerMessageKey;
    protected $questionOrderKey;
    protected $questionPostType;
    protected $questionTaxonomy;
    private $allowAnonimousAjax;
    private $nextButtonText;

    public function __construct()
    {
        $this->answerTypeKey = YWAAQ_METAKEYS_answer_type_metakey;
        $this->lastAnswerMessageKey = YWAAQ_METAKEYS_last_answer_metakey;
        $this->questionOrderKey = YWAAQ_METAKEYS_question_order_metakey;
        $this->questionTaxonomy = YWAAQ_CPT_question_taxonomy;
        $this->questionPostType = YWAAQ_CPT_question_post_type;
        $this->allowAnonimousAjax = YWAAQ_ALLOW_ANONIMOUS_AJAX;
        $this->nextButtonText = YWAAQ_NEXT_BUTTON_NAME;
        
    }


    public function init()
    {
        add_action('init', array($this, 'registerQuestionShortcode'));
        add_action('init', array($this, 'set_pagination_base'));
    }
    public function set_pagination_base()
    {
        global $wp_rewrite;
        $wp_rewrite->pagination_base = 'p';
    }



    

    public function registerQuestionShortcode()
    {
        add_shortcode('questions', array($this, 'shortCodeCallback'));
    }


    public function shortCodeCallback($atts = array(), $content = null, $tag = '')
    {
        if (!is_user_logged_in()) {
            wp_die("<p style='color:red;font-size:200%;'>Q&A shortcode works only for logged in users</p>");
        }
        //posts are save only from ajax request in AjaxSaveResponseAction.php
        /* if (!empty($_POST)) {
            $this->saveQuestion();
        }*/
        // normalize attribute keys, lowercase
        $atts = array_change_key_case((array) $atts, CASE_LOWER);
        $paged = get_query_var("paged") ? get_query_var("paged") : 1;
        $userId = get_current_user_id();
        query_posts(array(
            'post_type'      => $this->questionPostType,
            'post_status'    => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => $this->questionTaxonomy,
                    'field' => 'slug',
                    'terms' => $atts['topic'],
                )
            ),
            'orderby'  => array('meta_value_num' => 'ASC', 'post_date' => 'ASC'),
            'meta_key' => $this->questionOrderKey,
            'posts_per_page' => 1,
            'paged' => $paged
        ));
        // The Loop
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                ob_start();
                $this->displayHTMLStartContainer();
                $this->displayHiddenInputFields($userId, get_the_ID(), $atts['topic']);
                echo  '<h1>' . get_the_title() . '</h1>';
                $answerType = get_post_meta(get_the_ID(), $this->answerTypeKey, true);

                $answer = '';

                // $answerDao = new AnswerDao();
                //$answer = $answerDao->getRowByUserAndQuestion($userId, get_the_ID())['answer'];

                switch ($answerType) {
                    case 'calendar':
                        $this->displayHTMLCalendar($answer, $answerType);
                        break;
                    case 'textarea':
                        $this->displayHTMLTextArea($answer, $answerType);
                        break;
                    case 'range':
                        $this->displayHTMLRangeSlider($answer, $answerType);
                        break;
                    case 'last':
                        $message = get_post_meta(get_the_ID(), $this->lastAnswerMessageKey, true);
                        $this->displayLastAnswerMessage($message);
                        break;
                    default:
                        echo "<span style='color:red;font-size:200%s'>Error: This question has not an aswer type attached!</span>";
                }

                global $wp_query;
                $foundPosts = (int) $wp_query->found_posts;
                if ($foundPosts > $paged) {
                    $this->displayHTMLPagination($paged, $foundPosts - 1);
                }

                $this->displayHTMLEndContainer();
            }
            wp_reset_query();
            return ob_get_clean();
        } else {
            wp_reset_query();
            return 'No quizz yet';
        }
        /* Restore original Post Data */
    }

    public function displayLastAnswerMessage($message)
    {
        echo $message;
    }

    public function displayHiddenInputFields($userId, $questionId, $topic)
    {
        echo "<input type='hidden' class='qa-user-id' name='qa-user-id' value='{$userId}'/>";
        echo "<input type='hidden' class='qa-question-id' name='qa-question-id' value='{$questionId}'/>";
        echo "<input type='hidden' class='qa-topic' name='qa-question-topic' value='{$topic}'/>";
    }

    public function saveQuestion()
    {
        //userId is zero for anonimous users
        $userId = $_POST['qa-user-id'];
        $questionId = $_POST['aq-question-id'];
        if (isset($_POST['range']) && !empty($_POST['range'])) {
            $answer = $_POST['range'];
        } elseif (isset($_POST['calendar']) && !empty($_POST['calendar'])) {
            $answer = $_POST['calendar'];
        } elseif (isset($_POST['textarea']) && !empty($_POST['textarea'])) {
            $answer = $_POST['textarea'];
        }
        //$answer = $_POST['range']?? $_POST['calendar'] ?? $_POST['textarea'];
        try {
            $answerDao = new AnswerDao();
            $answerDao->saveOrUpdate($userId, $questionId, $answer);
        } catch (DatabaseException $ex) {
            wp_die('Database error.Please contact the administrator');
        }
    }

    public function displayHTMLStartContainer()
    {
        echo  "<div class='questions-container'><form method='POST'>";
    }
    public function displayHTMLEndContainer()
    {
        echo "</form></div>";
    }

    public function displayHTMLCalendar($defaultValue, $answerType)
    {
        echo "<p>Date: <input type='text' class='qa-answer'  name='calendar' id='ywaaq_datepicker' data-type='{$answerType}' value='{$defaultValue}'></p>";
    }
    public function displayHTMLSubmitButton($btnText)
    {
        echo "<button class='question-green-btn'>{$btnText}</button>";
    }

    public function displayHTMLTextArea($defaultValue, $answerType)
    {
        echo "<textarea class='qa-answer qustion-textarea' name='textarea' rows='4' cols='50' data-type='{$answerType}'>{$defaultValue}</textarea>";
    }

    public function displayHTMLRangeSlider($defaultValue, $answerType)
    {
        $defaultValue = (int) $defaultValue;

        echo '<input type="text" class="ywa-js-range-slider" style="display:none" name="my_range" value="" />';
        echo "<input type='range' class='qa-answer' style='display:none' id='qa-range' name='range' min='0' max='10' data-type='{$answerType}' /> ";
    }

    public function displayHTMLPagination($paged, $foundPosts)
    {
        // echo '<div class="listings question-pagination clearfix">';
        // echo '<div class="nav-previous">' . previous_posts_link(__('&larr; Previous question <span class="meta-nav"></span>&nbsp;')) . '</div>';
        //  echo '<div class="yw-next-question">' . get_next_posts_link(__('Save question')) . '(' . $paged . ' of ' . $foundPosts . ')</div>';
        $btnTxt = ($btnTxt = get_option('qa_next_button_name')) ? $btnTxt : 'Next';

        echo '<div class="yw-next-question">' . get_next_posts_link(__($btnTxt)) . '</div>';
        //echo "</div>";
    }
}


//https://wordpress.stackexchange.com/questions/58880/shortcode-displaying-custom-post-types
