<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;
use Tualo\Office\DS\DSTable;

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
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer') && 
                    $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle')
                ) {
                    $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
                    $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                    $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                    $table=new DSTable($db,'uebersetzer');
                    if ($table -> update($_REQUEST) === FALSE){
                        $crm -> set('error',TRUE);
                        $crm -> set('errorMessage',$table -> errorMessage());
                    }

                }
                if (
                    isset($_REQUEST['edit-tr-communication']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')&& 
                    $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle')
                ) {

                    $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
                    $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                    $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                    $table=new DSTable($db,'uebersetzer');
                    if ($table -> update($_REQUEST) === FALSE){
                        $crm -> set('error',TRUE);
                        $crm -> set('errorMessage',$table -> errorMessage());
                    }
                }
                if (
                    isset($_REQUEST['edit-tr-accounting']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')&& 
                    $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle')
                ) {
                    $_REQUEST['gutschrift']=(isset($_REQUEST['gutschrift']) ? 1:0);
                    $_REQUEST['mwst_befreit']=(isset($_REQUEST['mwst_befreit']) ? 1:0);
                    $_REQUEST['__id']=$crm->get('account')->get('kostenstelle').'|'.$crm->get('account')->get('kundennummer');
                    $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                    $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                    $table=new DSTable($db,'uebersetzer');
                    if ($table -> update($_REQUEST) === FALSE){
                        $crm -> set('error',TRUE);
                        $crm -> set('errorMessage',$table -> errorMessage());
                    }
                }         
                if(
                    isset($_REQUEST['type']) &&
                    isset($_REQUEST['edit']) &&
                    isset($_REQUEST['did']) && 
                    isset($_REQUEST['type'])=='profile' &&
                    isset($_REQUEST['edit']) && 'language'
                ){
                    $hash=['did'  => $_REQUEST['did']];
                    $sql='select * from  uebersetzer_sprachen where md5(concat(`kundennummer`,`kostenstelle`,`destination_language`,`destination_language`)) = {did}';
                    $langARR=$db->direct($sql,$hash);
                    if (
                        $langARR[0]['kundennummer']==$crm->get('account')->get('kundennummer') && 
                        $langARR[0]['kostenstelle']==$crm->get('account')->get('kostenstelle')  
                    ){
                        $sql='delete from  uebersetzer_sprachen where md5(concat(`kundennummer`,`kostenstelle`,`destination_language`,`destination_language`)) = {did}';
                        $db->direct($sql,$hash);
                    }
                }

                $langs=array_column($result['translations_languages'],'name','id');
                if(
                    isset($_REQUEST['edit-tr-language']) &&
                    isset($_REQUEST['source_language']) &&
                    isset($_REQUEST['destination_language']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer') && 
                    $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle')   &&
                    array_key_exists($_REQUEST['destination_language'],$langs) && 
                    array_key_exists($_REQUEST['source_language'],$langs)
                ){
                    $hash['kundennummer']=$crm->get('account')->get('kundennummer');
                    $hash['kostenstelle']=$crm->get('account')->get('kostenstelle');
                    $hash['source_language']=$_REQUEST['source_language'];
                    $hash['destination_language']=$_REQUEST['destination_language'];

                    $table=new DSTable($db,'uebersetzer_sprachen');
                    if ($table -> insert($hash) === FALSE){
                        $crm -> set('error',TRUE);
                        $crm -> set('errorMessage',$table -> errorMessage());
                    }

                }

                
                if (
                    isset($_REQUEST['edit-tr-attributes']) &&
                    isset($_REQUEST['tr-nr']) &&
                    $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer')&& 
                    $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle')
                ) {
                    $_REQUEST['kundennummer']=$crm->get('account')->get('kundennummer');
                    $_REQUEST['kostenstelle']=$crm->get('account')->get('kostenstelle');
                    $table=new DSTable($db,'uebersetzer_attributes');
                    if ($table -> delete($_REQUEST) === FALSE){
                        $crm -> set('error',TRUE);
                        $crm -> set('errorMessage',$table -> errorMessage());
                    }
                    if (
                        isset($_REQUEST['attributes']) && 
                        is_array($_REQUEST['attributes'])
                        )
                        {
                            foreach ($_REQUEST['attributes'] as $la => $nix){
                                $_REQUEST['attributes_id']=$la;
                                if ($table -> insert($_REQUEST) === FALSE){
                                    $crm -> set('error',TRUE);
                                    $crm -> set('errorMessage',$table -> errorMessage());
                                }                                

                            }
                        }
                }
              
                if (isset($_REQUEST['edit-tr-password'])){ 
                    if(
                        isset($_REQUEST['tr-nr']) &&
                        $_REQUEST['tr-nr'] == $crm->get('account')->get('kundennummer') && 
                        $_REQUEST['tr-kst'] == $crm->get('account')->get('kostenstelle') && 
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