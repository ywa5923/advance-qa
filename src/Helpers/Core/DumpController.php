<?php

namespace YWA\Helpers\Core;

class DumpController
{

    public function __construct()
    {
        add_action('admin_notices', array($this, 'adminNotices'), 1);
    }

    public function adminNotices()
    {

        if (!($errors = get_transient('ywaqa_settings_errors1'))) {
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
        delete_transient('ywaqa_settings_errors1');
        remove_action('admin_notices', [$this, 'adminNotices']);
    }

    public function noticeDump($var)
    {
        ob_start();
        var_dump($var);
        $err = ob_get_clean();
        set_transient('ywaqa_settings_errors1', [$err], 300);
    }

    public function dump($var)
    {
        echo "<pre>";
        print_r($var);
        echo "</pre>";
    }
}
