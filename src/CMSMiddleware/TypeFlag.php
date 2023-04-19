<?php
namespace Tualo\Office\CrmCms\CMSMiddleware;
use Tualo\Office\Basic\TualoApplication as App;
use Tualo\Office\CrmCms\CRM;
use Tualo\Office\CrmCms\Account;
use Michelf\MarkdownExtra;

class TypeFlag {
    public static function run(&$request,&$result){
        @session_start();
        $crm = CRM::getInstance();
        if (isset($_REQUEST['type'])&&is_string($_REQUEST['type'])) $crm->set('type',$_REQUEST['type']);
        if (isset($_REQUEST['edit'])&&is_string($_REQUEST['type'])) $crm->set('edit',$_REQUEST['edit']);
    }

}