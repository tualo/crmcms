<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;
use Tualo\Office\DS\DSFileHelper;

class TranslatorUpload {
    
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        if (isset($_FILES['userfile'])){
            @session_start();
            $db = self::db();
            $crm = CRM::getInstance();
            if (
                !is_null($crm->get('account')) &&
                $crm->get('account')->isLoggedIn() &&
                $crm->get('account')->get('login_type')=='translator'
            ) {
           
            }
            if (
                $crm->get('account')->isLoggedIn() &&
                $crm->get('account')->get('login_type')=='customer'
            ) {

                $hash = $db->singleRow('select concat("TRNS",substring(replace(replace(replace(now()," ",""),"-",""),":",""),1,12)) project, uuid() translation',[],'');
                $db->direct('insert into projects (id,created) values ({project},now())',$hash);
                $hash+=[
                    'source_language'       =>  'deutsch',
                    'destination_language'  =>  'slowakisch',
                    'kundennummer'          =>  $crm->get('account')->get('kundennummer'),
                    'kostenstelle'          =>  $crm->get('account')->get('kostenstelle')
                ];
                $db->direct('insert into translations (id,project,source_language,destination_language,created) values ({translation},{project},{source_language},{destination_language},now())',$hash);
                $db->direct('insert into translations_kunden (translation,kundennummer,kostenstelle) values ({translation},{kundennummer},{kostenstelle} )',$hash);
                
                DSFileHelper::uploadFileToDB('userfile','translations',[
                    'fieldName'=>'translations__document',
                    'translations__id'=>$hash['translation']
                ]);
            }
        }

    }
}