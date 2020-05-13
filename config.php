<?php

/**
 * This const represent the name of  option_name key in options table
 */
/*define('YWAAQ_OPTIONS', [
    'memberpress_options'=>'qa_memberpress_options'//'ywa_aq_options'
]);*/

define('YWAAQ_OPTIONS_memberpress_options', 'qa_memberpress_options');
/**
 * This const set the anonimous or private character for ajax calls.For private calls use ''
 */
define('YWAAQ_ALLOW_ANONIMOUS_AJAX', FALSE);

/**
 * This const represent the maximum length for text responses
 */
define('YWAAQ_ANSWER_MAX_LENGTH', 500);

/**
 * The metakeys 
 */
/*define('YWAAQ_METAKEYS', [
    'answer_type_metakey' => '_answer_type_key',
    'last_answer_metakey' => '_last_answer_key',
    'question_order_metakey' => '_question_order_key',
]);*/
define('YWAAQ_METAKEYS_answer_type_metakey', '_answer_type_key');
define('YWAAQ_METAKEYS_last_answer_metakey', '_last_answer_key');
define('YWAAQ_METAKEYS_question_order_metakey', '_question_order_key');


/**
 * Custom post types 
 */

/*define('YWAAQ_CPT', [
    'question_post_type' => 'ywa-questions',
    'question_taxonomy' => 'ywa-qa-topics',
    'question_taxonomy2' => 'test_name'
]);*/
define('YWAAQ_CPT_question_post_type', 'ywa-questions');
define('YWAAQ_CPT_question_taxonomy', 'ywa-qa-topics');
define('YWAAQ_CPT_question_taxonomy2', 'ywa-qa-quizz');



/*define('YWAAQ_CUSTOM_TABLES', [
    'answers_table' => 'qa_answers'
]);*/

define('YWAAQ_CUSTOM_TABLES_answers_table', 'qa_answers');

define('YWAAQ_NEXT_BUTTON_NAME', 'qa_next_button_name');
