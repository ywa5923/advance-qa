<?php

namespace YWA\Actions;

use YWA\Actions\ActionInterface;
use YWA\Helpers\Core\DumpController;

class CptQuestionAction extends DumpController implements ActionInterface
{

    private $nonceField;
    private $nonceAction;
    private $questionOrderMetaKey;
    private $answerTypeMetaKey;
    private $lastAnswerTextareaMetaKey;
    private $questionPostType;
    private $questionTaxonomy;
    //private $questionTaxonomy2;

    public function __construct()
    {
        list($nonceAction, $nonceField) = $this->getNonceFields('qa_settings');
        $this->nonceField = $nonceField;
        $this->nonceAction = $nonceAction;
        $this->questionOrderMetaKey = YWAAQ_METAKEYS_question_order_metakey;
        $this->answerTypeMetaKey = YWAAQ_METAKEYS_answer_type_metakey;
        $this->lastAnswerTextareaMetaKey = YWAAQ_METAKEYS_last_answer_metakey;
        $this->questionPostType = YWAAQ_CPT_question_post_type;
        $this->questionTaxonomy = YWAAQ_CPT_question_taxonomy;
        //$this->questionTaxonomy2 = YWAAQ_CPT_question_taxonomy2;

        parent::__construct();
    }


    public function init()
    {
        add_action('init', array($this, 'registerCPT'));
        add_action('add_meta_boxes', array($this, 'addQuestionMetabox'));
        add_action('save_post', array($this, 'saveAnswerTypeCallback'));
        add_action('admin_notices', array($this, 'adminNotices'), 1);
        add_action('init', array($this, 'registerTaxonomy'), 0);

        add_filter("manage_{$this->questionPostType}_posts_columns", array($this, 'addAdminColumnsTitle'));
        add_action("manage_{$this->questionPostType}_posts_custom_column", array($this, 'addAdminColumns'), 10, 2);
        //add sortable field
        // add_filter("manage_edit-{$this->questionPostType}_sortable_columns", [$this, 'sortByTopics']);
    }

    public function sortByTopics($columns)
    {

        //this feature will be replaced with filter by taxonomy
        $taxonomyKey = 'taxonomy-' . $this->questionTaxonomy;
        $columns[$taxonomyKey] = $taxonomyKey;
        return $columns;
    }

    public function addAdminColumnsTitle($columns)
    {

        $date = $columns['date'];
        unset($columns['date']);
        return array_merge(
            $columns,
            array(
                $this->questionOrderMetaKey => __('Order'),
                $this->answerTypeMetaKey => __('Answer Type'),
                'date' => $date
            )
        );
    }
    public function addAdminColumns($column, $post_id)
    {
        if ($this->questionOrderMetaKey === $column) {
            $order = (int) get_post_meta($post_id, $this->questionOrderMetaKey, true);
            echo $order;
        } elseif ($this->answerTypeMetaKey === $column) {
            echo get_post_meta($post_id, $this->answerTypeMetaKey, true);
        }
    }


    public function registerTaxonomy()
    {

        // Now register the non-hierarchical taxonomy like tag

        register_taxonomy($this->questionTaxonomy, $this->questionPostType, array(
            'hierarchical' => true,
            'labels' => $this->getTaxonomyLabels('Topic', 'Topics'),
            'show_ui' => true,
            'show_admin_column' => true,
            // 'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'topic'),
        ));



        /*register_taxonomy($this->questionTaxonomy2, $this->questionPostType, array(
            'hierarchical' => false,
            'labels' => $this->getTaxonomyLabels('Test', 'Tests'),
            'show_ui' => true,
            'show_admin_column' => true,
            //'update_count_callback' => '_update_post_term_count',
            'query_var' => true,
            'rewrite' => array('slug' => 'test'),
        ));*/
    }

    public function getTaxonomyLabels($singularName, $pluralName)
    {
        $pluralNameLC = strtolower($pluralName);
        $singularNameLC = strtolower($singularName);

        return array(
            'name' => _x($pluralName, 'YWA_QandA'),
            'singular_name' => _x("{$singularName} name", 'YWA_QandA'),
            'search_items' =>  __("Search {$pluralName}", 'YWA_QandA'),
            'popular_items' => __("Popular {$pluralName}", 'YWA_QandA'),
            'all_items' => __("All {$pluralName}", 'YWA_QandA'),
            'parent_item' => null,
            'parent_item_colon' => null,
            'edit_item' => __("Edit {$singularName}", 'YWA_QandA'),
            'update_item' => __("Update {$singularName}", 'YWA_QandA'),
            'add_new_item' => __("Add New {$singularName}", 'YWA_QandA'),
            'new_item_name' => __("New {$singularName} Name", 'YWA_QandA'),
            'separate_items_with_commas' => __("Separate {$pluralNameLC} with commas", 'YWA_QandA'),
            'add_or_remove_items' => __("Add or remove {$singularNameLC}", 'YWA_QandA'),
            'choose_from_most_used' => __("Choose from the most used {$pluralNameLC}", 'YWA_QandA'),
            'menu_name' => __("{$pluralName}", 'YWA_QandA'),
        );
    }
    public function adminNotices()
    {

        if (!($errors = get_transient('ywaqa_settings_errors'))) {
            return;
        }
        $message = '';
        //Otherwise, build the list of errors that exist in the settings errors
        foreach ($errors as $error) {
            $message .= '<span>' . $error . '</span></br>';
        }
        $nottice = '<div id="message" class="error notice notice-error is-dismissible"> 
        <p><strong>' . $message . '</strong></p>
        <button type="button" class="notice-dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
         </div>';
        //Write error messages to the screen
        echo $nottice;

        //Clear and the transient and unhook any other notices so we don’t see duplicate messages
        delete_transient('ywaqa_settings_errors');
        remove_action('admin_notices', [$this, 'your_admin_notices_action']);
    }

    public function registerCPT()
    {

        register_post_type($this->questionPostType, array(
            'labels' => $this->getLabels(),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'queries'),
            'capability_type' => 'ywa-qa',
            'map_meta_cap' => true,
            'hierarchical' => false,
            'publicly_queryable' => false,
            'supports' => ['title', 'custom-fields'],
            //'taxonomies' => array('category'),
            'menu_icon'   => 'dashicons-clipboard',
            'menu_positions' => 5
        ));
    }

    public function getLabels()
    {
        return array(
            'name' => __('Advanced Q&A', 'YWA_QandA'),
            'singular_name' => 'Q&A',
            'add_new' => __('Add new question', 'YWA_QandA'),
            'add_new_item' => __('Add new question', 'YWA_QandA'),
            'edit_item' => __('Edit question', 'YWA_QandA'),
            'new_item' => __('New question', 'YWA_QandA'),
            'all_items' => __('All question', 'YWA_QandA'),
            'view_item' => __('View question', 'YWA_QandA'),
            'search_items' => __('Search question', 'YWA_QandA'),
            'menu_name' => 'Q&A'
        );
    }

    public function addQuestionMetabox()
    {
        add_meta_box(
            'ywa-answer-type',
            'Question Settings',
            array($this, 'questionMetaboxCallback'),
            $this->questionPostType,
            'normal',
            'high'

        );
    }

    public function questionMetaboxCallback($post)
    {

        wp_nonce_field($this->nonceAction, $this->nonceField);

        $answerTypeValue = get_post_meta($post->ID, $this->answerTypeMetaKey, true);
        $questionOrderValue = get_post_meta($post->ID,  $this->questionOrderMetaKey, true);

        $cssDisplayQuestionOrder = ($answerTypeValue === 'last') ? 'none' : 'block';
        $questionOrderValue = (empty($questionOrderValue)) ? 1 : $questionOrderValue;

        $this->displayHTMLDropdown($this->answerTypeMetaKey, $answerTypeValue);
        $this->displayHTMLRangeSlider($this->questionOrderMetaKey, $questionOrderValue, $cssDisplayQuestionOrder);

        //show a description textarea for the last answer
        if ($answerTypeValue === 'last') {
            $lastAnswerText = get_post_meta($post->ID,  $this->lastAnswerTextareaMetaKey, true);
            $displayLastAnswerTextarea = 'block';
        } else {
            $lastAnswerText = '';
            $displayLastAnswerTextarea = 'none';
            //$this->displayHTMLTextArea($this->lastAnswerTextareaMetaKey, $lastAnswerText,  $displayLastAnswerTextarea);
        }

        $this->displayLastMessageEditor($this->lastAnswerTextareaMetaKey, $lastAnswerText, $displayLastAnswerTextarea);
    }


    public function displayHTMLDropdown($name, $defaultValue)
    {
        $selectedTextarea = "";
        $selectedCalendar = "";
        $selectedRange = "";
        $selectedLastQuestion = "";
        switch ($defaultValue) {
            case 'textarea':
                $selectedTextarea = "selected";
                break;
            case 'calendar':
                $selectedCalendar = 'selected';
                break;
            case 'range':
                $selectedRange = 'selected';
                break;
            case 'last':
                $selectedLastQuestion = 'selected';
                break;
        }

        printf(
            "<span question-type-label><b>Select  question type:</b></label><select class='{$name}' name='{$name}'>
            <option value=''>-----------</option>
        <option value='%s' {$selectedTextarea} >%s</option >
        <option value='%s' {$selectedCalendar}>%s</option>
        <option value='%s' {$selectedRange}>%s</option>
        <option value='%s' {$selectedLastQuestion}>%s</option>
        </select>",
            'textarea',
            'Textarea',
            'calendar',
            'Calendar',
            'range',
            'Range Slider',
            'last',
            'Last Question'
        );
    }

    public function displayHTMLRangeSlider($name, $defaultValue, $display)
    {
        $defaultValue = (int) $defaultValue;
        echo "<div class='range-wrapper' style='display:{$display};'><br/>
        <span question-order-label><b>Select  question order:</b></span>
        <input 
        type='number' 
        id='qa-range-number'
        name='{$name}'
        min='1' 
        value='{$defaultValue}'
        style='width:60px;'/>
        </div>";
        echo "<script>
        document.getElementById('qa-range-number').value={$defaultValue};
        </script>";
    }

    public function displayHTMLTextArea($name, $defaultValue = null, $display)
    {
        echo "<br/><br/>
        <div class='{$name}' style='display:{$display}'>
        <label>*Describe something here...</label>
        <textarea 
        class='qustion-textarea' 
        name='{$name}' 
        rows='4' 
        cols='50' 
        placeholder='Describe something here...'
        style='width:100%;max-width:98%;'>
        {$defaultValue}</textarea></div>";
    }

    public function displayLastMessageEditor($name, $defaultValue = null, $display)
    {
        echo  "<div class='{$name}' style='display:{$display}'>";
        wp_editor($defaultValue, $name, array('media_buttons' => true));
        echo "</div>";
    }

    public function saveAnswerTypeCallback($postID)
    {

        if (!isset($_POST[$this->nonceField])) {
            return;
        }

        if (!wp_verify_nonce($_POST[$this->nonceField], $this->nonceAction)) {
            return;
        }


        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $postID)) {
            //return;
        }

        if (!isset($_POST[$this->answerTypeMetaKey])) {
            return;
        }


        if (!empty($_POST[$this->answerTypeMetaKey])) {

            update_post_meta($postID, $this->answerTypeMetaKey, sanitize_text_field($_POST[$this->answerTypeMetaKey]));
        } else {
            set_transient('ywaqa_settings_errors', ['Please select a valid question type'], 300);
        }

        //if the questions is the last oane, save the textarea metakey, else remove it
        if ($_POST[$this->answerTypeMetaKey] === 'last') {
            update_post_meta($postID, $this->lastAnswerTextareaMetaKey, $_POST[$this->lastAnswerTextareaMetaKey]);
        } else {
            if (!empty(get_post_meta($postID, $this->lastAnswerTextareaMetaKey, true))) {
                delete_post_meta($postID, $this->lastAnswerTextareaMetaKey);
            }
        }
        /*=========if question type is last, the question order is set to 1000======*/
        $questionOrderValue = ($_POST[$this->answerTypeMetaKey] === 'last') ? 1000 : $_POST[$this->questionOrderMetaKey];
        update_post_meta($postID, $this->questionOrderMetaKey, $questionOrderValue);
    }

    public function getNonceFields($name)
    {
        return [
            "{$name}_nonce_action",
            "{$name}_nonce_field"
        ];
    }
}
