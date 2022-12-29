<?php

namespace app\modules\module_page_profiles\ext;

use PDO;

class Plugins
{

    private
        $num_db = [];
    public
        $General,
        $Db,
        $Modules,
        $profile,
        $server_id,
        $Player,
        $Translate,
        $Status = [];

    public function __construct($General, $Db, $Modules, $profile, $server_id, $Player, $Translate)
    {
        define("PLUG", './app/modules/module_page_profiles/plugins/');

        $this->General = $General;
        $this->Db = $Db->db_data;
        $this->Modules = $Modules;
        $this->profile = $profile;
        $this->server_id = $server_id;
        $this->Player = $Player;
        $this->Translate = $Translate;

        $this->get_class();
    }

    # Search plugins | return ['name' => [Files]]
    private function search_plugins()
    {
        foreach (array_diff(scandir(PLUG, true), ['.', '..']) as $plug) {
            $pf[$plug] = array_diff(scandir(PLUG . $plug, true), ['.', '..']);
        }
        return  $pf;
    }

    # проверка плагнов на работа способность на ВСЕХ серверах | return ['server_id' => [plugins]]
    private function check_servers()
    {
        $Db = $this->Db;
        $load = [];

        foreach ($this->General->server_list as $key => $arr) :

            $load[$key] = [];

            $sname = $arr['name'];


            foreach ($this->search_plugins() as $name => $file) :

                $error = false;

                if (!in_array("config.php", $file))
                    break;

                $config = require PLUG  . $name . '/config.php';

                foreach ($config['db'] as $db) :
                    if (!in_array($db, $Db))
                        if (!in_array($sname, array_column($Db[$db], 'name'))) {
                            $error = true;
                            break;
                        }

                    $this->db_list($key, $db, array_search($sname, array_column($Db[$db], 'name')), $Db[$db][array_search($sname, array_column($Db[$db], 'name'))]['DB_num']);
                endforeach;

                if (!$error)
                    $load[$key][] = $name;

            endforeach;
        endforeach;

        return $load;
    }

    # Работа с кешем функции check_servers
    private function cache($reset = 0)
    {
        if (file_exists(MODULES . 'module_page_profiles/temp/cache.php') and $reset != 1) :
            return $this->Modules->get_module_cache('module_page_profiles');
        else :
            $this->Modules->set_module_cache('module_page_profiles', ['servers' => $this->check_servers(), 'db' => $this->num_db]);
            return $this->Modules->get_module_cache('module_page_profiles');
        endif;
    }

    private function db_list($server, $db, $num1, $num2)
    {
        $this->num_db[$server][$db] = [$num1, $num2];
    }

    # Сотировка плагинов для вывода | return [what => [number = plugins]]
    private function sorting()
    {
        $sorting = [
            'main' => [],
            'user' => [],
            'page' => []
        ];

        foreach ($this->cache()['servers'][$this->server_id] as $plug) :

            $config = require PLUG  . $plug . '/config.php';

            if (isset($config['main']) and file_exists(PLUG . $plug . '/main.php'))
                $sorting['main'][$config['main']] = $plug;

            if (isset($config['user']) and file_exists(PLUG . $plug . '/user.php'))
                $sorting['user'][$config['user']] = $plug;

            if (isset($config['page']) and file_exists(PLUG . $plug . '/page.php')) {
                $sorting['page'][$config['page']]['name'] = $config['page_name'];
                $sorting['page'][$config['page']]['link'] = $config['page_link'];
            }


        endforeach;

        return $sorting;
    }

    private function get_class()
    {
        foreach ($this->cache()['servers'][$this->server_id] as $class) {
            if (file_exists(PLUG . $class . '/class.php')) :
                include PLUG . $class . '/class.php';
                (method_exists($class, 'status')) ? $this->set_status((new $class)->status()) : (new $class);
            endif;
        }
    }


    public function get_content($what, $page = false)
    {
        $General = $this->General;
        $Db = $this->Db;
        $Modules = $this->Modules;
        $profile = $this->profile;
        $server_id = $this->server_id;
        $Player = $this->Player;
        $Translate = $this->Translate;

        if ($page != false) {
            include PLUG . $page . '/' . $what . '.php';
        } else {
            foreach ($this->sorting()[$what] as $f) {

                include PLUG . $f . '/' . $what . '.php';
            }
        }
    }

    public function plugins_css()
    {
        $list = [];

        foreach ($this->cache()['servers'][$this->server_id] as $css) {
            if (in_array('style.css', $this->search_plugins()[$css]))
                $list[] = $css;
        }

        return json_encode($list);
    }

    public function plugins_js()
    {
        $list = [];
        foreach ($this->cache()['servers'][$this->server_id] as $js) {
            if (in_array('script.js', $this->search_plugins()[$js]))
                $list[] = $js;
        }
        return $list;
    }


    public function get_page_list()
    {

        return $this->sorting()['page'];
    }


    // Status
    private function set_status($status)
    {
        $this->Status[] = $status;
    }

    public function get_status()
    {
        return $this->Status;
    }
}
