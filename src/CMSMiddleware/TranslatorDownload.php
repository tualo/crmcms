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
            isset($_REQUEST['translation_original_download']) ||
            isset($_REQUEST['translation_result_download'])
        ){
            @session_start();
            $db = self::db();
            $crm = CRM::getInstance();
            if (
                !is_null($crm->get('account')) &&
                $crm->get('account')->isLoggedIn() &&
                (
                    ($crm->get('account')->get('login_type')=='translator') ||
                    ($crm->get('account')->get('login_type')=='customer')
                )
            ) {
                $sql = '
                select 
                    document,
                    result
                from 
                    translations
                    join translations_$tbl$ bez
                        on bez.translation = translations.id
                        and (translations.id,bez.kundennummer,bez.kostenstelle) = ({translation},{kundennummer},{kostenstelle})
                ';
                $sql = str_replace('$tbl$',($crm->get('account')->get('login_type')=='translator')?'uebersetzer':'adressen', $sql);
                $hash=[
                    'translation'           =>  preg_replace("/[^0-9a-z\-]/","",$_REQUEST['translation_original_download']),
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