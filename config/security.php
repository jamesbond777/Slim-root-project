<?php
//Controle d'accès de l'application
return [   'config' => [
                'EntityName' => 'Users',
                'ListRoles' => [
                    'ROLE_USER',
                    'ROLE_ADMIN',
                    'ROLE_SUPER_ADMIN',
                ],
                'PathSecurity' => [
                    '/user/dashboard' => 'ROLE_USER',
                    '/admin/dashboard' => 'ROLE_ADMIN',
                    '/super-admin/dashboard' => 'ROLE_SUPER_ADMIN',
                ],
                'AuthenticateWith' => [
                    'login' => 'Username',  // Username or Email
                ],
                'EmailConfirmation' => true,
                'TokenValidityTime' => 300, // in seconds (6*60 = 300) = 6 min
            ],

           ];