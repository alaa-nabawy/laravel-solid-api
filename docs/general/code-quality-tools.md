# Code Quality Tools Documentation

This document provides comprehensive documentation for the code quality tools and scripts available in this Laravel project.

## Overview

The project includes a powerful code quality enhancement script (`scripts/code-quality.sh`) that provides automated tools for code analysis, testing, security auditing, and quality improvements.

## 🛠️ Code Quality Script (`code-quality.sh`)

### Purpose

The `code-quality.sh` script is a comprehensive tool that automates various code quality checks and improvements for the Laravel project. It integrates multiple tools and provides a unified interface for maintaining high code standards.

### Location

```
scripts/code-quality.sh
```

### Prerequisites

- Docker and Docker Compose must be running
- Development environment should be set up (`make dev`)
- Must be run from the project root directory

## 📋 Available Commands

### Quick Reference

| Command        | Description                      | Usage                                    |
| -------------- | -------------------------------- | ---------------------------------------- |
| `install-deps` | Install development dependencies | `./scripts/code-quality.sh install-deps` |
| `phpstan`      | Run PHPStan static analysis      | `./scripts/code-quality.sh phpstan`      |
| `psalm`        | Run Psalm static analysis        | `./scripts/code-quality.sh psalm`        |
| `style`        | Fix code style with Laravel Pint | `./scripts/code-quality.sh style`        |
| `test`         | Run tests with coverage          | `./scripts/code-quality.sh test`         |
| `security`     | Run security audit               | `./scripts/code-quality.sh security`     |
| `optimize`     | Optimize application caches      | `./scripts/code-quality.sh optimize`     |
| `clear`        | Clear all caches                 | `./scripts/code-quality.sh clear`        |
| `ide-helpers`  | Generate IDE helper files        | `./scripts/code-quality.sh ide-helpers`  |
| `db-analyze`   | Analyze database structure       | `./scripts/code-quality.sh db-analyze`   |
| `full-check`   | Run complete quality check       | `./scripts/code-quality.sh full-check`   |
| `help`         | Show help message                | `./scripts/code-quality.sh help`         |

### Makefile Integration

The script is integrated with the project's Makefile for easier access:

```bash
# Direct Makefile commands
make code-quality        # Run all quality checks
make phpstan            # Static analysis
make code-style         # Fix code formatting
make test-coverage      # Run tests with coverage
make security-audit     # Security vulnerability scan
make ide-helpers        # Generate IDE helpers
make full-quality-check # Complete quality analysis
```

## 🔍 Detailed Command Documentation

### 1. Install Development Dependencies

```bash
./scripts/code-quality.sh install-deps
# or
make code-quality cmd="install-deps"
```

**Purpose**: Installs essential development tools via Composer

**Installed Tools**:

- PHPStan for static analysis
- Psalm for additional static analysis
- Rector for automated refactoring
- PHPUnit for testing
- Mockery for mocking
- Faker for test data generation
- Collision for better error reporting
- Laravel Ignition for debugging

**When to Use**:

- Initial project setup
- After adding new developers to the team
- When development tools are missing

### 2. PHPStan Static Analysis

```bash
./scripts/code-quality.sh phpstan
# or
make phpstan
```

**Purpose**: Performs static analysis to detect potential bugs and type issues

**Features**:

- Automatically creates `phpstan.neon` configuration if missing
- Analyzes `app/`, `config/`, `database/`, and `routes/` directories
- Uses level 5 analysis (good balance of strictness and practicality)
- Memory limit set to 1GB for large projects

**Configuration**:
The script creates a default `phpstan.neon` with:

- Analysis level 5
- Exclusion of Laravel kernel files
- Common Laravel-specific error ignoring

**Output**: Detailed report of potential issues with file locations and descriptions

### 3. Psalm Static Analysis

```bash
./scripts/code-quality.sh psalm
```

**Purpose**: Additional static analysis with different focus areas than PHPStan

**Features**:

- Automatically initializes Psalm configuration if missing
- Provides different perspective on code quality
- Excellent for type safety and potential null pointer issues

**When to Use**:

- Complement PHPStan analysis
- Focus on type safety
- Before major releases

### 4. Code Style Formatting

```bash
./scripts/code-quality.sh style
# or
make code-style
```

**Purpose**: Automatically fixes code style issues using Laravel Pint

**Features**:

- Follows Laravel coding standards
- Automatically fixes formatting issues
- Consistent code style across the project
- Uses configuration from `pint.json`

**What it Fixes**:

- Indentation and spacing
- Import organization
- Array syntax consistency
- PHPDoc formatting
- Method and property ordering

### 5. Test Coverage

```bash
./scripts/code-quality.sh test
# or
make test-coverage
```

**Purpose**: Runs the test suite with coverage reporting

**Features**:

- Executes all PHPUnit tests
- Generates coverage reports
- Enforces minimum 3% coverage (adjusted for early development phase)
- Uses Laravel's built-in test runner

**Requirements**:

- Properly configured `phpunit.xml`
- Test database setup
- Xdebug or PCOV for coverage

**Output**:

- Test results with pass/fail status
- Coverage percentage by file and overall
- HTML coverage report (if configured)

#### Code Coverage Configuration

**Current Minimum Coverage**: 3%

**Rationale**: The minimum coverage threshold has been set to 3% to accommodate the early development phase of the project. This allows the CI pipeline to pass while the codebase is still growing and comprehensive test coverage is being developed.

**Coverage Progression Plan**:

- **Phase 1 (Current)**: 3% minimum - Basic CI functionality
- **Phase 2 (Development)**: 25% minimum - Core functionality covered
- **Phase 3 (Pre-production)**: 50% minimum - Major features tested
- **Phase 4 (Production-ready)**: 80% minimum - Comprehensive coverage

**Configuration Locations**:

- CI Pipeline: `.github/workflows/ci.yml` (line 78)
- Quality Script: `scripts/code-quality.sh` (line 100)
- Local Development: `Makefile` test-coverage target

**Monitoring**: Current coverage can be viewed in:

- `coverage.txt` - Text summary
- `coverage.xml` - XML format for CI tools
- `coverage-html/` - Detailed HTML reports

### 6. Security Audit

```bash
./scripts/code-quality.sh security
# or
make security-audit
```

**Purpose**: Scans for security vulnerabilities and outdated packages

**Features**:

- Composer security audit for known vulnerabilities
- Checks for outdated direct dependencies
- Identifies potential security risks

**What it Checks**:

- Known security vulnerabilities in dependencies
- Outdated packages with security implications
- Direct dependency status

**Output**:

- List of vulnerable packages with severity
- Recommendations for updates
- Outdated package information

### 7. Application Optimization

```bash
./scripts/code-quality.sh optimize
```

**Purpose**: Optimizes the application for better performance

**Optimizations Applied**:

- Configuration caching (`config:cache`)
- Route caching (`route:cache`)
- View caching (`view:cache`)
- Event caching (`event:cache`)

**When to Use**:

- Before production deployment
- Performance testing
- After configuration changes

**Note**: Should be run in production environment or when testing production-like performance

### 8. Cache Clearing

```bash
./scripts/code-quality.sh clear
```

**Purpose**: Clears all application caches for fresh state

**Caches Cleared**:

- Configuration cache
- Route cache
- View cache
- Event cache
- Application cache

**When to Use**:

- Development environment refresh
- After major configuration changes
- Troubleshooting cache-related issues
- Before running tests

### 9. IDE Helper Generation

```bash
./scripts/code-quality.sh ide-helpers
# or
make ide-helpers
```

**Purpose**: Generates IDE helper files for better code completion and analysis

**Generated Files**:

- `_ide_helper.php` - General Laravel helpers
- `_ide_helper_models.php` - Model method hints
- `.phpstorm.meta.php` - PhpStorm-specific metadata

**Benefits**:

- Better IDE autocomplete
- Improved static analysis
- Enhanced developer experience
- Reduced development errors

### 10. Database Analysis

```bash
./scripts/code-quality.sh db-analyze
```

**Purpose**: Analyzes database structure and migration status

**Analysis Includes**:

- Migration status and pending migrations
- Database table structure
- Column information and types
- Schema analysis

**Output**:

- Migration status table
- Database schema information
- Table and column details

**When to Use**:

- Database troubleshooting
- Schema documentation
- Migration verification
- Database optimization planning

### 11. Full Quality Check

```bash
./scripts/code-quality.sh full-check
# or
make full-quality-check
```

**Purpose**: Runs a comprehensive quality check covering all aspects

**Process**:

1. Installs/updates development dependencies
2. Runs PHPStan static analysis
3. Applies code style fixes
4. Executes tests with coverage
5. Performs security audit
6. Generates IDE helpers

**Duration**: 5-15 minutes depending on project size

**When to Use**:

- Before major releases
- Weekly quality checks
- After significant code changes
- New developer onboarding
- CI/CD pipeline integration

## 🚀 Integration with Development Workflow

### Daily Development

```bash
# Start of day
make code-style          # Fix any style issues
make test               # Ensure tests pass

# During development
make phpstan            # Check for issues
make code-style         # Fix formatting

# Before committing (automated via pre-commit hooks)
make test-coverage      # Ensure tests pass with coverage
make security-audit     # Check for vulnerabilities
```

### Weekly Quality Checks

```bash
# Comprehensive weekly check
make full-quality-check

# Update IDE helpers
make ide-helpers

# Database health check
./scripts/code-quality.sh db-analyze
```

### Pre-Release Checklist

```bash
# Complete quality verification
make full-quality-check

# Performance optimization
./scripts/code-quality.sh optimize

# Final security check
make security-audit

# Generate fresh IDE helpers
make ide-helpers
```

## 🔧 Configuration Files

### PHPStan Configuration (`phpstan.neon`)

```neon
parameters:
    level: 5
    paths:
        - app
        - config
        - database
        - routes
    excludePaths:
        - app/Console/Kernel.php
        - app/Http/Kernel.php
    ignoreErrors:
        - '#Unsafe usage of new static#'
```

### Laravel Pint Configuration (`pint.json`)

See the existing `pint.json` file for detailed code style rules.

### PHPUnit Configuration (`phpunit.xml`)

Configured for PostgreSQL testing with coverage reporting.

## 🐳 Docker Integration

All commands run within Docker containers to ensure consistency:

- **Workspace Container**: Used for most commands
- **App Container**: Used for application-specific operations
- **Database Container**: Used for database analysis

**Benefits**:

- Consistent environment across team members
- Isolated dependencies
- No local tool installation required
- Version consistency

## 📊 Performance Considerations

### Optimization Tips

1. **Parallel Execution**: Run independent checks in parallel
2. **Selective Analysis**: Use specific commands instead of full-check for quick feedback
3. **Cache Utilization**: PHPStan and other tools cache results
4. **Resource Allocation**: Ensure adequate Docker resources

### Typical Execution Times

| Command      | Typical Duration | Notes                    |
| ------------ | ---------------- | ------------------------ |
| `style`      | 10-30 seconds    | Fast, mostly I/O bound   |
| `phpstan`    | 30-120 seconds   | Depends on codebase size |
| `test`       | 30-300 seconds   | Depends on test count    |
| `security`   | 10-30 seconds    | Network dependent        |
| `full-check` | 5-15 minutes     | Complete analysis        |

## 🔍 Troubleshooting

### Common Issues

#### PHPStan Memory Issues

```bash
# Increase memory limit
docker-compose -f compose.dev.yaml exec workspace php -d memory_limit=2G ./vendor/bin/phpstan analyse
```

#### Test Database Issues

```bash
# Reset test database
make artisan cmd="migrate:fresh --env=testing"
```

#### Permission Issues

```bash
# Fix file permissions
sudo chown -R $USER:$USER storage bootstrap/cache
```

#### Docker Container Issues

```bash
# Restart development environment
make down
make dev
```

### Debug Mode

For detailed debugging, run commands with verbose output:

```bash
# Enable debug mode
set -x
./scripts/code-quality.sh phpstan
set +x
```

## 🎯 Best Practices

### For Developers

1. **Run `style` before committing**: Ensures consistent formatting
2. **Use `phpstan` during development**: Catch issues early
3. **Run `test` before pushing**: Ensure functionality
4. **Weekly `full-check`**: Comprehensive quality review
5. **Keep dependencies updated**: Regular `security` audits

### For Teams

1. **Standardize on script usage**: All team members use same tools
2. **Integrate with CI/CD**: Automate quality checks
3. **Document exceptions**: When and why to skip certain checks
4. **Regular training**: Keep team updated on tool usage
5. **Monitor metrics**: Track code quality trends

### For CI/CD Integration

```yaml
# Example GitHub Actions integration
- name: Run Code Quality Checks
  run: |
      make setup
      make full-quality-check
```

## 📈 Metrics and Reporting

### Quality Metrics Tracked

- **Code Coverage**: Percentage of code covered by tests
- **Static Analysis Issues**: Number of PHPStan/Psalm issues
- **Security Vulnerabilities**: Count of known vulnerabilities
- **Code Style Violations**: Formatting issues found/fixed
- **Test Success Rate**: Percentage of passing tests

### Reporting

- **Console Output**: Immediate feedback during development
- **Coverage Reports**: HTML reports for detailed analysis
- **CI/CD Integration**: Automated reporting in pipelines
- **IDE Integration**: Real-time feedback during coding

## 🔮 Future Enhancements

### Planned Improvements

1. **Performance Profiling**: Add performance analysis tools
2. **Code Complexity Analysis**: Cyclomatic complexity reporting
3. **Dependency Analysis**: Unused dependency detection
4. **Architecture Validation**: SOLID principles checking
5. **Custom Rules**: Project-specific quality rules

### Integration Opportunities

1. **SonarQube Integration**: Enterprise-grade analysis
2. **Slack Notifications**: Team quality alerts
3. **Dashboard Creation**: Quality metrics visualization
4. **Automated Fixes**: AI-powered code improvements

## 📚 Additional Resources

### Tool Documentation

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Psalm Documentation](https://psalm.dev/docs/)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

### Related Project Documentation

- [Pre-commit Setup Guide](./pre-commit-setup.md)
- [Architecture Guide](./architecture.md)
- [DevOps Guide](./devops.md)
- [Docker Setup Guide](../docker/README.md)

## 🤝 Contributing

When modifying the code quality script:

1. **Test thoroughly**: Ensure all commands work in clean environment
2. **Update documentation**: Keep this guide current
3. **Maintain compatibility**: Ensure Docker integration works
4. **Add error handling**: Graceful failure and recovery
5. **Performance consideration**: Optimize for speed when possible

---

**Note**: This script is designed to work within the Docker development environment. Ensure your development environment is properly set up before using these tools.
