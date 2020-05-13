<?php

/**
 * Created by PhpStorm.
 * User: ywa
 * Date: 08.11.2018
 * Time: 15:21
 */

namespace YWA\Actions;


use YWA\Actions\ActionInterface;
use YWA\Helpers\Core\PathFinder;

class LoadAssetsAction implements ActionInterface
{
    private $pathFinder;

    public function __construct()
    {
        $this->pathFinder = new PathFinder();
    }
    public function init()
    {

        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueue'));
    }

    function enqueue()
    {
        //update jquery version:
        // wp_deregister_script('jquery');
        // Change the URL if you want to load a local copy of jQuery from your own server.
        // wp_register_script('jquery', "https://code.jquery.com/jquery-3.4.1.min.js", array(), '3.4.1');


        wp_enqueue_script('questions-js', $this->pathFinder->getAssetsUrl() . 'js/questions-main.js', ['jquery']);

        wp_register_style('jquery-calendar-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
        wp_enqueue_style('jquery-calendar-css');

        wp_register_style('ion-slider-css', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/css/ion.rangeSlider.min.css');
        wp_enqueue_style('ion-slider-css');

        wp_register_script('jQuery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.js', ['jquery'], null, true);
        wp_enqueue_script('jQuery-ui');

        wp_register_script('ion-slider-js', 'https://cdnjs.cloudflare.com/ajax/libs/ion-rangeslider/2.3.1/js/ion.rangeSlider.min.js', ['jquery'], null, true);
        wp_enqueue_script('ion-slider-js');

        wp_enqueue_style('qustions-css', $this->pathFinder->getAssetsUrl() . 'css/questions-main.css');
    }

    public function adminEnqueue()
    {
        wp_enqueue_script('questions-admin-js', $this->pathFinder->getAssetsUrl() . 'js/questions-admin.js', ['jquery']);
    }
}
