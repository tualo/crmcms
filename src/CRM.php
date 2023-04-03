<?php
declare(strict_types=1);
namespace Tualo\Office\CrmCms;

use Tualo\Office\Basic\TualoApplication as App;
use Michelf\MarkdownExtra;
use Ramsey\Uuid\Uuid;
class CRM {
    public function logger(string $channel){
        return App::logger($channel);
    }
    public function db(){
        return App::get('session')->getDB();
    }
    public function config(){
        return App::get('configuration');
    }
    private static ?CRM $instance = null;
    public static function getInstance(): CRM
    {
      if (self::$instance === null) {
        if (isset($_SESSION['crm'])){
            self::$instance = unserialize( $_SESSION['crm'] );
        }else{
            self::$instance = new self();
            self::$instance->refreshLoginFieldNames();
        }
      }
      return self::$instance;
    }
    public array $_data=[];

    public function refreshLoginFieldNames():void{
        if (
            isset( (App::get('configuration'))['crmcms'] ) && 
            isset( (App::get('configuration'))['crmcms'] ) &&
            (App::get('configuration'))['crmcms'] == '1'
        ){
            $this->set('login','cms_login');
            $this->set('password','cms_password');
        }else{
            $this->set('login_field_name',(Uuid::uuid4())->toString());
            $this->set('password_field_name',(Uuid::uuid4())->toString());
        }
        
    }

    public function set(string $key, mixed $data):void{
        $this->_data[$key]=$data;
    }
    public function get(string $key):mixed{
        if (!isset($this->_data[$key])) return null;
        return $this->_data[$key];
    }

}