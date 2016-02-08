<?php

class NRDatabase extends PDO
{
    const USERQ = "
  select
  p.id,
  p.display_name as name,
  p.user_email as email,
  max(IF(pa.meta_key='wp-athletics_gender', pa.meta_value, null)) as gender,
  max(IF(pa.meta_key='wp-athletics_dob', pa.meta_value, null)) as dob
  from kai_users p
  left join kai_usermeta as pa on p.id = pa.user_id
  group by p.id
  ";

    public function __construct()
    {
        global $db;
        parent::__construct(
            "mysql:dbname={$db['database']};host={$db['host']}",
            $db['user'],
            $db['pass'],
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );
    }

    public function getUsers()
    {
        return $this->query(self::USERQ);
    }
}

class Runner
{
    public $id;
    public $name;
    public $email;
    public $gender;
    public $dob;

    public function __construct($dbrow)
    {
        $this->id = $dbrow['id'];
        $this->name = $dbrow['name'];
        $this->email = $dbrow['email'];
        $this->gender = $dbrow['gender'];
        $this->dob = $dbrow['dob'];
    }

    public function isComplete()
    {
        if ($this->dob != "01 jan 0001" && !empty($this->email))
        {
            return TRUE;
        }
        return FALSE;
    }

    public function isNewdata()
    {
        if (isset($_GET['runnermail'][$this->id]) && strlen(isset($_GET['runnermail'][$this->id])))
        {
            return TRUE;
        }
        if (isset($_GET['runnerdob'][$this->id]) && strlen(isset($_GET['runnerdob'][$this->id])) && $_GET['runnerdob'][$this->id] != "01 jan 0001")
        {
            return TRUE;
        }
        return FALSE;
    }

    public function update() {
        $mail = isset($_GET['runnermail'][$this->id]) ? $_GET['runnermail'][$this->id] : '';
        $dob = isset($_GET['runnerdob'][$this->id]) ? $_GET['runnerdob'][$this->id] : '01 jan 0001';
        // @todo regexp check?
        // @todo sql update or insert for each?
    }
}

class Util {
    static function alert($var,$label= "debug")
    {
        global $alerts;
        $time = date(DATE_ATOM);
        $data = is_string($var) ? $var : print_r($var,TRUE);
        $alerts[] = "$label: $data";
    }
}