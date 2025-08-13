# Laravel API SOLID - Documentation

Welcome to the Laravel API SOLID documentation. This directory contains comprehensive documentation for the project.

## 📚 Documentation Structure

### General Documentation

- [Architecture](./general/architecture.md) - Project architecture and SOLID principles implementation
- [DevOps](./general/devops.md) - Development operations, deployment, and environment setup
- [Service Repository Pattern](./general/service-repository-pattern.md) - Detailed guide on the service and repository pattern implementation
- [Route Generation](./general/route-generation.md) - Complete guide to the enhanced route generation commands
- [Code Quality Tools](./general/code-quality-tools.md) - Comprehensive guide to code quality scripts and tools
- [Pre-commit Setup](./general/pre-commit-setup.md) - Pre-commit hooks configuration and usage

### API Documentation

- [Scramble API Documentation](./general/scramble-api-documentation.md) - Comprehensive guide to automatic API documentation with Scramble
- [Scramble Quick Reference](./general/scramble-quick-reference.md) - Quick reference guide for developers using Scramble

## 🚀 Quick Links

- [Main Project README](../README.md)
- [Live API Documentation](http://localhost:8000/docs/api) - Interactive Swagger UI (when server is running)
- [Scramble Setup Guide](./general/scramble-api-documentation.md) - Complete setup documentation
- [Development Guide](./development/) (Coming Soon)
- [Deployment Guide](./deployment/) (Coming Soon)

## 📖 Getting Started

For a quick overview of the project, start with the [main README](../README.md). For detailed technical information, explore the specific documentation files in the `general/` directory.

## 🔗 API Documentation

This project uses **Scramble** for automatic API documentation generation. Scramble analyzes your Laravel code to create comprehensive OpenAPI documentation without requiring manual annotations.

### Quick Access

- **Live Documentation**: [http://localhost:8000/docs/api](http://localhost:8000/docs/api) (requires `php artisan serve`)
- **Setup Guide**: [Scramble API Documentation](./general/scramble-api-documentation.md)
- **Quick Reference**: [Scramble Quick Reference](./general/scramble-quick-reference.md)

### Current API Endpoints

- `GET /api/v1/users` - List users with search and pagination
- `POST /api/v1/users` - Create a new user
- `GET /api/v1/users/{user}` - Show specific user
- `PUT/PATCH /api/v1/users/{user}` - Update user
- `DELETE /api/v1/users/{user}` - Delete user
- `GET /api/health` - Health check endpoint
- `GET /api/user` - Get authenticated user (requires auth)

### Features

✅ **Automatic Documentation** - No manual annotations required  
✅ **Interactive Testing** - Try endpoints directly from the documentation  
✅ **Request/Response Schemas** - Generated from FormRequests and API Resources  
✅ **Validation Rules** - Automatically documented with error messages  
✅ **Authentication Requirements** - Shows which endpoints require auth

### For Developers

To add new endpoints:

1. Create Controller with proper return type hints
2. Create FormRequest for validation rules
3. Create API Resource for response formatting
4. Add Route to `routes/api.php`
5. Documentation updates automatically ✨
