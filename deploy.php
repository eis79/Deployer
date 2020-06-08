<?php
namespace Deployer;

require 'recipe/common.php';
require 'vendor/deployer/recipes/recipe/cachetool.php';

// Project name
set('application', 'marketing-api');
set('keep_releases', 5);
set('default_timeout', null);


// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', false);

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['runtime']);


set('cachetool', '/run/php/php7.3-fpm.sock');



// Hosts

host(getenv('SERVER_IP'))
    ->set('deploy_path', '/var/www/marketing-api-new')
    ->user('rzk')
    ->port(10022)
    ->multiplexing(true)
    ->addSshOption('StrictHostKeyChecking', 'no');
    
    
host('xx.xx.xx.xx', 'xx.xx.xx.xx')
    ->stage('tank')
    ->set('deploy_path', '/var/www/marketing-api-new')
    ->user('rzk')
    ->port(10022)
    ->multiplexing(true)
    ->addSshOption('StrictHostKeyChecking', 'no');

host('xx.xx.xx.xx', 'xx.xx.xx.xx')
    ->stage('preprod')
    ->set('deploy_path', '/var/www/marketing-api-new')
    ->user('rzk')
    ->port(10022)
    ->multiplexing(true)
    ->addSshOption('StrictHostKeyChecking', 'no');


host('xx.xx.xx.xx','xx.xx.xx.xx','xx.xx.xx.xx','xx.xx.xx.xx')
    ->stage('prod')
    ->set('deploy_path', '/var/www/marketing-api-new')
    ->user('rzk')
    ->port(10022)
    ->multiplexing(true)
    ->addSshOption('StrictHostKeyChecking', 'no');
    
    
task('build', function () {
     run('composer install');
})->local();

// Tasks
task('upload', function () {
    upload(__DIR__ . '/', '{{release_path}}');
})->desc('Environment setup');

task('deploy:migratedb', function () {
    //run('cd {{release_path}}&&sleep 600');
    run('{{bin/php}} {{release_path}}/yii migrate --interactive=0');
})->once();



task('release', [
    'deploy:prepare',
    'deploy:release',
    'upload',
    'deploy:shared',
    'deploy:writable',
    'deploy:migratedb',
]);

task('symlink', [
 
    'deploy:symlink',
]);


task('deploy', [
  'release',
  'symlink',
  'cleanup',
  'success'
]);

after('deploy:symlink', 'cachetool:clear:opcache');
after('rollback', 'cachetool:clear:opcache');

// [Optional] If deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
