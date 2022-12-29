<?php

use app\modules\module_page_profiles\ext\Player,
    app\modules\module_page_profiles\ext\Plugins;

$Router->map('GET|POST', 'profiles/[:id]/', 'profiles');
$Router->map('GET|POST', 'profiles/[:id]/[i:sid]/', 'profiles');

$Map = $Router->match();

$server_id = $Map['params']['sid'] ?? 0;
$profile = $Map['params']['id'];
$search = intval($_GET['search'] ?? 0);

empty($Map) && get_iframe("404", "Похоже, URL введен хреново");
empty($profile) && get_iframe('009', 'Данная страница не существует');

// Создаём экземпляр класса с импортом подкласса Db и указанием Steam ID игрока.
$Player = new Player($General, $Db, $Modules, $profile, $server_id, $search);
$Plugins = new Plugins($General, $Db, $Modules, $profile, $server_id, $Player, $Translate);
