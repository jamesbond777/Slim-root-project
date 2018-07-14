<?php
// Chargement des dépendences
require __DIR__ .'/../container.php';

/**
 * @param array $array
 * @param $container
 * @return array
 */
function updateUserRole(array $array, $container){
    $errors = [];
    $em = $container['getEntityManager'];
    $result = [
        'user' => null,
        'errors' => $errors
    ];
    $user = $em->getRepository('App\Entity\Users')->findOneBy(['username'=>$array['username']]);
    if ($user !== null and $array['role'] !== ''){
        $user->setRole($array['role']);
        $em->persist($user);
        $em->flush();
        $result['user'] = $user;
    }else{
        $errors['username'] = 'Veuillez entrer un username et un role valide.';
        $result['errors'] = $errors;
    }
    return $result;
}

/**
 * @param array $data
 * @param $container
 * @return array
 */
function addUser(array $data, $container){
    $em = $container['getEntityManager'];
    $errors = [];
    \Respect\Validation\Validator::notEmpty()->validate($data['username']) || $errors['username'] = 'Veuillez entrer un username valide.';
    \Respect\Validation\Validator::email()->validate($data['email']) || $errors['email'] = 'Votre email n\'est pas valide.';
    \Respect\Validation\Validator::notEmpty()->validate($data['password']) || $errors['password'] = 'Veuillez saisir un mot de passe correcte.';

    $result = [
        'user' => null,
        'errors' => $errors
    ];
    if (empty($errors)){
        //vérifions que l'utilisateur n'existe pas déjà
        $userList = $em->getRepository('App\Entity\Users')->findAll();
        foreach ($userList as $item) {
            if ($item->getUsername() === $data['username'] or $item->getEmail() === $data['email']){
                $errors['registration'] = 'Un utilisateur possède déjà un username ou un email identique!';
                return $result = [
                    'user' => null,
                    'errors' => $errors
                ];
            }
        }
        $user = new \App\Entity\Users();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setEnable(1);
        $user->setRole('ROLE_USER');
        $em->persist($user);
        $em->flush();
        $result['user'] = $user;
    }
    return $result;
}


/**
 * @param array $array
 * @param $container
 * @return array
 */
function changeUserPass(array $array, $container){
    $errors = [];
    $em = $container['getEntityManager'];
    $result = [
        'user' => null,
        'errors' => $errors
    ];
    $user = $em->getRepository('App\Entity\Users')->findOneBy(['username'=>$array['username']]);
    if ($user !== null and $array['password'] !== ''){
        $user->setPassword($array['password']);
        $em->persist($user);
        $em->flush();
        $result['user'] = $user;
    }else{
        $errors['username'] = 'Veuillez entrer un username et un password valide.';
        $result['errors'] = $errors;
    }
    return $result;
}

/**
 * @param array $array
 * @param $container
 * @return array
 */
function activateUser(array $array, $container){
    $errors = [];
    $em = $container['getEntityManager'];
    $result = [
        'user' => null,
        'errors' => $errors
    ];
    $user = $em->getRepository('App\Entity\Users')->findOneBy(['username'=>$array['username']]);
    if ($user !== null){
        $user->setEnable(1);
        $em->persist($user);
        $em->flush();
        $result['user'] = $user;
    }else{
        $errors['username'] = 'Veuillez entrer un username valide.';
        $result['errors'] = $errors;
    }
    return $result;
}

/**
 * @param array $array
 * @param $container
 * @return array
 */
function desactivateUser(array $array, $container){
    $errors = [];
    $em = $container['getEntityManager'];
    $result = [
        'user' => null,
        'errors' => $errors
    ];
    $user = $em->getRepository('App\Entity\Users')->findOneBy(['username'=>$array['username']]);
    if ($user !== null){
        $user->setEnable(0);
        $em->persist($user);
        $em->flush();
        $result['user'] = $user;
    }else{
        $errors['username'] = 'Veuillez entrer un username valide.';
        $result['errors'] = $errors;
    }
    return $result;
}

/**
 * @param $argv
 */
function commandLine($argv, $container, $config){
    $climate = new League\CLImate\CLImate;
    $climate->style->addCommand('error', 'red');
    $config = $config['settings']['doctrine'];
    $host = gethostbyname($config['connection']['host']);
// Liste des lignes commandes
    switch ($argv) {
        case !isset($argv[1]):
            $climate->green('Slim framework V3 by whiteknight');
            $climate->br();
            $data = [
                ['[0m Command :', ''],
                ['[32m cache:clear', '[0m Delete all the cache files.'],
                ['[32m server:run', '[0m Launch the server.'],
                ['[32m doctrine:list', '[0m Doctrine Command Line Interface 2.5.10.'],
                ['[32m doctrine:database:create', '[0m Create a database with your dbname.'],
                ['', ''],
                ['[0m User command :', ''],
                ['[32m add:user', '[0m Create a new user.'],
                ['[32m update:user:role', '[0m Update the user role.'],
                ['[32m change:user:pass', '[0m Change user password.'],
                ['[32m user:activate', '[0m Activate the user account.'],
                ['[32m user:desactivate', '[0m Desactivate the user account.'],
            ];
            $climate->columns($data);
            break;
        case $argv[1] === 'server:run':
            echo `php -S $host:8080 -t web -ddisplay_errors=1 -dzned_extension=xdebug.so`;
            break;
        case $argv[1] === 'cache:clear':
            echo `rm -R var/cache`;
            $progress = $climate->progress()->total(100);
            for ($i = 0; $i <= 100; $i++) {
                $progress->current($i);
                usleep(8000);
            }
            $climate->out('Cache:clear with success');
            break;
        case $argv[1] === 'doctrine:help':
            echo `"vendor/bin/doctrine" help`;
            break;
        case $argv[1] === 'doctrine:database:create':
            $bdd = $container['pdo'];
            $dbname = $config['connection']['dbname'];
            $stmt = $bdd->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =?');
            $stmt->execute(array($dbname));
            if ($stmt->fetchColumn() == 0) {
                // la bdd  n'existe pas
//                echo `"vendor/bin/doctrine" dbal:run-sql "CREATE DATABASE $db"`;
                $db = $bdd->prepare('CREATE DATABASE '.$dbname);
                $db->execute();
                $progress = $climate->progress()->total(100);
                for ($i = 0; $i <= 100; $i++) {
                    $progress->current($i);
                    usleep(8000);
                }
                $climate->out('DATABASE '.$dbname.' CREATE with success');
            } else {
                // la bdd  existe
                $climate->error('DATABASE '.$dbname.' exist');
            }
            break;

        //----------------------------------
        //          USER COMMANDES
        //-----------------------------------
        case $argv[1] === 'add:user':
            $username = $climate->green()->input('username:');
            $rep1 = $username->prompt();
            $email = $climate->green()->input('email:');
            $rep2 = $email->prompt();
            $password = $climate->green()->password('Please enter password:');
            $rep3 = $password->prompt();
            $user_data = [
                'username' => $rep1,
                'email' => $rep2,
                'password' => $rep3,
            ];
            $result = addUser($user_data, $container);
            if (count($result['errors']) === 0 and $result['user'] !== null){
                $progress = $climate->progress()->total(100);
                for ($i = 0; $i <= 100; $i++) {
                    $progress->current($i);
                    usleep(8000);
                }
                $climate->out('user '.$rep1.' has been created with success!');
                break;
            }else{
                foreach ($result['errors'] as $error){
                    $climate->error($error);
                }
                break;
            }
        case $argv[1] === 'update:user:role':
            $username = $climate->green()->input('username:');
            $rep1 = $username->prompt();
            $role = $climate->green()->input('new role:');
            $rep2 = $role->prompt();
            $user_data = [
                'username' => $rep1,
                'role' => $rep2,
            ];
            $result = updateUserRole($user_data, $container);
            if (count($result['errors']) === 0 and $result['user'] !== null){
                $climate->green('user role '.$rep2.' has been added with success!');
                break;
            }else{
                foreach ($result['errors'] as $error){
                    $climate->error($error);
                }
                break;
            }
        case $argv[1] === 'change:user:pass':
            $username = $climate->green()->input('username:');
            $rep1 = $username->prompt();
            $password = $climate->green()->password('Please enter new password:');
            $rep2 = $password->prompt();
            $user_data = [
                'username' => $rep1,
                'password' => $rep2,
            ];
            $result = changeUserPass($user_data, $container);
            if (count($result['errors']) === 0 and $result['user'] !== null){
                $climate->green('user password has been changed with success!');
                break;
            }else{
                foreach ($result['errors'] as $error){
                    $climate->error($error);
                }
                break;
            }
        case $argv[1] === 'user:activate':
            $username = $climate->green()->input('username:');
            $rep1 = $username->prompt();
            $user_data = [
                'username' => $rep1
            ];
            $result = activateUser($user_data, $container);
            if (count($result['errors']) === 0 and $result['user'] !== null){
                $climate->green('user has been activated with success!');
                break;
            }else{
                foreach ($result['errors'] as $error){
                    $climate->error($error);
                }
                break;
            }
        case $argv[1] === 'user:desactivate':
            $username = $climate->green()->input('username:');
            $rep1 = $username->prompt();
            $user_data = [
                'username' => $rep1
            ];
            $result = desactivateUser($user_data, $container);
            if (count($result['errors']) === 0 and $result['user'] !== null){
                $climate->green('user has been desactivated with success!');
                break;
            }else{
                foreach ($result['errors'] as $error){
                    $climate->error($error);
                }
                break;
            }
        //----------------------------------
        //          DBAL COMMANDES
        //-----------------------------------
        case $argv[1] === 'doctrine:run-sql':
            echo `"vendor/bin/doctrine" dbal:run-sql $argv[2]`;
            break;
        case $argv[1] === 'doctrine:import':
            echo `"vendor/bin/doctrine" dbal:import $argv[2]`;
            break;
        case $argv[1] === 'doctrine:dbal:import':
            echo `"vendor/bin/doctrine" dbal:import $argv[2]`;
            break;
        //----------------------------------
        //          ORM COMMANDES
        //-----------------------------------
        case $argv[1] === 'doctrine:convert-mapping':
            echo `"vendor/bin/doctrine" orm:convert-mapping $argv[2] $argv[3]`;
            break;
        case $argv[1] === 'doctrine:production:settings':
            echo `"vendor/bin/doctrine" orm:ensure-production-settings`;
            break;
        case $argv[1] === 'doctrine:generate:proxies':
            echo `"vendor/bin/doctrine" orm:generate-proxies`;
            break;
        case $argv[1] === 'doctrine:generate:entities':
            echo `"vendor/bin/doctrine" orm:generate-entities "./"`;
            break;
        case $argv[1] === 'doctrine:generate:repositories':
            echo `"vendor/bin/doctrine" orm:generate-repositories $argv[2]`;
            break;
        case $argv[1] === 'doctrine:entities:info':
            echo `"vendor/bin/doctrine" orm:info`;
            break;
        case $argv[1] === 'doctrine:run-dql':
            echo `"vendor/bin/doctrine" dbal:run-dql $argv[2]`;
            break;
        case $argv[1] === 'doctrine:mapping:describe':
            echo `"vendor/bin/doctrine" orm:mapping:describe $argv[2]`;
            break;
        case $argv[1] === 'doctrine:schema:drop':
            echo `"vendor/bin/doctrine" orm:schema-tool:drop`;
            break;
        case $argv[1] === 'doctrine:schema:create':
            echo `"vendor/bin/doctrine" orm:schema-tool:create`;
            break;
        case $argv[1] === 'doctrine:schema:update':
            echo `"vendor/bin/doctrine" orm:schema-tool:update $argv[2]`;
            break;
        case $argv[1] === 'doctrine:list':
            $climate->green('Doctrine Command Line Interface 2.5.10');
            $climate->br();
            $climate->white('Usage:');
            $climate->white('command [options] [arguments]');
            $data = [
                ['[0m Options :', ''],
                ['[32m -h, --help ', '[0m Display this help message.'],
                ['[32m -q, --quiet', '[0m Do not output any message.'],
                ['[32m -V, --version ', '[0m Display this application version.'],

                ['', ''],
                ['[32m --ansi', '[0m Force ANSI output.'],
                ['[32m --no-ansi', '[0m Disable ANSI output.'],
                ['[32m -n, --no-interaction', '[0m Do not ask any interactive question.'],
                ['[32m -v|vv|vvv, --verbose', '[0m Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug.'],

                ['', ''],
                ['[0m Available commands', ''],
                ['[32m doctrine:help', '[0m Displays help for a command.'],
                ['[32m doctrine:list ', '[0m Lists commands.'],

                ['[0m dbal', ''],
                ['[32m doctrine:import ', '[0m Import SQL file(s) directly to Database.'],
                ['[32m doctrine:run-sql', '[0m Executes arbitrary SQL directly from the command line.'],
                ['[32m doctrine:database:create', '[0m Create your database.'],
                ['[0m orm', ''],
                ['[32m doctrine:convert:mapping  ', '[0m Convert mapping information between supported formats.'],
                ['[32m doctrine:production:settings', '[0m Verify that Doctrine is properly configured for a production environment.'],
                ['[32m doctrine:generate:entities', '[0m Generate entity classes and method stubs from your mapping information.'],
                ['[32m doctrine:generate:proxies', '[0m Generates proxy classes for entity classes.'],
                ['[32m doctrine:generate:repositories', '[0m Generate repository classes from your mapping information.'],
                ['[32m doctrine:entities:info', '[0m Show basic information about all mapped entities.'],
                ['[32m doctrine:mapping:describe ', '[0m Display information about mapped objects.'],
                ['[32m doctrine:run-dql', '[0m Executes arbitrary DQL directly from the command line.'],

                ['[32m doctrine:schema:create', '[0m Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.'],

                ['[32m doctrine:schema:drop', '[0m Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output.'],

                ['[32m doctrine:schema:update', '[0m Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata.'],

                ['[32m doctrine:validate-schema', '[0m Validate the mapping files.'],
            ];
            $climate->columns($data);
            break;
        default:
            $climate->error('---Error syntax...');
            $climate->out('$ bin/console to display the command list');
    };
}