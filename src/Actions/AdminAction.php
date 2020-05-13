<?php

namespace YWA\Actions;

use YWA\Actions\ActionInterface;
use YWA\Helpers\Core\BaseAction;

class AdminAction extends BaseAction implements ActionInterface
{
    private $nextButtonName;
    public function __construct()
    {

        $this->nextButtonName = YWAAQ_NEXT_BUTTON_NAME;
        parent::__construct();
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'addMenu'));
        add_action('admin_init', array($this, 'initializeOptions'));
    }

    public function addMenu()
    {
        add_menu_page('Advanced Q&A', 'Advanced Q&A', 'manage_options', 'advanced-qa', [$this, 'adminPage'], 'dashicons-tickets', 6);
    }

    public function initializeOptions()
    {
        // If the theme options don't exist, create them.
        if (false == get_option($this->nextButtonName)) {
            add_option($this->nextButtonName);
        }

        add_settings_section(
            'qa_nextbutton_section',         // ID used to identify this section and with which to register options
            'Admin settings',                  // Title to be displayed on the administration page
            array($this, 'nextButtonSectionCallback'), // Callback used to render the description of the section
            'nextButtonSettingsPage'     // Page on which to add this section of options
        );
        add_settings_field(
            'qa_next_button',                      // ID used to identify the field throughout the theme
            'Next button display text',                           // The label to the left of the option interface element
            array($this, 'displayNextButtonCallback'),   // The name of the function responsible for rendering the option interface
            'nextButtonSettingsPage',    // The page on which this option will be displayed
            'qa_nextbutton_section',         // The name of the section to which this field belongs
            []
        );

        register_setting(
            'nextButtonSettingsPage',
            $this->nextButtonName
        );
    }

    public function nextButtonSectioncallback()
    {
        echo "<p>Enter the display text for Next Question button:</p>";
    }
    public function displayNextButtonCallback()
    {
        // First, we read the options collection
        $nextButtonName = get_option($this->nextButtonName);

        $html = "<input type='text' id='next_button' name='{$this->nextButtonName}' value='{$nextButtonName}' />";

        echo $html;
    }

    public function adminPage()
    {
        $this->template->display('admin', array());
    }
}
