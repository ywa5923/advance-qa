<?php

namespace YWA\Actions;

use YWA\Actions\ActionInterface;
use YWA\Helpers\Core\BaseAction;

class MemberpressAddonAction extends BaseAction implements ActionInterface
{


    private $questionTaxonomy;
    private $mbOptionsName;
    public function __construct()
    {
        $this->questionTaxonomy = YWAAQ_CPT_question_taxonomy;
        $this->mbOptionsName = YWAAQ_OPTIONS_memberpress_options;
        parent::__construct();
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'initializeMemberpessOptions'));
        add_action('mepr_account_nav', array($this, 'addMemberpressTabs'));
        add_action('mepr_account_nav_content', array($this, 'addMemberpressTabsContent'));
    }

    public function addMemberpressTabs()
    {
        $options = get_option($this->mbOptionsName);

        foreach ($options as $k => $v) {
            if ($v == 1) {
                $link = urlencode($k);
                echo "<span class='mepr-nav-item prem-support'>
                <a href='/account/?action={$link}'>{$k}</a>
              </span>";
            }
        }
    }

    public function addMemberpressTabsContent($action)
    {
        $termName = urldecode($action);

        echo do_shortcode("[questions-list topic='{$termName}']");
    }



    public function addMenu()
    {
        add_submenu_page(
            'advanced-qa',
            'Memberpress',
            'Memberpress',
            'manage_options',
            'memberpress',
            array($this, 'memberpressPage')
        );
    }

    public function memberpressPage()
    {
        //echo  do_shortcode("[questions-list topic='Topic 1']");
        $this->template->display('memberpress', array());
    }

    public function initializeMemberpessOptions()
    {
        // If the theme options don't exist, create them.
        if (false == get_option($this->mbOptionsName)) {
            add_option($this->mbOptionsName);
        }

        add_settings_section(
            'qa_memberpress_section',         // ID used to identify this section and with which to register options
            'Memberpress Options',                  // Title to be displayed on the administration page
            array($this, 'memberpressSectioncallback'), // Callback used to render the description of the section
            'memberpress'     // Page on which to add this section of options
        );
        $terms = get_terms($this->questionTaxonomy, array("hide_empty" => false));
        if (!empty($terms) && !is_wp_error($terms)) {


            foreach ($terms as $term) {
                // Next, we'll introduce the fields for toggling the visibility of content elements.
                add_settings_field(
                    $term->name,                      // ID used to identify the field throughout the theme
                    $term->name,                           // The label to the left of the option interface element
                    array($this, 'displayCheckboxCallback'),   // The name of the function responsible for rendering the option interface
                    'memberpress',    // The page on which this option will be displayed
                    'qa_memberpress_section',         // The name of the section to which this field belongs
                    [
                        $term->term_taxonomy_id,
                        $term->name,
                        $term->slug
                    ]
                );
            }
        }

        register_setting(
            'memberpress',
            $this->mbOptionsName
        );
    }

    public function displayCheckboxCallback($args)
    {
        // First, we read the options collection
        $options = get_option($this->mbOptionsName);

        if (!empty($options) && isset($options[$args[1]])) {
            $checked = checked(1, $options[$args[1]], false);
        } else {
            $checked = '';
        }

        $html = "<input type='checkbox' id='show_header' name='{$this->mbOptionsName}[{$args[1]}]' value='1' {$checked}/>";

        echo $html;
    }

    public function memberpressSectioncallback()
    {
        echo '<p>Select which topics you wish to display in memberpress account page.</p>';
    }
}
