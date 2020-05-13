<?php

namespace YWA\Actions;

use YWA\Actions\Exception\DatabaseException;
use YWA\Helpers\Core\PathFinder;
use YWA\Dao\AnswerDao;

class AjaxSaveResponseAction implements ActionInterface
{
    private $pathFinder;
    private $allowAnonimousAjax;
    private $textAnswerMaxLength;

    public function __construct()
    {
        $this->pathFinder = new PathFinder();
        $this->allowAnonimousAjax = YWAAQ_ALLOW_ANONIMOUS_AJAX ? 'nopriv_' : '';
        $this->textAnswerMaxLength = YWAAQ_ANSWER_MAX_LENGTH;
    }


    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
        add_action("wp_ajax_{$this->allowAnonimousAjax}ywa_save_qresponse", array($this, 'saveQuestionResponse'));
    }

    public function enqueue()
    {
        $config_array = array(
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxNonce' => wp_create_nonce('ywa-ques-nonce')
        );
        //load ajax request js file
        wp_enqueue_script('ajax-questions-js', $this->pathFinder->getAssetsUrl() . 'js/questions-ajax.js', ['jquery']);
        wp_localize_script('ajax-questions-js', 'config', $config_array);
    }

    public function saveQuestionResponse()
    {

        $userId = $_POST['data']['user_id'];
        $questionId = $_POST['data']['question_id'];
        $answer = $_POST['data']['answer'];
        if (
            isset($userId) && is_numeric($userId) &&
            isset($questionId) && is_numeric($questionId) &&
            isset($answer) && strlen($answer) <= $this->textAnswerMaxLength
        ) {
            $answerDao = new AnswerDao();
            try {
                $answerDao->saveOrUpdate(
                    $userId,
                    $questionId,
                    $answer
                );
                $this->responseOK();
            } catch (DatabaseException $ex) {
                $this->responseError();
            }
        } else {
            $this->responseError();
        }
    }

    public function responseOK()
    {
        echo json_encode(array(
            "status" => "success",
        ));
        exit;
    }
    public function responseError()
    {
        echo json_encode(array(
            "status" => "error",
        ));
        exit;
    }
}
