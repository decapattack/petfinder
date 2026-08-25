# 🐾 PetFinder

Plataforma comunitária para cadastro, busca e reencontro de pets perdidos. Os usuários podem registrar seus pets, emitir alertas geolocalizados (Radar 1 KM), gerenciar fichas de saúde e receber ajuda de vizinhos voluntários.

## Funcionalidades Principais

- **Cadastro e autenticação** via e-mail/senha ou OAuth (Google, X/Twitter, Microsoft).
- **Perfil com geolocalização** para ativar o Radar 1 KM.
- **Gestão de pets** com foto, espécie, raça, cor, condições especiais e UUID único.
- **QR Code de identificação** que aponta para uma página pública do pet.
- **Alerta SOS** que notifica usuários em um raio de 1 km quando um pet desaparece.
- **Gamificação** com pontos para heróis que ajudam no reencontro.
- **Aba de saúde** com cadastro de veterinário, fichas clínicas (upload de PDF/imagens) e lembretes de vacinas/remédios.
- **PWA** com manifest e service worker para instalação em dispositivos móveis.

## Stack Tecnológica

- **Backend:** Laravel 11 + Laravel Breeze + Laravel Socialite
- **Frontend:** Blade + Bootstrap 5 + Bootstrap Icons
- **QR Code:** `simplesoftwareio/simple-qrcode`
- **Banco de dados:** SQLite (padrão), configurável via `.env`

## Requisitos

- PHP >= 8.2
- Composer
- SQLite (padrão) ou MySQL/PostgreSQL

## Instalação

1. Clone o repositório e entre na pasta do projeto:
   ```bash
   cd petfinder
   ```

2. Instale as dependências:
   ```bash
   composer install
   ```

3. Copie o arquivo de ambiente e gere a chave da aplicação:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure o banco de dados no `.env`:
   ```env
   DB_CONNECTION=sqlite
   DB_DATABASE=database/database.sqlite
   ```
   Crie o arquivo do banco:
   ```bash
   touch database/database.sqlite
   ```

5. Execute as migrações:
   ```bash
   php artisan migrate
   ```

6. Crie o link simbólico para o storage:
   ```bash
   php artisan storage:link
   ```

7. Inicie o servidor:
   ```bash
   php artisan serve
   ```
   Acesse: http://localhost:8000

## Configuração OAuth (Opcional)

Para habilitar login social, configure as credenciais no `.env`:

```env
GOOGLE_CLIENT_ID=seu_client_id
GOOGLE_CLIENT_SECRET=seu_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

TWITTER_CLIENT_ID=seu_client_id
TWITTER_CLIENT_SECRET=seu_client_secret
TWITTER_REDIRECT_URI=http://localhost:8000/auth/twitter-oauth-2/callback

MICROSOFT_CLIENT_ID=seu_client_id
MICROSOFT_CLIENT_SECRET=seu_client_secret
MICROSOFT_REDIRECT_URI=http://localhost:8000/auth/microsoft/callback
```

> Provedores permitidos: `google`, `twitter-oauth-2`, `microsoft`.

## Testes

Execute os testes automatizados:

```bash
php artisan test
```

## Privacidade

- A página pública de cada pet é acessível via QR Code e UUID.
- O telefone do responsável só aparece na página pública quando:
  1. O pet está com status **desaparecido**;
  2. O responsável marcou a opção "Permitir que meu telefone apareça na página pública" no perfil.
- O nome do responsável é exibido apenas quando o pet está perdido, para facilitar o reencontro.
- A localização do usuário é utilizada apenas para calcular o raio de 1 KM no alerta SOS e não é compartilhada publicamente.

## Estrutura Relevante

- `app/Http/Controllers/PetController.php` — Cadastro e gestão de pets.
- `app/Http/Controllers/AlertController.php` — Emissão e resolução de alertas SOS.
- `app/Http/Controllers/HealthRecordController.php` — Fichas clínicas e upload de arquivos.
- `app/Http/Controllers/ScheduleController.php` — Lembretes de vacinas e remédios.
- `app/Http/Controllers/AuthController.php` — Login social via OAuth.
- `resources/views/` — Templates Blade.
- `database/migrations/` — Esquema do banco de dados.

## Licença

MIT
