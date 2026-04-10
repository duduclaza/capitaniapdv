# Capitania PDV - Bar Management System

Capitania PDV é um sistema profissional para gestão de bares e restaurantes, desenvolvido com foco em performance, design moderno e facilidade de uso operacional.

## 🚀 Tecnologias
- **PHP 8.2+** (Arquitetura MVC customizada)
- **MariaDB / MySQL** (PDO)
- **Composer** (Gerenciamento de dependências)
- **Tailwind CSS** (Interface moderna e responsiva)
- **Stripe API** (Pagamentos via PIX QR Code e Sincronização de Catálogo)
- **JavaScript Puro** (Interações rápidas)

## 🛠️ Funcionalidades Principais
- **PDV (Ponto de Venda):** Operação rápida de balcão com busca instantânea.
- **Gestão de Comandas & Mesas:** Controle total de consumo por mesa com fechamento simplificado.
- **Integração Stripe:**
  - Sincronização automática de produtos e preços com a API do Stripe.
  - Pagamentos via PIX com geração automática de QR Code no PDV.
  - Webhooks para confirmação automática de pagamento e baixa no caixa.
- **Controle de Estoque:** Entrada, saída, ajuste, perda e alertas de estoque baixo.
- **Gestão Financeira:** Controle de caixa (abertura, sangria, suprimento e fechamento).
- **Relatórios:** Dashboards interativos, relatórios de vendas, produtos mais vendidos e exportação de dados.
- **Segurança:** Proteção CSRF, Autenticação de múltiplos níveis e Logs do sistema.

## 📦 Instalação

1.  **Clone o repositório:**
    ```bash
    git clone https://github.com/seu-usuario/capitaniapdv.git
    cd capitaniapdv
    ```

2.  **Instale as dependências:**
    ```bash
    composer install
    ```

3.  **Configure o ambiente:**
    Copie o arquivo `.env.example` para `.env` e preencha suas credenciais:
    ```bash
    cp .env.example .env
    ```

4.  **Configure o Banco de Dados:**
    Execute o script de instalação automática para criar as tabelas e popular dados iniciais:
    ```bash
    php database/install.php
    ```

5.  **Inicie o servidor:**
    ```bash
    php -S 0.0.0.0:8000 -t public
    ```

## 📖 Estrutura do Projeto
- `app/`: Lógica do sistema (Controllers, Models, Services, Middleware, Core).
- `bootstrap/`: Inicialização da aplicação.
- `config/`: Arquivos de configuração.
- `database/`: Scripts SQL, Migrações e Seeds.
- `public/`: Única pasta acessível via Web (Entry point, Assets).
- `resources/`: Views (Layouts, Partials, Pages).
- `routes/`: Definição de rotas HTTP.
- `storage/`: Logs e cache temporário.

## 💳 Configuração Stripe
Para habilitar o pagamento via PIX:
1.  Obtenha suas chaves (Live ou Test) no painel do Stripe.
2.  Preencha `STRIPE_SECRET_KEY` e `STRIPE_PUBLIC_KEY` no `.env`.
3.  Configure o Webhook no Stripe apontando para `https://seu-dominio.com.br/webhooks/stripe`.

---
Desenvolvido com ❤️ para a gestão eficiente do seu negócio.
