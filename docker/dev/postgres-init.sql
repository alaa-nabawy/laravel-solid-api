-- PostgreSQL initialization script for development
-- This script runs when the PostgreSQL container is first created

-- Create additional databases for testing
CREATE DATABASE laravel_test;

-- Grant permissions
GRANT ALL PRIVILEGES ON DATABASE laravel_dev TO laravel;
GRANT ALL PRIVILEGES ON DATABASE laravel_test TO laravel;

-- Create extensions that might be useful
\c laravel_dev;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";

\c laravel_test;
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";
CREATE EXTENSION IF NOT EXISTS "unaccent";
