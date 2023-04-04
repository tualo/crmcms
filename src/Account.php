<?php
declare(strict_types=1);
namespace Tualo\Office\CrmCms;

use Tualo\Office\Basic\TualoApplication as App;
use Michelf\MarkdownExtra;
use Ramsey\Uuid\Uuid;
use Tualo\Office\CrmCms\CRM;

class Account {
    private $_isLoggedin=false;

    private static ?Account $instance = null;
    public static function getInstance()
    {
      if (self::$instance === null) {
        self::$instance = new self();
      }
      return self::$instance;
    }

    public function db() { return App::get('session')->getDB(); }

    public function logout(){
        try{
            if (isset($_REQUEST['logout'])&&($_REQUEST['logout']==1)){
                $this->_isLoggedin=false;
                $this->_data=[];
            }
        }catch(\Exception $e){
            
        }
    }

 
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
            ( $this->_isLoggedin==false ) &&
            isset( $_REQUEST[ $ur_fld ] ) &&
            isset( $_REQUEST[ $pw_fld ] )
        ){
            $username = $_REQUEST[ $ur_fld ];
            $password = $_REQUEST[ $pw_fld ];
        }

        if ( 
            !is_null($username) &&
            !is_null($password) &&
            ($res = json_decode($this->db()->singleValue('select test_crm_login({cms_login},{cms_password}) s ',['cms_login'=>$username,'cms_password'=>$password],'s'),true))
        ){
            if ($res['success']==true){
                $this->_isLoggedin=true;
                $this->set('login',$res['login']);
                $this->set('login_type',$res['login_type']);
            }
        }

        $crm = CRM::getInstance();
        $crm->refreshLoginFieldNames();
        return $this->_isLoggedin;
    }
    public function isLoggedin(){
        return $this->_isLoggedin;
    }

    

    public array $_data=[];
    public function set(string $key, mixed $data):void{
        $this->_data[$key]=$data;
    }
    public function get(string $key):mixed{
        if (!isset($this->_data[$key])) return null;
        return $this->_data[$key];
    }
}