<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;

class Customer {
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        @session_start();
        $db = self::db();
        $crm = CRM::getInstance();

        $result['translations_languages']=$db->direct('select distinct id,name from languages');

        if (
            $crm->get('account')->isLoggedIn() &&
            $crm->get('account')->get('login_type')=='customer'
        ) {
            $crm->get('account')->set('open_translations',$db->direct(
                '
                select
                    translations.*,
                    translations_kunden.created as since
                from
                    translations
                    join
                    translations_kunden
                    on translations_kunden.translation = translations.id
                where (translations_kunden.kundennummer,translations_kunden.kostenstelle) 
                = (select kundennummer,kostenstelle from adressen_logins where login = {login} )
                ',
                ['login'=>$crm->get('account')->get('login')]
            ));

            if (
                isset($_REQUEST['edit-cu-address']) &&
                isset($_REQUEST['cu-nr']) &&
                $_REQUEST['cu-nr'] == $crm->get('account')->get('kundennummer') && 
                $_REQUEST['cu-kst'] == $crm->get('account')->get('kostenstelle')
            ) {
                $table=new DSTable($db,'adressen');
                $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                if ($table -> update($_REQUEST) === FALSE){
                    $crm -> set('error',TRUE);
                    $crm -> set('errorMessage',$table -> errorMessage());
                }
                
            }                

            if (
                isset($_REQUEST['edit-cu-communication']) &&
                isset($_REQUEST['cu-nr']) &&
                $_REQUEST['cu-nr'] == $crm->get('account')->get('kundennummer') && 
                $_REQUEST['cu-kst'] == $crm->get('account')->get('kostenstelle')
            ) {
                $table=new DSTable($db,'adressen');
                $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                if ($table -> update($_REQUEST) === FALSE){
                    $crm -> set('error',TRUE);
                    $crm -> set('errorMessage',$table -> errorMessage());
                }
                
            }

            if (
                isset($_REQUEST['edit-cu-accounting']) &&
                isset($_REQUEST['cu-nr']) &&
                $_REQUEST['cu-nr'] == $crm->get('account')->get('kundennummer') && 
                $_REQUEST['cu-kst'] == $crm->get('account')->get('kostenstelle')
            ) {
                $table=new DSTable($db,'adressen');
                $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                if ($table -> update($_REQUEST) === FALSE){
                    $crm -> set('error',TRUE);
                    $crm -> set('errorMessage',$table -> errorMessage());
                }
    
            }

            if (isset($_REQUEST['edit-cu-password'])){ 
                if(
                    isset($_REQUEST['cu-nr']) &&
                    $_REQUEST['cu-nr'] == $crm->get('account')->get('kundennummer') && 
                    $_REQUEST['cu-kst'] == $crm->get('account')->get('kostenstelle')&&
                    ($_REQUEST['new_pw1']==$_REQUEST['new_pw2'])
                ){
                    if ($crm->get('account')->setPassword($_REQUEST['old_pw'],$_REQUEST['new_pw1'])){
                        $crm->set('message','Das Passwort wurde geändert!');
                    } 
                }else {
                    $crm->set('message','Das Passwort konnte nicht geändert werden - überprüfen Sie Ihre Eingaben!');
                    $crm->set('edit','password');
                }    
            }            
        }
    }
}