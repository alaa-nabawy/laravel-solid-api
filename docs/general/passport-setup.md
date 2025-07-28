# Laravel Passport Setup and Migration Conflict Resolution

This document explains how to properly set up Laravel Passport in this project and resolve migration conflicts that occur during Docker builds.

## The Problem

When installing Laravel Passport, the following migration conflict occurs:

```
SQLSTATE[HY000]: General error: 1 table "oauth_auth_codes" already exists
```

This happens because:

1. **Existing Migrations**: The project already contains Passport migration files (dated 2025-07-28)
2. **Duplicate Publishing**: When `php artisan passport:install` runs, it publishes new migration files with newer timestamps
3. **Table Conflict**: The new migrations try to create tables that already exist from the previous migrations

## The Solution

### 1. Custom Migration Management

We've implemented a custom approach to handle Passport migrations:

- **Existing Migrations**: Keep the current migration files (2025_07_28_100934 to 2025_07_28_100938)
- **Prevent Duplicates**: Use scripts to detect and prevent duplicate migration publishing
- **Smart Setup**: Use conditional logic to only run migrations when tables don't exist

### 2. Setup Scripts

#### Primary Setup Script: `scripts/setup-passport.sh`

This script handles the complete Passport setup:

```bash
# Run the setup script
./scripts/setup-passport.sh
```

**What it does:**

- ✅ Creates necessary storage directories
- ✅ Checks for existing migrations before publishing
- ✅ Runs migrations only if tables don't exist
- ✅ Generates encryption keys safely
- ✅ Creates OAuth clients with error handling
- ✅ Works both inside Docker and on host system

#### Migration Conflict Fix: `scripts/fix-passport-migrations.sh`

This script specifically handles migration conflicts:

```bash
# Fix migration conflicts
./scripts/fix-passport-migrations.sh
```

**What it does:**

- 🔧 Removes duplicate migration files
- 📁 Preserves existing migrations
- 🔐 Sets up oauth-keys directory
- 💡 Provides next steps guidance

### 3. AppServiceProvider Configuration

The `AppServiceProvider` is configured to:

```php
public function boot(): void
{
    // Configure Passport to use custom key storage location
    Passport::loadKeysFrom(storage_path('oauth-keys'));

    // Set custom token expiration times
    Passport::tokensExpireIn(now()->addDays(15));
    Passport::refreshTokensExpireIn(now()->addDays(30));
    Passport::personalAccessTokensExpireIn(now()->addMonths(6));
}
```

### 4. Security Considerations

#### .gitignore Updates

The following entries have been added to `.gitignore`:

```
# OAuth Keys
/storage/oauth-keys/
oauth-*.key
```

This prevents committing sensitive encryption keys to version control.

## Usage Instructions

### Automatic Setup (Recommended)

The Passport setup is now integrated into the project's build process:

```bash
# For development environment (includes Passport setup)
make setup

# For production environment (includes Passport setup)
make setup-prod

#### Manual Passport setup only
make passport-setup
```

### CI/CD Integration

The setup script is automatically used in:

- **GitHub Actions CI**: Replaces `php artisan passport:install --force`
- **Development Setup**: Integrated into `make setup`
- **Production Deployment**: Integrated into `make setup-prod`

This ensures consistent, conflict-free Passport installation across all environments.

### Manual Setup

#### For Docker Development

1. **Build containers** (if not already built):

    ```bash
    docker-compose -f compose.dev.yaml up -d
    ```

2. **Run the setup script**:

    ```bash
    docker-compose -f compose.dev.yaml exec workspace ./scripts/setup-passport.sh
    ```

3. **Verify setup**:
    ```bash
    docker-compose -f compose.dev.yaml exec workspace php artisan passport:client --list
    ```

#### For Host Development

1. **Run the setup script**:

    ```bash
    ./scripts/setup-passport.sh
    ```

2. **Verify setup**:
    ```bash
    php artisan passport:client --list
    ```

## Troubleshooting

### Issue: Migration Conflicts During Docker Build

**Symptoms:**

- `table "oauth_auth_codes" already exists` error
- Docker build fails during migration step

**Solution:**

```bash
# Clean up and restart
docker-compose -f compose.dev.yaml down -v
./scripts/fix-passport-migrations.sh
docker-compose -f compose.dev.yaml up -d
./scripts/setup-passport.sh
```

### Issue: Missing Encryption Keys

**Symptoms:**

- "Encryption key not found" errors
- OAuth endpoints return 500 errors

**Solution:**

```bash
# Regenerate keys
docker-compose -f compose.dev.yaml exec workspace php artisan passport:keys --force
```

### Issue: Missing OAuth Clients

**Symptoms:**

- "Client not found" errors
- Unable to generate tokens

**Solution:**

```bash
# Create clients manually
docker-compose -f compose.dev.yaml exec workspace php artisan passport:client --personal
docker-compose -f compose.dev.yaml exec workspace php artisan passport:client --password
```

## Best Practices

1. **Never commit encryption keys** - They're automatically ignored by `.gitignore`
2. **Use the setup scripts** - They handle edge cases and conflicts
3. **Test OAuth endpoints** - Verify setup with actual API calls
4. **Monitor token expiration** - Adjust expiration times in `AppServiceProvider` as needed
5. **Backup client secrets** - Store OAuth client secrets securely

## API Endpoints

After successful setup, the following OAuth endpoints will be available:

- `POST /oauth/token` - Get access token
- `GET /oauth/authorize` - Authorization endpoint
- `POST /oauth/token/refresh` - Refresh token
- `DELETE /oauth/tokens/{token-id}` - Revoke token

## Integration with Frontend

For SPA applications, consider using Laravel Sanctum instead of Passport for simpler token-based authentication. Passport is ideal for:

- Third-party API access
- OAuth2 server implementation
- Complex authentication flows
- Mobile app authentication

---

**Note**: This setup ensures that Passport works reliably in both development and production environments without migration conflicts.
