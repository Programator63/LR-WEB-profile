<?php

namespace app\modules\module_page_profiles\ext;

class Plugins
{
    private
        $cache;

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

        $this->server();
        $this->get_class();
    }

    public function server()
    {
        global $dbid;
        $dbid = [];
        foreach ($this->General->server_list as $key => $value) :
            if (!empty($this->General->server_list[$key]['server_stats']) and $this->General->server_list[$key]['server_stats'] == $this->Player->found[$this->server_id]['DB_mod'] . ";" . $this->Player->found[$this->server_id]['USER_ID'] . ";" . $this->Player->found[$this->server_id]['DB'] . ";" . $this->Player->found[$this->server_id]['Table']) :
                $dbid['ip'] = explode(":", $this->General->server_list[$key]['ip']);
                $dbid['vip'] = explode(";", $this->General->server_list[$key]['server_vip']);
                $dbid['vip_id'] = $this->General->server_list[$key]['server_vip_id'];
                $dbid['server_sb'] = explode(";", $this->General->server_list[$key]['server_sb']);
                $dbid['shop'] = explode(";", $this->General->server_list[$key]['server_shop']);
                $dbid['warnsystem'] = explode(";", $this->General->server_list[$key]['server_warnsystem']);
            endif;
        endforeach;
    }

    # Search plugins | return ['name' => [Files]]
    private function search_plugins()
    {
        foreach (array_diff(scandir(PLUG, true), ['.', '..', 'disable']) as $plug) {
            $pf[$plug] = array_diff(scandir(PLUG . $plug, true), ['.', '..']);
        }
        return  $pf;
    }

    # Работа с кешем функции check_servers
    private function cache()
    {
        if (file_exists(MODULES . 'module_page_profiles/temp/plugins.php')) :
            return require MODULES . 'module_page_profiles/temp/plugins.php';
        else :
            file_put_contents(MODULES . 'module_page_profiles/temp/plugins.php', '<?php return ' . var_export_opt($this->search_plugins(), true) . ";");
            return $this->Modules->get_module_cache('module_page_profiles');
        endif;
    }

    private function get_class()
    {
        foreach ($this->cache() as $class => $value) {
            if (file_exists(PLUG . $class . '/class.php')) :
                include PLUG . $class . '/class.php';
                (method_exists($class, 'status')) ? $this->set_status((new $class)->status()) : (new $class);
            endif;
        }
    }


    # Сотировка плагинов для вывода | return [what => [number = plugins]]
    private function sorting()
    {
        $sorting = [
            'main' => [],
            'user' => [],
            'page' => []
        ];

        foreach ($this->cache() as $plug => $value) :

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

    # Ну тут все понятно что это делает(надеюсь)
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

        foreach ($this->cache() as $css => $value) {
            if (in_array('style.css', $this->search_plugins()[$css]))
                $list[] = $css;
        }

        return json_encode($list);
    }

    public function plugins_js()
    {
        $list = [];
        foreach ($this->cache() as $js => $value) {
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
