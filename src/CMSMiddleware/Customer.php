<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;
use Tualo\Office\DS\DSTable;

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
            $sql='
                select * from view_open_translations where kostenstelle={kostenstelle} and kundennummer={kundennummer} 
            ';
            $hash=[
                'kundennummer' => $crm->get('account')->get('kundennummer'),
                'kostenstelle' => $crm->get('account')->get('kostenstelle')
            ];
            $crm->get('account')->set('open_translations',$db->direct($sql,$hash));


            if (
                isset($_REQUEST['edit-cu-address']) &&
                isset($_REQUEST['cu-nr']) &&
                $_REQUEST['cu-nr'] == $crm->get('account')->get('kundennummer') && 
                $_REQUEST['cu-kst'] == $crm->get('account')->get('kostenstelle')
            ) {
                $table=new DSTable($db,'adressen');
                $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
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
                $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
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
                $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
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