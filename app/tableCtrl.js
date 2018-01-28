app.controller('tableCtrl', function ($scope, $rootScope, $routeParams, $location, Data, AUTH_EVENTS) {
    $rootScope.page = 'tables';

    $scope.$on(AUTH_EVENTS.loginSuccess, function() {
        Data.get('tables').then(function (results) {
            $scope.tables = results.tables;
            $scope.tableId = results.tableId;
        });
    });

    $scope.tableUpdate = function (tableNum) {
        Data.put('tables/' + tableNum, {
            //nothing needed for this call
        }).then(function (results) {
            Data.toast(results);
            Data.get('tables').then(function (results) {
                $scope.tables = results.tables;
                $scope.tableId = results.tableId;
            });
        });
    };

    $scope.removeFromTables = function () {
        Data.delete('tables').then(function (results) {
            Data.toast(results);
            Data.get('tables').then(function (results) {
                $scope.tables = results.tables;
                $scope.tableId = results.tableId;
            });
        });
    };
});
