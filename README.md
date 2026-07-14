<h1 align="center">🧹 Clean Attachments</h1>
<p align="center"><strong>Plugin para GLPI 11</strong></p>
<p align="center">Remove automaticamente anexos e imagens de tickets com base em regras configuráveis.</p>

<hr>

<h2>📋 Sumário</h2>
<ul>
    <li><a href="#descricao">Descrição</a></li>
    <li><a href="#funcionalidades">Funcionalidades</a></li>
    <li><a href="#requisitos">Requisitos</a></li>
    <li><a href="#instalacao">Instalação</a></li>
    <li><a href="#configuracao">Configuração</a></li>
    <li><a href="#uso">Uso</a></li>
    <li><a href="#estrutura">Estrutura de Arquivos</a></li>
    <li><a href="#banco-de-dados">Banco de Dados</a></li>
    <li><a href="#logs">Logs</a></li>
    <li><a href="#seguranca">Segurança</a></li>
    <li><a href="#licenca">Licença</a></li>
    <li><a href="#autor">Autor</a></li>
</ul>

<hr>

<h2 id="descricao">📝 Descrição</h2>
<p>O <strong>Clean Attachments</strong> é um plugin para GLPI 11 que permite configurar regras automáticas para remoção de anexos e imagens de tickets, baseando-se em:</p>
<ul>
    <li><strong>Entidade</strong></li>
    <li><strong>Categoria ITIL</strong></li>
    <li><strong>Status do ticket</strong> (Solucionado ou Fechado)</li>
    <li><strong>Tempo decorrido</strong> desde a solução/fechamento</li>
</ul>
<p>O plugin é ideal para conformidade com LGPD/GDPR, removendo documentos sensíveis (como RG, comprovantes, fotos) que não precisam mais ser armazenados após o encerramento do chamado.</p>

<hr>

<h2 id="funcionalidades">✨ Funcionalidades</h2>
<ul>
    <li>✅ Múltiplas regras de limpeza configuráveis</li>
    <li>✅ Seleção por entidade, categoria ITIL e status</li>
    <li>✅ Tempo configurável em <strong>dias</strong> ou <strong>minutos</strong></li>
    <li>✅ Remove anexos diretos do ticket</li>
    <li>✅ Remove imagens inseridas no corpo das respostas (acompanhamentos)</li>
    <li>✅ Remove anexos de tarefas e soluções</li>
    <li>✅ Modo de exclusão: apenas documentos órfãos (preserva anexos compartilhados)</li>
    <li>✅ Adiciona acompanhamento privado no ticket informando a limpeza</li>
    <li>✅ Não altera o status do chamado</li>
    <li>✅ Não dispara notificações ao usuário</li>
    <li>✅ Tarefa automática integrada ao cron do GLPI</li>
</ul>

<hr>

<h2 id="requisitos">🔧 Requisitos</h2>
<ul>
    <li><strong>GLPI</strong>: versão 11.0 ou superior</li>
    <li><strong>PHP</strong>: 8.0 ou superior</li>
    <li><strong>MySQL/MariaDB</strong>: 5.7 ou superior</li>
    <li><strong>Permissão</strong>: acesso de superadmin para instalar e configurar</li>
</ul>

<hr>

<h2 id="instalacao">📦 Instalação</h2>

<h3>Método 1: Manual</h3>
<ol>
    <li>Baixe a última versão do plugin.</li>
    <li>Copie a pasta <code>cleanattachments</code> para o diretório <code>plugins/</code> do GLPI:</li>
</ol>
<pre><code>cp -r cleanattachments /var/www/glpi/plugins/</code></pre>
<ol start="3">
    <li>Ajuste as permissões:</li>
</ol>
<pre><code>sudo chown -R www-data:www-data /var/www/glpi/plugins/cleanattachments
sudo chmod -R 755 /var/www/glpi/plugins/cleanattachments</code></pre>
<ol start="4">
    <li>Acesse o GLPI como superadmin.</li>
    <li>Vá em <strong>Configurar &gt; Plugins</strong>.</li>
    <li>Localize <strong>Clean Attachments</strong> e clique em <strong>Instalar</strong>.</li>
</ol>

<h3>Método 2: Console (CLI)</h3>
<pre><code>php bin/console plugin:install cleanattachments</code></pre>

<hr>

<h2 id="configuracao">⚙️ Configuração</h2>

<h3>1. Ativar a tarefa automática</h3>
<ol>
    <li>Vá em <strong>Administração &gt; Tarefas automáticas</strong>.</li>
    <li>Encontre a tarefa <strong>"Limpar anexos conforme regras configuradas"</strong>.</li>
    <li>Marque como <strong>Ativo</strong> e defina o agendamento (recomendado: diário).</li>
</ol>

<h3>2. Criar regras de limpeza</h3>
<ol>
    <li>Vá em <strong>Configurar &gt; Plugins</strong> e clique no nome <strong>Clean Attachments</strong>.</li>
    <li>Clique em <strong>Nova regra</strong>.</li>
    <li>Preencha os campos:</li>
</ol>
<table border="1" cellpadding="5" cellspacing="0">
    <tr><th>Campo</th><th>Descrição</th></tr>
    <tr><td><strong>Entidade</strong></td><td>Entidade do GLPI (0 = todas)</td></tr>
    <tr><td><strong>Categoria ITIL</strong></td><td>Categoria dos tickets a serem processados</td></tr>
    <tr><td><strong>Status do ticket</strong></td><td>Solucionado ou Fechado</td></tr>
    <tr><td><strong>Tempo</strong></td><td>Número (dias ou minutos)</td></tr>
    <tr><td><strong>Unidade</strong></td><td>Dias ou Minutos</td></tr>
    <tr><td><strong>Ativo</strong></td><td>Sim/Não</td></tr>
</table>
<ol start="4">
    <li>Clique em <strong>Salvar</strong>.</li>
</ol>

<hr>

<h2 id="uso">🚀 Uso</h2>
<p>Após configurar as regras e ativar a tarefa automática:</p>
<ol>
    <li>Os tickets que atendem aos critérios serão processados automaticamente.</li>
    <li>Os anexos/imagens serão removidos (arquivo físico + registro no banco).</li>
    <li>Um acompanhamento privado será adicionado ao ticket informando a limpeza.</li>
</ol>
<p><strong>Importante:</strong> O plugin usa o modo <strong>"órfão"</strong> por padrão: só exclui o documento se ele não estiver mais vinculado a nenhum outro item do GLPI. Se o mesmo documento estiver anexado a outros chamados, ele será preservado.</p>

<hr>

<h2 id="estrutura">📁 Estrutura de Arquivos</h2>
<pre>
cleanattachments/
├── setup.php                         # Instalação/desinstalação e versão
├── hook.php                          # Registro da tarefa automática e criação da tabela
├── inc/
│   ├── config.class.php              # Classe da regra (CRUD)
│   └── croncleanattachments.class.php # Lógica da tarefa automática
├── front/
│   └── config.php                    # Interface de configuração
└── install/
    ├── install.sql                   # SQL para criação da tabela
    └── uninstall.sql                 # SQL para remoção da tabela
</pre>

<hr>

<h2 id="banco-de-dados">🗄️ Banco de Dados</h2>
<p>O plugin cria a tabela <code>glpi_plugin_cleanattachments_config</code> com a seguinte estrutura:</p>
<table border="1" cellpadding="5" cellspacing="0">
    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
    <tr><td><code>id</code></td><td>INT UNSIGNED AUTO_INCREMENT</td><td>Chave primária</td></tr>
    <tr><td><code>entities_id</code></td><td>INT UNSIGNED</td><td>ID da entidade</td></tr>
    <tr><td><code>itilcategories_id</code></td><td>INT UNSIGNED</td><td>ID da categoria ITIL</td></tr>
    <tr><td><code>ticket_status</code></td><td>INT</td><td>Status do ticket (5=Solucionado, 6=Fechado)</td></tr>
    <tr><td><code>interval_days</code></td><td>INT</td><td>Valor do tempo</td></tr>
    <tr><td><code>interval_unit</code></td><td>ENUM('days','minutes')</td><td>Unidade de tempo</td></tr>
    <tr><td><code>is_active</code></td><td>TINYINT(1)</td><td>Regra ativa (1) ou não (0)</td></tr>
</table>

<hr>

<h2 id="logs">📊 Logs</h2>
<p>O plugin registra logs no GLPI (visíveis em <strong>Administração &gt; Logs</strong>) e no arquivo <code>files/_log/cron.log</code>.</p>
<p>Exemplos de mensagens de log:</p>
<ul>
    <li><code>Regra #1: processando 5 documentos.</code></li>
    <li><code>Documento "foto_rg.png" (ID 2) excluído (ficou órfão).</code></li>
    <li><code>Ticket #4: acompanhamento privado adicionado.</code></li>
</ul>

<hr>

<h2 id="seguranca">🔒 Segurança e Conformidade</h2>
<ul>
    <li>✅ <strong>LGPD/GDPR</strong>: Remove dados pessoais sensíveis após o período necessário.</li>
    <li>✅ <strong>Acompanhamento privado</strong>: Apenas técnicos veem o registro da limpeza.</li>
    <li>✅ <strong>Sem notificações</strong>: O usuário não é notificado sobre a remoção.</li>
    <li>✅ <strong>Modo órfão</strong>: Documentos compartilhados com outros itens não são excluídos indevidamente.</li>
    <li>✅ <strong>Arquivo físico removido</strong>: O documento é excluído do servidor, não apenas do banco.</li>
</ul>

<hr>

<h2 id="solucao-problemas">🛠️ Solução de Problemas</h2>

<h3>Warning: "Usage of signed integers..."</h3>
<p>Este é um aviso <strong>cosmético</strong> do GLPI 11 durante a instalação. A tabela é criada corretamente com colunas <code>UNSIGNED</code>. O aviso pode ser ignorado com segurança e não afeta o funcionamento.</p>

<h3>Tarefa automática não executa</h3>
<ul>
    <li>Verifique se a tarefa está <strong>Ativa</strong> em Administração &gt; Tarefas automáticas.</li>
    <li>Verifique se há regras <strong>ativas</strong> cadastradas.</li>
    <li>Execute manualmente para testar.</li>
</ul>

<hr>

<h2 id="licenca">📄 Licença</h2>
<p>Este projeto está licenciado sob a licença <strong>GPLv2+</strong>.</p>
<p>Você pode:</p>
<ul>
    <li>✅ Usar livremente</li>
    <li>✅ Modificar</li>
    <li>✅ Distribuir</li>
</ul>
<p>Mantendo a mesma licença e atribuição ao autor original.</p>

<hr>

<h2 id="autor">👤 Autor</h2>
<p><strong>Anderson Lucas</strong></p>
<p>📧 E-mail: <em>(suporte@ufcg.edu.br)</em></p>
<p>🔗 GitHub: <em>(https://github.com/luquech)</em></p>

<hr>

<h2>🤝 Contribuições</h2>
<p>Contribuições são bem-vindas! Para contribuir:</p>
<ol>
    <li>Faça um fork do repositório</li>
    <li>Crie uma branch para sua funcionalidade (<code>git checkout -b feature/nova-funcionalidade</code>)</li>
    <li>Commit suas mudanças (<code>git commit -m 'Adiciona nova funcionalidade'</code>)</li>
    <li>Push para a branch (<code>git push origin feature/nova-funcionalidade</code>)</li>
    <li>Abra um Pull Request</li>
</ol>

<hr>

<h2>🐛 Reportar Bugs</h2>
<p>Encontrou um bug? Abra uma <strong>issue</strong> no GitHub com:</p>
<ul>
    <li>Versão do GLPI</li>
    <li>Versão do plugin</li>
    <li>Versão do PHP</li>
    <li>Descrição do problema</li>
    <li>Logs relevantes</li>
</ul>

<hr>

<p align="center">
    <strong>Desenvolvido com ❤️ para a comunidade GLPI</strong>
</p>
