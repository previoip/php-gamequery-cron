# GameQuery CronJob

personal-dump repo; php cron job script for collecting dedicated servers information for analytical and server monitoring. 
PHP 7.4.20

## Pre-Setup

1. Edit `config.php` and `servers.json`
2. Run `create_db.php` to initiate database
3. setup a cronjob with `php cron.php`. see on how to setup a cron-job below.

## Setting up Cron-Job
#### Windows

[stackoverflow guide](https://stackoverflow.com/questions/7195503/setting-up-a-cron-job-in-windowslink)

1. Make sure you logged on as an administrator or you have the same access as an administrator.
2. Start->Control Panel->System and Security->Administrative Tools->Task Scheduler
3. Action->Create Basic Task->Type a name and Click Next
4. Follow through the wizard.

 
#### todo: Linux
todo: readme

note: tested on source dedicated servers only, my goal was for an self own personal factorio dedicated server, although i havent find any query protocol lib for php.