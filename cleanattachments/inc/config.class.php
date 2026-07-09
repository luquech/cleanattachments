<?php
class PluginCleanattachmentsConfig extends CommonDBTM {

    public static $rightname = 'config';

    static function getTable($classname = null) {
        return 'glpi_plugin_cleanattachments_config';
    }

    static function getTypeName($nb = 0) {
        return _n('Regra de limpeza', 'Regras de limpeza', $nb, 'cleanattachments');
    }

    function defineTabs($options = []) {
        $tabs = [];
        $this->addStandardTab('PluginCleanattachmentsConfig', $tabs, $options);
        return $tabs;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == 'PluginCleanattachmentsConfig') {
            return self::createTabEntry(self::getTypeName());
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == 'PluginCleanattachmentsConfig') {
            Html::redirect(Plugin::getWebDir('cleanattachments') . '/front/config.php');
        }
        return true;
    }

    function showForm($ID, $options = []) {
        if ($ID > 0) {
            $this->getFromDB($ID);
        } else {
            $this->fields = [
                'id'                 => 0,
                'entities_id'        => 0,
                'itilcategories_id'  => 0,
                'ticket_status'      => Ticket::SOLVED,
                'interval_days'      => 60,
                'interval_unit'      => 'days',
                'is_active'          => 1
            ];
        }

        echo "<form method='post' action='".Plugin::getWebDir('cleanattachments')."/front/config.php'>";
        echo "<input type='hidden' name='id' value='".$this->fields['id']."'>";
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr class='tab_bg_1'><td>".__('Entidade')."</td><td>";
        Dropdown::show('Entity', [
            'name'  => 'entities_id',
            'value' => $this->fields['entities_id']
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>".__('Categoria ITIL')."</td><td>";
        Dropdown::show('ITILCategory', [
            'name'  => 'itilcategories_id',
            'value' => $this->fields['itilcategories_id']
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>".__('Status do ticket')."</td><td>";
        $statuses = [
            Ticket::SOLVED => __('Solucionado'),
            Ticket::CLOSED => __('Fechado')
        ];
        Dropdown::showFromArray('ticket_status', $statuses, [
            'value' => $this->fields['ticket_status']
        ]);
        echo "</td></tr>";

        // Tempo
        echo "<tr class='tab_bg_1'><td>".__('Tempo')."</td><td>";
        echo "<input type='number' name='interval_days' value='".$this->fields['interval_days']."' min='0' step='1' required>";
        echo "</td></tr>";

        // Unidade (dias/minutos)
        echo "<tr class='tab_bg_1'><td>".__('Unidade')."</td><td>";
        Dropdown::showFromArray('interval_unit', [
            'days'    => __('Dias'),
            'minutes' => __('Minutos')
        ], [
            'value' => $this->fields['interval_unit'] ?? 'days'
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td>".__('Ativo')."</td><td>";
        Dropdown::showYesNo('is_active', $this->fields['is_active']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_2'><td colspan='2' class='center'>";
        echo "<input type='submit' name='save' value='".__('Salvar')."' class='submit'>";
        echo "</td></tr>";
        echo "</table>";
        Html::closeForm();
    }

    function showList() {
        global $DB;

        $iterator = $DB->request([
            'FROM'  => 'glpi_plugin_cleanattachments_config',
            'ORDER' => 'id ASC'
        ]);

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th>ID</th><th>".__('Entidade')."</th><th>".__('Categoria ITIL')."</th><th>".__('Status')."</th><th>".__('Tempo')."</th><th>".__('Unidade')."</th><th>".__('Ativo')."</th><th></th></tr>";

        foreach ($iterator as $row) {
            $entity = Dropdown::getDropdownName('glpi_entities', $row['entities_id']);
            $category = Dropdown::getDropdownName('glpi_itilcategories', $row['itilcategories_id']);
            $status = ($row['ticket_status'] == Ticket::SOLVED) ? __('Solucionado') : __('Fechado');
            $active = $row['is_active'] ? __('Sim') : __('Não');
            $unit   = ($row['interval_unit'] == 'minutes') ? __('Minutos') : __('Dias');

            echo "<tr class='tab_bg_1'>";
            echo "<td>".$row['id']."</td>";
            echo "<td>".$entity."</td>";
            echo "<td>".$category."</td>";
            echo "<td>".$status."</td>";
            echo "<td>".$row['interval_days']."</td>";
            echo "<td>".$unit."</td>";
            echo "<td>".$active."</td>";
            echo "<td class='center'>";
            echo "<a href='?edit=".$row['id']."'><i class='fas fa-edit'></i></a>";
            echo "&nbsp;";
            echo "<a href='?delete=".$row['id']."' onclick='return confirm(\"".__('Deseja realmente excluir?')."\")'><i class='fas fa-trash'></i></a>";
            echo "</td></tr>";
        }
        echo "</table>";
    }
}
