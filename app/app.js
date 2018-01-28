var app = angular.module('myApp', ['ngRoute', 'ngAnimate', 'toaster']);

app.config(['$routeProvider',
  function ($routeProvider) {
        $routeProvider.
        when('/login', {
            title: 'Login',
            templateUrl: 'partials/login.html',
            controller: 'authCtrl'
        })
        .when('/logout', {
            title: 'Logout',
            templateUrl: 'partials/login.html',
            controller: 'logoutCtrl'
        })
        .when('/signup', {
            title: 'Signup',
            templateUrl: 'partials/signup.html',
            controller: 'signupCtrl'
        })
        .when('/dashboard', {
            title: 'Dashboard',
            templateUrl: 'partials/dashboard.html',
            controller: 'profileCtrl'
        })
        .when('/passwordChange', {
            title: 'Password Change',
            templateUrl: 'partials/password.html',
            controller: 'passwordCtrl'
        })
        .when('/passwordReset', {
            title: 'Password Reset',
            templateUrl: 'partials/reset.html',
            controller: 'passwordCtrl'
        })
        .when('/passwordReset/:resetLink', {
            title: 'Password Reset',
            templateUrl: 'partials/reset.html',
            controller: 'passwordCtrl'
        })
        .when('/activate', {
            title: 'Password Change',
            templateUrl: 'partials/password.html',
            controller: 'passwordCtrl'
        })
        .when('/tables', {
            title: 'tables',
            templateUrl: 'partials/tables.html',
            controller: 'tableCtrl'
        })
        .when('/', {
            title: 'Landing',
            templateUrl: 'partials/landing.html',
            role: '0'
        })
        .otherwise({
            redirectTo: '/'
        });
  }])
    .run(function ($rootScope, $location, Data, AUTH_EVENTS) {
        $rootScope.$on("$routeChangeStart", function (event, next, current) {
            Data.get('session').then(function (results) {
                if (results.id) {
                    $rootScope.id = results.id;
                    $rootScope.isAdmin = results.isAdmin;
                    $rootScope.$broadcast(AUTH_EVENTS.loginSuccess);
                } else {
                    var nextUrl = next.$$route.originalPath;
                    if (nextUrl != '' && nextUrl != '/' && nextUrl != '/login' && nextUrl.indexOf('/passwordReset') != 0) {
                        $location.path("/login");
                    }
                }
            });
        });
    });
