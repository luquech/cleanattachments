sudo tee /var/www/glpi/plugins/cleanattachments/README.md > /dev/null << 'EOF'
# Clean Attachments - Plugin para GLPI 11

[![License](https://img.shields.io/badge/License-GPLv2+-blue.svg)](LICENSE)
[![GLPI Version](https://img.shields.io/badge/GLPI-11.0+-green.svg)](https://glpi-project.org)

Plugin para GLPI 11 que remove automaticamente anexos e imagens de tickets conforme regras configuráveis, ideal para exclusão de documentos sensíveis (RG, fotos, comprovantes) após um período definido.

## 🚀 Funcionalidades

- ✅ **Múltiplas regras de limpeza** configuráveis por entidade, categoria ITIL e status do ticket
- ✅ **Suporte a dias ou minutos** como unidade de tempo
- ✅ **Remove anexos diretos** (aba "Documentos" do ticket)
- ✅ **Remove imagens inseridas no corpo das respostas** (acompanhamentos, tarefas, soluções)
- ✅ **Modo de exclusão segura**: remove apenas documentos que ficam órfãos (sem outros vínculos)
- ✅ **Acompanhamento privado** registrado no ticket após a limpeza (visível apenas para técnicos)
- ✅ **Não altera o status** do chamado e **não dispara notificações** ao usuário
- ✅ **Interface administrativa** para gerenciar regras (adicionar, editar, excluir)
- ✅ **Tarefa automática** (cron) integrada ao GLPI

## 📋 Requisitos

- GLPI 11.0 ou superior
- PHP 8.0 ou superior
- MySQL/MariaDB

## 🔧 Instalação

1. Copie a pasta `cleanattachments` para o diretório `plugins/` do GLPI:
   ```bash
   cp -r cleanattachments /var/www/glpi/plugins/
