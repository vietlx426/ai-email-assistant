# AI Email Assistant

A Laravel-based AI-powered email assistant desktop application built with NativePHP.

## Features

- **Email Drafting**: Generate professional email drafts from descriptions
- **Response Generator**: Create suggested replies to received emails  
- **Email Analyzer**: Check tone, professionalism, and clarity
- **Template Library**: AI-generated reusable templates
- **Email Summarizer**: Summarize long email threads

## Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Frontend**: NativePHP Electron
- **AI Provider**: DeepSeek (configurable for other providers)
- **Database**: SQLite
- **Cache**: Database/Redis

## Installation

### 1. Clone and Setup

```bash
git clone <repository-url>
cd ai-email-assistant
composer install
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure AI Provider

Edit `.env` file and add your API keys:

```env
# Required: Choose your AI provider
AI_PROVIDER=deepseek
AI_MOCK_MODE=false

# DeepSeek Configuration
DEEPSEEK_API_KEY=sk-your-actual-deepseek-key
DEEPSEEK_MODEL=deepseek-chat
```


### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 5. Run Application

**Development Mode:**
```bash
./scripts/dev.sh
# or
php artisan serve
```

**Desktop App:**
```bash
php artisan native:serve
```

## API Testing

Test your AI provider connection:
```bash
php artisan deepseek:test
```

## Security Best Practices

### Before Pushing to GitHub:

1. **Verify sensitive files are ignored:**
```bash
git check-ignore .env
git status | grep -E "\.(env|key|pem)$" | wc -l  # Should be 0
```

2. **Check for hardcoded secrets:**
```bash
grep -r "sk-\|api[_-]key" . --exclude-dir=vendor --exclude-dir=node_modules
```

3. **Use environment variables for all secrets:**
   - API keys
   - Database credentials  
   - Encryption keys
   - Third-party tokens

### Deployment Security:

- Use separate `.env` files for each environment
- Enable Laravel's encryption for sensitive data
- Set `APP_DEBUG=false` in production
- Use HTTPS for all API communications
- Monitor API usage and costs

## License

MIT License
