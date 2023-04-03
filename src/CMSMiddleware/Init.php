<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;

class Init {
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        @session_start();
        $db = self::db();
        $crm = CRM::getInstance();
        $account = $crm->get('account');

        if (is_null($account)) $crm->set('account',Account::getInstance());
        $crm->get('account')->login();

        $_SESSION['crm'] = serialize($crm);
        $result['crm'] = $crm;
        $result['md'] = self::markdownfn();
        $result['texts'] = self::textfn();
    }

    private static $textsSQL = 'select id,value_plain from page_texts where id={id} ';
    public static function markdownfn():mixed{
        return function(string $textkey):string{
            $result = MarkdownExtra::defaultTransform( self::textfn()($textkey) );
            if (strpos($result,"<p>")===0) $result = substr( $result ,3,-3);
            return $result;
        };
    }
    public static function textfn():mixed{
        return function(string $textkey):string{
            $db = App::get('session')->getDB();
            $txt = $db->singleValue(self::$textsSQL,['id'=>$textkey],'value_plain');
            if ($txt===false)$txt = "{$textkey} not defined";
            return $txt;
        };
    }

}