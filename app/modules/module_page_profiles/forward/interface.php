<?php if (sizeof($Player->found) > 1) : ?>
    <div class="col-md-12 profile">
        <div class="servers">
            <?php foreach ($Player->found as $server) { ?>
                <a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam32to64($Player->get_steam_32()) ?>/<?= $server['server_group'] ?>" class="servers_link">
                    <?= $server['name_servers'] ?>
                </a>
            <?php } ?>

        </div>
    </div>
<?php endif; ?>
<div class="col-md-12 profile">
    <div class="row">
        <div class="user card">
            <div class="user_info">
                <a href="https://steamcommunity.com/profiles/<?= $Player->get_steam_64() ?>" target="_blank">
                    <img src="<?= $General->getAvatar($Player->get_steam_64(), 1) ?>" alt="<?= $General->checkName($Player->get_steam_64()) ?>">
                </a>
                <div>
                    <span><?= $General->checkName($Player->get_steam_64()) ?></span>
                    <div class="info_stats">
                        <?php foreach ($Plugins->get_status() as $status) :
                            echo $status;
                        endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="user_stats">
                <div class="stats copy">
                    <? $General->get_icon('zmdi', 'steam') ?>
                    <div>
                        <span id="copy"><?= $Player->get_steam_32() ?></span>
                        <span>STEAM ID</span>
                    </div>
                </div>

                <?= $Plugins->get_content('user') ?>
            </div>
        </div>
        <div class="col main">
            <?php if (!count($Plugins->get_page_list()) == 0) : ?>
                <div class="pages card">
                    <div class="row">

                        <a href="/profiles/<?= con_steam32to64($Player->get_steam_32()) ?>/<?= $server_id ?>" class="link col">
                            Основная статистика
                        </a>

                        <?php foreach ($Plugins->get_page_list() as $page) : ?>

                            <a href="/profiles/<?= con_steam32to64($Player->get_steam_32()) ?>/<?= $server_id ?>/<?= $page['link'] ?>" class="link col">
                                <?= $page['name'] ?>
                            </a>

                        <?php endforeach; ?>
                    </div>

                </div>
            <?php endif; ?>

            <div class="statistic">

                <?= $Plugins->get_content('main') ?>


                <pre>
                          <?= var_dump($GLOBALS['dbid']) ?>  
                </pre>

            </div>

        </div>
    </div>
</div>

<!--- подключение стилей -->
<script>
    window.onload = function() {
        let css = <?= $Plugins->plugins_css() ?>;
        for (var i = 0; i <= css.length; i++) {
            if (css[i] != undefined)
                $("<link>", {
                    rel: 'stylesheet',
                    type: 'text/css',
                    href: `/app/modules/module_page_profiles/plugins/${css[i]}/style.css`
                }).appendTo('head')
        }
    }
</script>

<!--- подключение скриптов) -->
<?php foreach ($Plugins->plugins_js() as $js) : ?>

    <script src="/app/modules/module_page_profiles/plugins/<?= $js ?>/script.js"></script>

<?php endforeach; ?>