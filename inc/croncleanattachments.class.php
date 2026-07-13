<?php
class PluginCleanattachmentsCroncleanattachments extends CommonDBTM {

    static function cronInfo($name) {
        switch ($name) {
            case 'cleanAttachments':
                return [
                    'description' => __('Remove anexos conforme regras configuradas (dias ou minutos)')
                ];
        }
        return [];
    }

    static function cronCleanAttachments($task) {
        global $DB;

        $cron_status = 0;
        $deleteMode = 'orphan_only';

        $rules = $DB->request([
            'FROM'  => 'glpi_plugin_cleanattachments_config',
            'WHERE' => ['is_active' => 1]
        ]);

        $processedDocs = 0;

        foreach ($rules as $rule) {
            $status          = $rule['ticket_status'];
            $entity          = $rule['entities_id'];
            $category        = $rule['itilcategories_id'];
            $value           = (int)$rule['interval_days'];
            $unit            = $rule['interval_unit'] ?? 'days';

            if ($unit === 'minutes') {
                $threshold = date('Y-m-d H:i:s', time() - $value * 60);
            } else {
                $threshold = date('Y-m-d H:i:s', time() - $value * 86400);
            }

            if ($status == Ticket::SOLVED) {
                $dateField = 'solvedate';
            } elseif ($status == Ticket::CLOSED) {
                $dateField = 'closedate';
            } else {
                $dateField = 'date_mod';
            }

            $tickets = $DB->request([
                'FROM'  => 'glpi_tickets',
                'WHERE' => [
                    'entities_id'       => $entity,
                    'itilcategories_id' => $category,
                    'status'            => $status,
                    $dateField          => ['<', $threshold]
                ]
            ]);

            $ticketIds = [];
            foreach ($tickets as $t) {
                $ticketIds[] = $t['id'];
            }

            if (empty($ticketIds)) {
                $task->log(sprintf(__('Regra #%d: nenhum ticket encontrado.', 'cleanattachments'), $rule['id']));
                continue;
            }

            $docItemTable = 'glpi_documents_items';
            $links = $DB->request([
                'FROM'  => $docItemTable,
                'WHERE' => [
                    'items_id' => $ticketIds,
                    'itemtype' => 'Ticket'
                ]
            ]);

            $documentIds = [];
            foreach ($links as $link) {
                $documentIds[] = $link['documents_id'];
            }

            if (empty($documentIds)) {
                $task->log(sprintf(__('Regra #%d: tickets sem anexos.', 'cleanattachments'), $rule['id']));
                continue;
            }

            $uniqueDocIds = array_unique($documentIds);

            foreach ($uniqueDocIds as $docId) {
                if ($deleteMode === 'total') {
                    $allLinks = $DB->request([
                        'FROM'  => $docItemTable,
                        'WHERE' => ['documents_id' => $docId]
                    ]);
                    foreach ($allLinks as $link) {
                        $docItem = new Document_Item();
                        $docItem->delete(['id' => $link['id']], true);
                    }
                    $document = new Document();
                    if ($document->delete(['id' => $docId], true)) {
                        $processedDocs++;
                        $task->log(sprintf(__('Documento ID %d excluído completamente.', 'cleanattachments'), $docId));
                    } else {
                        $task->log(sprintf(__('Falha ao excluir documento ID %d.', 'cleanattachments'), $docId));
                    }
                } else {
                    $linksToRemove = $DB->request([
                        'FROM'  => $docItemTable,
                        'WHERE' => [
                            'documents_id' => $docId,
                            'items_id'     => $ticketIds,
                            'itemtype'     => 'Ticket'
                        ]
                    ]);
                    foreach ($linksToRemove as $link) {
                        $docItem = new Document_Item();
                        $docItem->delete(['id' => $link['id']], true);
                    }

                    $remaining = $DB->request([
                        'COUNT'  => 'cpt',
                        'FROM'   => $docItemTable,
                        'WHERE'  => ['documents_id' => $docId]
                    ])->current()['cpt'] ?? 0;

                    if ($remaining == 0) {
                        $document = new Document();
                        if ($document->delete(['id' => $docId], true)) {
                            $processedDocs++;
                            $task->log(sprintf(__('Documento ID %d excluído (órfão).', 'cleanattachments'), $docId));
                        } else {
                            $task->log(sprintf(__('Falha ao excluir documento órfão ID %d.', 'cleanattachments'), $docId));
                        }
                    } else {
                        $task->log(sprintf(__('Documento ID %d mantido – ainda possui vínculos.', 'cleanattachments'), $docId));
                    }
                }
            }
        }

        if ($processedDocs > 0) {
            $cron_status = 1;
            $task->addVolume($processedDocs);
        }

        return $cron_status;
    }
}
