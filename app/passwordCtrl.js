app.controller('passwordCtrl', function ($scope, $rootScope, $routeParams, $location, Data, AUTH_EVENTS) {

    $scope.changePassword = function (credentials) {
        Data.put('password/' + $rootScope.id, {
            credentials: credentials
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                $location.path(results.redirect);
            }
        });
    };

    $scope.resetPassword = function (user) {
        Data.post('password/reset', {
            user: user 
        }).then(function (results) {
            Data.toast(results);
            if (results.status == "success") {
                $location.path(results.redirect);
            }
        });
    };

    var resetLink = $routeParams.resetLink;
    if (resetLink != null){
        Data.get('password/reset/' + resetLink).then(function (results) {
            if (results.status == "success"){
                $scope.message = results.message; 
            } else {
                Data.toast(results);
                $location.path(results.redirect);
            }
        });
    }

});

