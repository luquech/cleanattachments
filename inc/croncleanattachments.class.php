<?php
class PluginCleanattachmentsCroncleanattachments extends CommonDBTM {

    static function cronInfo($name) {
        switch ($name) {
            case 'cleanAttachments':
                return [
                    'description' => __('Remove anexos e imagens de tickets conforme regras configuradas')
                ];
        }
        return [];
    }

    static function cronCleanAttachments($task) {
        global $DB;

        $cron_status = 0;
        $deleteMode = 'orphan_only';

        // Limite de documentos processados por execução (evita timeout)
        $maxDocsPerRun = 100;
        $processedDocs = 0;
        $stopProcessing = false;

        $rules = $DB->request([
            'FROM'  => 'glpi_plugin_cleanattachments_config',
            'WHERE' => ['is_active' => 1]
        ]);

        foreach ($rules as $rule) {
            if ($stopProcessing) {
                break;
            }

            $status          = $rule['ticket_status'];
            $entity          = $rule['entities_id'];
            $category        = $rule['itilcategories_id'];
            $value           = max(0, (int)$rule['interval_days']);
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

            // 1. Busca os tickets que atendem à regra
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
            $ticketData = [];
            foreach ($tickets as $t) {
                $ticketIds[] = $t['id'];
                $ticketData[$t['id']] = $t;
            }

            if (empty($ticketIds)) {
                $task->log(sprintf(__('Regra #%d: nenhum ticket encontrado.', 'cleanattachments'), $rule['id']));
                continue;
            }

            // 2. Busca documentos vinculados DIRETAMENTE ao ticket
            $docItemTable = 'glpi_documents_items';
            $directLinks = $DB->request([
                'FROM'  => $docItemTable,
                'WHERE' => [
                    'items_id' => $ticketIds,
                    'itemtype' => 'Ticket'
                ]
            ]);

            $documentIds = [];
            $ticketDocuments = [];
            foreach ($directLinks as $link) {
                $documentIds[] = $link['documents_id'];
                $ticketDocuments[$link['items_id']][$link['documents_id']] = true;
            }

            // 3. Acompanhamentos
            $followups = $DB->request([
                'FROM'  => 'glpi_itilfollowups',
                'WHERE' => [
                    'items_id' => $ticketIds,
                    'itemtype' => 'Ticket'
                ]
            ]);
            $followupIds = [];
            $followupTicketMap = [];
            foreach ($followups as $f) {
                $followupIds[] = $f['id'];
                $followupTicketMap[$f['id']] = $f['items_id'];
            }

            if (!empty($followupIds)) {
                $followupLinks = $DB->request([
                    'FROM'  => $docItemTable,
                    'WHERE' => [
                        'items_id' => $followupIds,
                        'itemtype' => 'ITILFollowup'
                    ]
                ]);
                foreach ($followupLinks as $link) {
                    $documentIds[] = $link['documents_id'];
                    $ticketId = $followupTicketMap[$link['items_id']] ?? null;
                    if ($ticketId) {
                        $ticketDocuments[$ticketId][$link['documents_id']] = true;
                    }
                }
            }

            // 4. Tarefas
            $tasks = $DB->request([
                'FROM'  => 'glpi_tickettasks',
                'WHERE' => [
                    'tickets_id' => $ticketIds
                ]
            ]);
            $taskIds = [];
            $taskTicketMap = [];
            foreach ($tasks as $t) {
                $taskIds[] = $t['id'];
                $taskTicketMap[$t['id']] = $t['tickets_id'];
            }

            if (!empty($taskIds)) {
                $taskLinks = $DB->request([
                    'FROM'  => $docItemTable,
                    'WHERE' => [
                        'items_id' => $taskIds,
                        'itemtype' => 'TicketTask'
                    ]
                ]);
                foreach ($taskLinks as $link) {
                    $documentIds[] = $link['documents_id'];
                    $ticketId = $taskTicketMap[$link['items_id']] ?? null;
                    if ($ticketId) {
                        $ticketDocuments[$ticketId][$link['documents_id']] = true;
                    }
                }
            }

            // 5. Soluções
            $solutions = $DB->request([
                'FROM'  => 'glpi_itilsolutions',
                'WHERE' => [
                    'items_id' => $ticketIds,
                    'itemtype' => 'Ticket'
                ]
            ]);
            $solutionIds = [];
            $solutionTicketMap = [];
            foreach ($solutions as $s) {
                $solutionIds[] = $s['id'];
                $solutionTicketMap[$s['id']] = $s['items_id'];
            }

            if (!empty($solutionIds)) {
                $solutionLinks = $DB->request([
                    'FROM'  => $docItemTable,
                    'WHERE' => [
                        'items_id' => $solutionIds,
                        'itemtype' => 'ITILSolution'
                    ]
                ]);
                foreach ($solutionLinks as $link) {
                    $documentIds[] = $link['documents_id'];
                    $ticketId = $solutionTicketMap[$link['items_id']] ?? null;
                    if ($ticketId) {
                        $ticketDocuments[$ticketId][$link['documents_id']] = true;
                    }
                }
            }

            $documentIds = array_unique($documentIds);

            if (empty($documentIds)) {
                $task->log(sprintf(__('Regra #%d: tickets sem anexos/imagens.', 'cleanattachments'), $rule['id']));
                continue;
            }

            // Busca informações dos documentos
            $docInfo = [];
            if (!empty($documentIds)) {
                $docIterator = $DB->request([
                    'FROM'  => 'glpi_documents',
                    'WHERE' => ['id' => $documentIds]
                ]);
                foreach ($docIterator as $doc) {
                    $docInfo[$doc['id']] = $doc;
                }
            }

            $task->log(sprintf(__('Regra #%d: processando %d documentos.', 'cleanattachments'), $rule['id'], count($documentIds)));

            $docsDeletedByTicket = [];
            
            foreach ($documentIds as $docId) {
                // Verifica limite de documentos
                if ($processedDocs >= $maxDocsPerRun) {
                    $stopProcessing = true;
                    break;
                }

                $docName = $docInfo[$docId]['name'] ?? "Documento #$docId";
                $deleted = false;

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
                        $deleted = true;
                        $task->log(sprintf(__('Documento "%s" (ID %d) excluído completamente.', 'cleanattachments'), $docName, $docId));
                    }
                } else {
                    $allRelatedItemIds = array_merge(
                        array_map(function($id) { return ['id' => $id, 'type' => 'Ticket']; }, $ticketIds),
                        array_map(function($id) { return ['id' => $id, 'type' => 'ITILFollowup']; }, $followupIds),
                        array_map(function($id) { return ['id' => $id, 'type' => 'TicketTask']; }, $taskIds),
                        array_map(function($id) { return ['id' => $id, 'type' => 'ITILSolution']; }, $solutionIds)
                    );

                    foreach ($allRelatedItemIds as $item) {
                        $linksToRemove = $DB->request([
                            'FROM'  => $docItemTable,
                            'WHERE' => [
                                'documents_id' => $docId,
                                'items_id'     => $item['id'],
                                'itemtype'     => $item['type']
                            ]
                        ]);
                        foreach ($linksToRemove as $link) {
                            $docItem = new Document_Item();
                            $docItem->delete(['id' => $link['id']], true);
                        }
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
                            $deleted = true;
                            $task->log(sprintf(__('Documento "%s" (ID %d) excluído (ficou órfão).', 'cleanattachments'), $docName, $docId));
                        }
                    } else {
                        $task->log(sprintf(__('Documento "%s" (ID %d) mantido – ainda possui %d vínculo(s).', 'cleanattachments'), $docName, $docId, $remaining));
                    }
                }

                if ($deleted) {
                    foreach ($ticketDocuments as $ticketId => $docs) {
                        if (isset($docs[$docId])) {
                            $docsDeletedByTicket[$ticketId][] = $docName;
                        }
                    }
                }
            }

            // Adiciona acompanhamentos privados
            foreach ($docsDeletedByTicket as $ticketId => $deletedDocNames) {
                if (!empty($deletedDocNames)) {
                    $followup = new ITILFollowup();
                    $followupInput = [
                        'items_id'  => $ticketId,
                        'itemtype'  => 'Ticket',
                        'content'   => sprintf(
                            __('🧹 [Limpeza Automática] Em %s, o plugin Clean Attachments removeu os seguintes anexos/imagens deste chamado (por regra de limpeza):\n\n%s\n\nEsta ação foi executada automaticamente e não altera o status do chamado.', 'cleanattachments'),
                            date('d/m/Y H:i:s'),
                            '• ' . implode("\n• ", $deletedDocNames)
                        ),
                        'is_private'=> 1,
                        'users_id'  => 0,
                    ];
                    $followup->add($followupInput);
                    $task->log(sprintf(__('Ticket #%d: acompanhamento privado adicionado.', 'cleanattachments'), $ticketId));
                }
            }

            if ($stopProcessing) {
                break;
            }
        }

        if ($processedDocs > 0) {
            $cron_status = 1;
            $task->addVolume($processedDocs);
            $task->log(sprintf(__('Processados %d documentos nesta execução.', 'cleanattachments'), $processedDocs));
        }

        return $cron_status;
    }
}
