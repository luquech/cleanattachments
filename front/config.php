<?php
include('../../../inc/includes.php');

Session::checkRight("config", UPDATE);

Html::header(
    PluginCleanattachmentsConfig::getTypeName(),
    $_SERVER['PHP_SELF'],
    "config",
    "plugins"
);

$plugin = new Plugin();
if (!$plugin->isActivated('cleanattachments')) {
    Html::displayNotFoundError();
    exit;
}

$config = new PluginCleanattachmentsConfig();

if (isset($_POST['save'])) {
    $input = [
        'id'                 => $_POST['id'] ?? 0,
        'entities_id'        => $_POST['entities_id'],
        'itilcategories_id'  => $_POST['itilcategories_id'],
        'ticket_status'      => $_POST['ticket_status'],
        'interval_days'      => $_POST['interval_days'],
        'interval_unit'      => $_POST['interval_unit'],   // <<< Campo essencial
        'is_active'          => $_POST['is_active']
    ];
    if ($input['id'] > 0) {
        $config->update($input);
    } else {
        unset($input['id']);
        $config->add($input);
    }
    Html::redirect(Plugin::getWebDir('cleanattachments') . '/front/config.php');
}

if (isset($_GET['delete'])) {
    $config->delete(['id' => $_GET['delete']]);
    Html::redirect(Plugin::getWebDir('cleanattachments') . '/front/config.php');
}

$config->showList();

echo "<hr>";

$id = $_GET['edit'] ?? 0;
if ($id > 0) {
    echo "<h2>".__('Editar regra')."</h2>";
} else {
    echo "<h2>".__('Nova regra')."</h2>";
}
$config->showForm($id);

Html::footer();
