app.controller('signupCtrl', function ($scope, $rootScope, $location, Data, AUTH_EVENTS) {
    $rootScope.page = 'signup';
    
    //initially set those objects to null to avoid undefined error
    $scope.signup = {
                        ticketNum: '',
                        email:'',
                        firstName:'',
                        lastName:''
                    };
    
    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        if ($rootScope.isAdmin != 1){
            $location.path("/login");
        }
    });

    $scope.$on(AUTH_EVENTS.loginFailed, function() {
        $location.path("/login");
    }); 

    $scope.signUp = function (user) {
        Data.post('signUp', {
            user: user
        }).then(function (results) {
            Data.toast(results);
            $location.path(results.redirect);
        });
    };

});

