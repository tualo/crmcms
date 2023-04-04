<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;
use Tualo\Office\DS\DSFileHelper;

class TranslatorDownload {
    
    public static function db() { return App::get('session')->getDB(); }
    public static function run(&$request,&$result){
        if (
            isset($_REQUEST['translations_download'])
        ){
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
                !is_null($crm->get('account')) &&
                $crm->get('account')->isLoggedIn() &&
                $crm->get('account')->get('login_type')=='customer'
            ) {
                // finished-date

                $sql = '
                select 
                    document
                from 
                    translations
                    join translations_kunden
                        on translations_kunden.translation = translations.id
                        and (translations.id,translations_kunden.kundennummer,translations_kunden.kostenstelle) = ({translation},{kundennummer},{kostenstelle})
                ';
                $hash=[
                    'translation'           =>  preg_replace("/[^0-9a-z\-]/","",$_REQUEST['translations_download']),
                    'kundennummer'          =>  $crm->get('account')->get('kundennummer'),
                    'kostenstelle'          =>  $crm->get('account')->get('kostenstelle')
                ];
                $document = $db->singleValue($sql,$hash,'document');
                if ($document!==false){
                    $res = DSFileHelper::getFile($db,'translations',$document,true);
                    if($res['success']===true){
                        header('Expires: 0');
                        header('Content-Type: '.$res['mime']);
                        header('Cache-Control: must-revalidate');
                        header('Pragma: public');
                        header('Content-Length: ' . $res['length']);
                        echo $res['data'];
                        exit();
                    }
                    
                }
            }
        }

    }
}