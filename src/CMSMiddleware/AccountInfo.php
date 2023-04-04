<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;

class AccountInfo {
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        @session_start();
        $db = self::db();
        $crm = CRM::getInstance();
        if (
            $crm->get('account')->isLoggedIn() &&
            is_null($crm->get('account')->get('kundennummer')) &&
            ($crm->get('account')->get('login_type')=='customer') &&
            ($data = $db->singleRow('select * from adressen_logins where login = {login}',['login'=>$crm->get('account')->get('login')]))
        ) {
            ;
            $crm->get('account')->set('kundennummer',$data['kundennummer']);
            $crm->get('account')->set('kostenstelle',$data['kostenstelle']);
            $crm->get('account')->set('email',$data['email']);
            
        }

        if (
            $crm->get('account')->isLoggedIn() &&
            is_null($crm->get('account')->get('kundennummer')) &&
            ($crm->get('account')->get('login_type')=='translator') &&
            ($data = $db->singleRow('select * from uebersetzer_logins where login = {login}',['login'=>$crm->get('account')->get('login')]))
        ) {
            $crm->get('account')->set('kundennummer',$data['kundennummer']);
            $crm->get('account')->set('kostenstelle',$data['kostenstelle']);
            $crm->get('account')->set('email',$data['email']);
            
        }

        if (
            $crm->get('account')->isLoggedIn() &&
            is_null($crm->get('account')->get('angestelltennummer')) &&
            ($crm->get('account')->get('login_type')=='employee') &&
            ($data = $db->singleRow('select * from angestellten_logins where login = {login}',['login'=>$crm->get('account')->get('login')]))
        ) {
            $crm->get('account')->set('angestelltennummer',$data['angestelltennummer']);
            $crm->get('account')->set('email',$data['email']);
            
        }

    }

}