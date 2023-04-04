<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;

class Translator {
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        @session_start();
        $db = self::db();
        $crm = CRM::getInstance();

        $result['translations_languages']=$db->direct('select * from languages'); // don't stor in session;

        if (
            $crm->get('account')->isLoggedIn() &&
            $crm->get('account')->get('login_type')=='translator'
        ) {
            $crm->get('account')->set('open_translations',$db->direct(
                '
                select
                    translations.*,
                    translations_uebersetzer.created as since
                from
                    translations
                    join
                    translations_uebersetzer
                    on translations_uebersetzer.translation = translations.id
                where (translations_uebersetzer.kundennummer,translations_uebersetzer.kostenstelle) 
                = (select kundennummer,kostenstelle from uebersetzer_logins where login = {login} )
                ',
                ['login'=>$crm->get('account')->get('login')]
            ));
        }

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
        }
    }

}