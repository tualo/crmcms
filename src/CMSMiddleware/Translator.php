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

        $result['translations_languages']=$db->direct('select distinct id,name from languages'); // don't store in session;

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
               if (
                  isset($_REQUEST['to-do-offer-id']) // check for new translator-offer
                  // alter table translations_uebersetzer add guaranteed_final_date datetime default null
                  // alter table translations_uebersetzer change offer_days offer_end datetime default null
                  // alter table translations_uebersetzer add message varchar(512) default null
                ){
                    if(
                        isset($_REQUEST['finished-date']) &&
                        isset($_REQUEST['gueltig-bis-date']) &&
                        isset($_REQUEST['offer-amount']) &&
                        isset($_REQUEST['agb-read']) 
                    ){
                        $message=(isset($_REQUEST['message']) ? $_REQUEST['message']:NULL);   
                        $hash=[
                            'offer_amount'  => $_REQUEST['offer-amount'],
                            'offer_date'  => date('Y-m-d H:i:s'),
                            'offer_end'  => $_REQUEST['gueltig-bis-date'],
                            'guaranteed_final_date'  => $_REQUEST['finished-date'],
                            'message'  => $message,
                            'kundennummer'  => $crm->get('account')->get('kundennummer'),
                            'translation'  => $_REQUEST['to-do-offer-id']
                        ];
                        $sql = ' update translations_uebersetzer set 
                            offer_amount = {offer_amount},
                            offer_date = {offer_date},
                            offer_end = {offer_end},
                            guaranteed_final_date = {guaranteed_final_date},
                            message = {message}
                            where translation={translation} and kundennummer={kundennummer}';
                            $db->direct($sql,$hash);
                    }
                }

                if (
                    isset($_REQUEST['edit-tr-address']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')
                ) {
                    $hash=[
                        'firma' =>  $_REQUEST['firma'],
                        'zusatz' =>  $_REQUEST['zusatz'],
                        'vorname' =>  $_REQUEST['vorname'],
                        'nachname' =>  $_REQUEST['nachname'],
                        'strasse' =>  $_REQUEST['strasse'],
                        'haus_nr' =>  $_REQUEST['haus_nr'],
                        'plz' =>  $_REQUEST['plz'],
                        'ort' =>  $_REQUEST['ort'],
                        'ortsteil' =>  $_REQUEST['ortsteil'],
                        'kundennummer'  => $crm->get('account')->get('kundennummer')
                    ];
                    $sql='update uebersetzer set firma={firma}, zusatz={zusatz}, vorname={vorname}, nachname={nachname}, strasse={strasse}, haus_nr={haus_nr}, plz={plz}, ort={ort}, ortsteil={ortsteil}
                    where kundennummer={kundennummer}';
                    $db->direct($sql,$hash);
                }
                if (
                    isset($_REQUEST['edit-tr-communication']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')
                ) {
                    $hash=[
                        'telefon' =>  $_REQUEST['telefon'],
                        'telefax' =>  $_REQUEST['telefax'],
                        'telefon2' =>  $_REQUEST['telefon2'],
                        'email' =>  $_REQUEST['email'],
                        'website' =>  $_REQUEST['website'],
                        'kundennummer'  => $crm->get('account')->get('kundennummer')
                    ];
                    $sql='update uebersetzer set telefon={telefon}, telefax={telefax}, telefon2={telefon2}, email={email}, website={website}
                    where kundennummer={kundennummer}';
                    $db->direct($sql,$hash);
                }
                if (
                    isset($_REQUEST['edit-tr-accounting']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')
                ) {
                    $hash=[
                        'firma' =>  $_REQUEST['firma'],
                        'zusatz' =>  $_REQUEST['zusatz'],
                        'vorname' =>  $_REQUEST['vorname'],
                        'nachname' =>  $_REQUEST['nachname'],
                        'strasse' =>  $_REQUEST['strasse'],
                        'haus_nr' =>  $_REQUEST['haus_nr'],
                        'plz' =>  $_REQUEST['plz'],
                        'ort' =>  $_REQUEST['ort'],
                        'ortsteil' =>  $_REQUEST['ortsteil'],
                        'kundennummer'  => $crm->get('account')->get('kundennummer')
                    ];
                    $sql='update uebersetzer set firma={firma}, zusatz={zusatz}, vorname={vorname}, nachname={nachname}, strasse={strasse}, haus_nr={haus_nr}, plz={plz}, ort={ort}, ortsteil={ortsteil}
                    where kundennummer={kundennummer}';
                    $db->direct($sql,$hash);
                }                                

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