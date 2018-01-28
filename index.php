<!DOCTYPE html>
<html lang="en" ng-app="myApp">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ECE Club Dinnerdance</title>
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <link href="css/toaster.css" rel="stylesheet">
    <link href="css/landing.css" rel="stylesheet">
    <link href="css/tables.css" rel="stylesheet">
    <style>
      a {
      color: orange;
      }
    </style>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]><link href= "css/bootstrap-theme.css"rel= "stylesheet" >

    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body ng-cloak="" class="black_background">
    <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#banner">9TECE</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul ng-if="!id" class="nav navbar-nav">
            <li><a ng-href="#about">Venue</a></li>
            <li><a ng-href="#buses">Bus Schedule</a></li>
            <li><a ng-href="#menu">Menu</a></li>
            <li><a ng-href="#tickets">Tickets</a></li>
          </ul>
          <ul ng-if="id" class="nav navbar-nav">
            <li ng-class="{'active' : page == 'signup'}" ng-if="isAdmin"><a ng-href="#signup">Sign Up</a></li>
            <li ng-class="{'active' : page == 'profile'}"><a ng-href="#dashboard">Profile</a></li>
            <li ng-class="{'active' : page == 'tables'}"><a ng-href="#tables">Tables</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li ng-if="!id" ng-class="{'active' : page == 'login'}"><a ng-href="#/login">Login</a></li>
            <li ng-if="id"><a ng-controller="authCtrl" ng-click="logout();">Logout</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
      <div class="black_background">

        <div class="main-content" data-ng-view="" id="ng-view"></div>

      </div>

    </body>
  <toaster-container toaster-options="{'time-out': 3000}"></toaster-container>
  <!-- Libs -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/angular.min.js"></script>
  <script src="js/angular-route.min.js"></script>
  <script src="js/angular-animate.min.js" ></script>
  <script src="js/toaster.js"></script>
  <script src="js/carousel.js"></script>
  <script src="app/app.js"></script>
  <script src="app/constants.js"></script>
  <script src="app/data.js"></script>
  <script src="app/directives.js"></script>
  <script src="app/authCtrl.js"></script>
  <script src="app/signupCtrl.js"></script>
  <script src="app/passwordCtrl.js"></script>
  <script src="app/profileCtrl.js"></script>
  <script src="app/tableCtrl.js"></script>
</html>
