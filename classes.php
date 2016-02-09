<?php

class NRDatabase extends PDO
{
    const USERTABLE = 'wp_users';
    const USERMETATABLE = 'wp_usermeta';

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
        $query = "
  select
  p.id,
  p.display_name as name,
  p.user_email as email,
  max(IF(pa.meta_key='wp-athletics_gender', pa.meta_value, null)) as gender,
  max(IF(pa.meta_key='wp-athletics_dob', pa.meta_value, null)) as dob
  from ".NRDatabase::USERTABLE." p
  left join ".NRDatabase::USERMETATABLE." as pa on p.id = pa.user_id
  group by p.id
  ";
        $result = $this->query($query);
        $runners = array();
        if ($result) {
            foreach ($result as $dbrow) {
                $user = new Runner($dbrow);
                if (!$user->isComplete())
                {
                    $runners[] = $user;
                }
            }
        }
        return $runners;
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
        if ( (!isset($this->dob)) || empty($this->dob) || $this->dob == "01 jan 0001" )
        {
            return FALSE;
        }
        if (empty($this->email))
        {
            return FALSE;
        }
        return TRUE;
    }

    public function getNewdata() {
        $out = array('runnermail' => NULL, 'runnerdob' => NULL);

        if (isset($_GET['runnermail'][$this->id]))
            $out['runnermail'] = filter_var($_GET['runnermail'][$this->id], FILTER_VALIDATE_EMAIL) ? $_GET['runnermail'][$this->id] : NULL;
        if (isset($_GET['runnerdob'][$this->id]))
            $out['runnerdob'] = preg_match('/^\d{1,2} [a-z]+ \d{4}$/i', $_GET['runnerdob'][$this->id]) ? $_GET['runnerdob'][$this->id] : NULL;

        return $out;
    }

    public function isNewdata()
    {
        $data = $this->getNewdata();
        if (strlen($data['runnermail']) && $data['runnermail'] != $this->email)
        {
            return TRUE;
        }
        if (strlen($data['runnerdob']) && $data['runnerdob'] != $this->dob)
        {
            return TRUE;
        }
        return FALSE;
    }

    public function update() {
        global $database;
        $data = $this->getNewdata();
        if (isset($data['runnermail'])) {
            $database->exec("UPDATE ".NRDatabase::USERTABLE." SET user_email='{$data['runnermail']}' WHERE ID={$this->id}");
            $this->email = $data['runnermail'];
        }
        if (isset($data['runnerdob'])) {
            $this->updateMeta('wp-athletics_dob',$data['runnerdob']);
            $this->dob = $data['runnerdob'];

        }
    }

    public function updateMeta($metafield, $data) {
        global $database;
        $query = "SELECT * FROM ".NRDatabase::USERMETATABLE." um WHERE um.user_id={$this->id} AND um.meta_key='{$metafield}'";
        $result = $database->query($query);
        if ($result) {
            $query = "UPDATE ".NRDatabase::USERMETATABLE." SET meta_value='$data' WHERE user_id={$this->id} AND meta_key='$metafield'";
            $database->exec($query);
        } else {
            $query = "INSERT INTO ".NRDatabase::USERMETATABLE." (user_id, meta_key, meta_value) VALUES ( {$this->id}, '$metafield', '$data')";
            $database->exec($query);
        }
    }
}

class Util {
    static function alert($var,$label="debug")
    {
        global $alerts;
        $time = date(DATE_ATOM);
        $data = is_string($var) ? $var : print_r($var,TRUE);
        $alerts[] = "$label: $data";
    }

}