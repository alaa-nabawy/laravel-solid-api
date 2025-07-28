# Pre-commit Hooks Setup

This document explains how to set up and use pre-commit hooks in this Laravel project to maintain code quality and consistency.

## Overview

Pre-commit hooks are scripts that run automatically before each commit to ensure code quality, style consistency, and catch potential issues early in the development process.

## Features

Our pre-commit configuration includes:

### PHP Quality Checks

- **Laravel Pint**: Automatic code style formatting
- **PHPStan**: Static analysis for bug detection
- **PHPUnit**: Automated test execution
- **Composer Security Audit**: Vulnerability scanning

### General Code Quality

- **Trailing whitespace removal**
- **End-of-file fixing**
- **YAML/JSON/XML validation**
- **Merge conflict detection**
- **Large file prevention**
- **Branch protection** (prevents commits to main/master)

### Infrastructure

- **Hadolint**: Dockerfile linting
- **ESLint**: JavaScript code quality
- **Prettier**: Code formatting
- **Markdown linting**
- **Secret detection**

## Quick Setup

### Automatic Setup (Recommended)

```bash
# Run the complete setup
make setup-pre-commit
```

This command will:

1. Install pre-commit if not already installed
2. Set up the development environment
3. Install all hooks
4. Create necessary configuration files
5. Run initial tests

### Manual Setup

1. **Install pre-commit**:

    ```bash
    # Via pip
    pip install pre-commit

    # Via Homebrew (macOS)
    brew install pre-commit

    # Via apt (Ubuntu/Debian)
    sudo apt-get install python3-pip
    pip3 install pre-commit
    ```

2. **Install hooks**:

    ```bash
    make install-pre-commit
    ```

3. **Test setup**:

    ```bash
    make test-pre-commit
    ```

## Usage

### Normal Development Workflow

Once set up, pre-commit hooks run automatically:

```bash
# Make your changes
vim app/Models/User.php

# Stage your changes
git add .

# Commit (hooks run automatically)
git commit -m "Add new user feature"
```

### Manual Hook Execution

```bash
# Run all hooks on all files
make pre-commit-run

# Run hooks only on staged files
make pre-commit-run-staged

# Run specific hook
pre-commit run laravel-pint
pre-commit run phpstan
```

### Available Make Commands

| Command                      | Description                |
| ---------------------------- | -------------------------- |
| `make setup-pre-commit`      | Complete setup process     |
| `make install-pre-commit`    | Install hooks only         |
| `make test-pre-commit`       | Test current setup         |
| `make update-pre-commit`     | Update hook versions       |
| `make pre-commit-run`        | Run all hooks on all files |
| `make pre-commit-run-staged` | Run hooks on staged files  |

## Hook Details

### PHP Hooks

#### Laravel Pint

- **Purpose**: Enforces Laravel coding standards
- **Auto-fix**: Yes
- **Files**: `*.php`
- **Manual run**: `pre-commit run laravel-pint`

#### PHPStan

- **Purpose**: Static analysis for type safety and bug detection
- **Auto-fix**: No
- **Files**: `*.php`
- **Configuration**: `phpstan.neon`
- **Manual run**: `pre-commit run phpstan`

#### PHPUnit

- **Purpose**: Runs unit and feature tests
- **Auto-fix**: No
- **Files**: `*.php`
- **Manual run**: `pre-commit run phpunit`

#### Composer Audit

- **Purpose**: Checks for security vulnerabilities
- **Auto-fix**: No
- **Files**: `composer.json`, `composer.lock`
- **Manual run**: `pre-commit run composer-audit`

### JavaScript Hooks

#### ESLint

- **Purpose**: JavaScript code quality and style
- **Auto-fix**: Yes (when possible)
- **Files**: `*.js`, `*.ts`, `*.vue`
- **Manual run**: `pre-commit run eslint`

#### Prettier

- **Purpose**: Code formatting
- **Auto-fix**: Yes
- **Files**: `*.js`, `*.ts`, `*.vue`, `*.css`, `*.scss`, `*.json`, `*.md`
- **Manual run**: `pre-commit run prettier`

## Troubleshooting

### Common Issues

#### Hook Fails Due to Docker

```bash
# Ensure Docker containers are running
make dev

# Wait for containers to be ready
docker-compose -f compose.dev.yaml ps
```

#### Permission Issues

```bash
# Make scripts executable
chmod +x scripts/*.sh
```

#### Slow Hook Execution

```bash
# Update to latest hook versions
make update-pre-commit

# Consider skipping heavy hooks for quick commits
SKIP=phpunit git commit -m "Quick fix"
```

### Bypassing Hooks

**⚠️ Use sparingly and only when necessary**

```bash
# Skip all hooks for one commit
git commit --no-verify -m "Emergency fix"

# Skip specific hook
SKIP=phpstan git commit -m "WIP: refactoring"

# Skip multiple hooks
SKIP=phpstan,phpunit git commit -m "Draft implementation"
```

### Hook Configuration

#### Modifying Hooks

Edit `.pre-commit-config.yaml` to:

- Add new hooks
- Modify existing hook behavior
- Change file patterns
- Adjust hook arguments

#### Updating Hook Versions

```bash
# Update all hooks to latest versions
make update-pre-commit

# Manual update
pre-commit autoupdate
```

## Integration with CI/CD

The same hooks run in GitHub Actions (see `.github/workflows/ci.yml`):

- **Consistency**: Same checks locally and in CI
- **Fast feedback**: Catch issues before pushing
- **Reduced CI failures**: Most issues caught locally

## Best Practices

### For Developers

1. **Run setup once**: `make setup-pre-commit`
2. **Commit frequently**: Smaller commits = faster hooks
3. **Fix issues promptly**: Don't bypass hooks regularly
4. **Update regularly**: `make update-pre-commit` monthly

### For Teams

1. **Standardize setup**: All team members should use pre-commit
2. **Document exceptions**: When and why to bypass hooks
3. **Review configuration**: Regularly update `.pre-commit-config.yaml`
4. **Monitor performance**: Optimize slow hooks

### Performance Tips

1. **Use file patterns**: Limit hooks to relevant files
2. **Cache dependencies**: Docker layer caching helps
3. **Parallel execution**: Most hooks run in parallel
4. **Skip in CI**: Use `SKIP` environment variable when needed

## Configuration Files

| File                          | Purpose                    |
| ----------------------------- | -------------------------- |
| `.pre-commit-config.yaml`     | Main hook configuration    |
| `.secrets.baseline`           | Known secrets to ignore    |
| `phpstan.neon`                | PHPStan configuration      |
| `pint.json`                   | Laravel Pint configuration |
| `scripts/setup-pre-commit.sh` | Setup automation           |

## Support

For issues or questions:

1. Check this documentation
2. Review `.pre-commit-config.yaml`
3. Run `make test-pre-commit` for diagnostics
4. Check [pre-commit documentation](https://pre-commit.com/)
5. Ask the team for help

## Contributing

When adding new hooks:

1. Test thoroughly
2. Document in this file
3. Add to `Makefile` if needed
4. Consider performance impact
5. Update setup script if required
