<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;
use Tualo\Office\DS\DSFileHelper;
use Ramsey\Uuid\Uuid;

class TranslatorOffer {
    public static function run(&$request,&$result){
        if (
            isset($_REQUEST['to-do-offer-id']) // check for new translator-offer
        ){
            @session_start();
            $db = self::db();
            $crm = CRM::getInstance();
            if (
                !is_null($crm->get('account')) &&
                $crm->get('account')->isLoggedIn() &&
                $crm->get('account')->get('login_type')=='translator'
            ) {
                print_r($_REQUEST);
                exit();
            }
 
        }

    }

}