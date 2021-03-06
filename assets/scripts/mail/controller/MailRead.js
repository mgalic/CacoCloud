angular.module('caco.mail.crtl')
    .controller('MailReadCrtl', function ($rootScope, $scope, $stateParams, $location, MailREST) {
        $rootScope.id = $stateParams.id;
        $rootScope.mailBoxBase64 = $stateParams.mailBoxBase64;

        MailREST.one($stateParams, function (data) {
            var mail = data.response;

            if (!mail.seen) {
                $rootScope.$broadcast('MailChanged', {id: $stateParams.id, mailBox: $stateParams.mailBox});
            }

            $scope.mail = mail;
        });
    });