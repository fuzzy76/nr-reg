<?php
include "settings.php";
include "classes.php";
header('X-Robots-Tag: noindex');
header('Content-Type: text/html; charset=UTF-8');
$alerts = array();
$database = new NRDatabase();

$runners = $database->getUsers();
if (!count($runners)) {
  Util::alert('Fant ingen løpere!', 'Advarsel');
}

if(isset($_GET['op'])&&$_GET['op']=='update')
{
  foreach($runners as $id => $runner)
  {
    if ($runner->isNewdata())
    {
      $runner->update();
      Util::alert("{$runner->email} oppdatert");
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
body { padding-top: 60px; }
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
          <a class="navbar-brand" href="/">NR Registrering</a>
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

      <?php if (!empty($alerts)) { ?>
        <?php foreach ($alerts as $alert) { ?>
          <div class="alert alert-warning" role="alert"><?= $alert ?></div>
        <?php } ?>
      <?php } ?>

      <div class="starter-template">
        <h1>Løperoppdatering</h1>
        <p class="lead"Oppdater løpere som mangler data.</p>
      </div>

      <form class="form-inline" method="get">
        <input type="hidden" name="op" value="update">
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
