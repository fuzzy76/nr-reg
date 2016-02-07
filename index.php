<?php
include "settings.php";
header('X-Robots-Tag: noindex');
header('Content-Type: text/html; charset=UTF-8');
$database = new NRDatabase();

$users = $database->getUsers();
$runners = array();
foreach ($users as $dbrow) {
  $user = new Runner($dbrow);
  if (!$user->isComplete())
  {
    $runners[] = $user;
  }
}

if(isset($_GET['op'])&&$_GET['op']=='update')
{
  foreach($runners as $id => $runner)
  {
    if ($runner->isNewdata())
    {
      $runner->update();
      echo "Oppdaterte noe!!!!<br>";
    }
    if ($runner->isComplete())
    {
      unset($runners[$id]);
    }
  }

}


?><!DOCTYPE html>
<html lang="nb">
  <head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex" />
    <title>Northern Runners registrering</title>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-7s5uDGW3AHqw6xtJmNNtr+OBRJUlgkNJEo78P4b0yRw= sha512-nNo+yCHEyn0smMxSswnf/OnX6/KwJuZTlNZBjauKhTK0c+zT+q5JOCx0UFhXQ6rJR9jg6Es8gPuD2uZcYDLqSw==" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha256-KXn5puMvxCw+dAYznun+drMdG1IFl3agK0p/pqT9KAo= sha512-2e8qq0ETcfWRI4HJBzQiA3UoyFk6tbNyG+qSaIBZLyW9Xf3sWZHN/lxe9fTh1U45DpPf07yj94KsUHHWe4Yk1A==" crossorigin="anonymous"></script>
    <style>
body { padding-top: 40px; }
@media screen and (max-width: 768px) {
    body { padding-top: 0px; }
}
</style>
  </head>
  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="http://matilda.fuzzy76.net/~fuzzy76/nr-reg/">NR Registrering</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <!--li class="active"><a href="#">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li-->
          </ul>
        </div>
      </div>
    </nav>

    <div class="container">

      <div class="starter-template">
        <h1>Løperoppdatering</h1>
        <p class="lead"Oppdater løpere som mangler data.</p>
      </div>

      <form class="form-inline" method="get">
        <input type="hidden" id="op" value="update">
        <table class="table table-striped table-condensed">
          <tr>
            <th>Id</th>
            <th>Navn</th>
            <th>E-post</th>
            <th>Kjønn</th>
            <th>Fødselsdato</th>
          </tr>
          <?php foreach ($runners as $runner) { ?>
            <tr>
              <td><?php echo $runner->id; ?></td>
              <td><?php echo $runner->name; ?></td>
              <td><input type="email" title="epost" class="form-control" name="runnermail[<?php echo $runner->id; ?>]" placeholder="user@host.no" value="<?php echo $runner->email; ?>"></td>
              <td><?php echo $runner->gender; ?></td>
              <td><input type="text" title ="dd mmm yyyy" class="form-control" name="runnerdob[<?php echo $runner->id; ?>]" placeholder="01 jan 0001" value="<?php echo $runner->dob; ?>" pattern="[0-9]{2} [A-Z]{3} [0-9]{4}"></td>
            </tr>
          <?php } ?>
        </table>
        <button type="submit" class="btn btn-default">Oppdater</button>
      </form>
    </div><!-- /.container -->

  </body>
</html>
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
