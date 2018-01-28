app.controller('authCtrl', function ($scope, $rootScope, $location, Data) {
    $rootScope.page = 'login';

    //initially set those objects to null to avoid undefined error
    $scope.login = {};
    
    $scope.doLogin = function (user) {
        Data.post('login', {
            user: user
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                $location.path(results.redirect);
            }
        });
    };
    
    $scope.logout = function () {
        Data.get('logout').then(function (results) {
            Data.toast(results);
            $rootScope.id = null;
            $location.path('login');
        });
    }
});

