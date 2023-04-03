<?php
declare(strict_types=1);
namespace Tualo\Office\CrmCms;

use Tualo\Office\Basic\TualoApplication as App;
use Michelf\MarkdownExtra;
use Ramsey\Uuid\Uuid;
use Tualo\Office\CrmCms\CRM;

class Account {
    private $isLoggedin=false;

    private static ?Account $instance = null;
    public static function getInstance()
    {
      if (self::$instance === null) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function db() { return App::get('session')->getDB(); }
    public function login(
        string $username = null,
        string $password = null
    ){
        $crm = CRM::getInstance();
        $ur_fld = $crm->get('login_field_name');
        $pw_fld = $crm->get('password_field_name');
        if ( 
            is_null($username) &&
            is_null($password) &&
            ( $this->isLoggedin==false ) &&
            isset( $_REQUEST[ $ur_fld ] ) &&
            isset( $_REQUEST[ $pw_fld ] )
        ){
            $username = $_REQUEST[ $ur_fld ];
            $password = $_REQUEST[ $pw_fld ];
        }

        if ( 
            !is_null($username) &&
            !is_null($password) &&
            ($res = json($this->db()->singleValue('select test_crm_login({cms_login},{cms_password}) s ',['cms_login'=>$username,'crm_password'=>$password],'s'),true))
        ){
            if ($res['success']==true){
                $this->isLoggedin=true;
                $this->set('login',$res['login']);
                $this->set('login_type',$res['login_type']);
            }
        }

        $crm = CRM::getInstance();
        $crm->set('login_field_name',(Uuid::uuid4())->toString());
        $crm->set('password_field_name',(Uuid::uuid4())->toString());
        
        return $this->isLoggedin;
    }

    public static array $_data=[];
    public function set(string $key, mixed $data):void{
        $this->_data[$key]=$data;
    }
    public function get(string $key):mixed{
        if (!isset($this->_data[$key])) return null;
        return $this->_data[$key];
    }
}