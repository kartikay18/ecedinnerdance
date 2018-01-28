app.controller('profileCtrl', function ($scope, $rootScope, $routeParams, $location, Data, AUTH_EVENTS) {
    $rootScope.page = 'profile';

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        Data.get('profile/' + $rootScope.id).then(function (results) {
            $scope.user = results.user;
            $scope.yearOptions = results.yearOptions;
            $location.path(results.redirect);
        });
    });

    $scope.$on(AUTH_EVENTS.loginFailed, function() {
        $location.path("/login");
    }); 

    $scope.profileUpdate = function (user) {
        Data.put('profile/' + $rootScope.id, {
            user: user
        }).then(function (results) {
            Data.toast(results);
        });
    };
});
