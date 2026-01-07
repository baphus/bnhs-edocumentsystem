# PostgreSQL Migration Guide

**Status:** Your application has been configured to use PostgreSQL ✅

## What's Been Changed

1. **Database Driver:** Changed from SQLite to PostgreSQL
2. **Config Updated:** `config/database.php` - default connection set to `pgsql`
3. **Environment Updated:** `.env` file now points to PostgreSQL
4. **Backup Created:** `.env.backup` contains your previous SQLite config

## Next Steps: Set Up PostgreSQL Server

### Option 1: Local PostgreSQL (Development)

#### Windows using PostgreSQL Installer:
```bash
# Download from: https://www.postgresql.org/download/windows/
# Run installer and note the password you set for 'postgres' user
```

#### Windows using Homebrew:
```bash
# Install PostgreSQL
brew install postgresql@16

# Start PostgreSQL service
brew services start postgresql@16

# Create the database
createdb bnhs_edocument
```

#### Windows using WSL2 + PostgreSQL:
```bash
# In WSL terminal
sudo apt update && sudo apt install postgresql postgresql-contrib -y
sudo service postgresql start

# Create database
createdb bnhs_edocument
```

### Option 2: Docker Container (Recommended for Development)

```bash
# Pull PostgreSQL image
docker pull postgres:16-alpine

# Run PostgreSQL container
docker run -d `
  --name bnhs-postgres `
  -e POSTGRES_USER=postgres `
  -e POSTGRES_PASSWORD= `
  -e POSTGRES_DB=bnhs_edocument `
  -p 5432:5432 `
  postgres:16-alpine

# Verify connection
docker logs bnhs-postgres
```

### Option 3: Cloud Database (Production)

- **Azure Database for PostgreSQL:** Managed PostgreSQL in Azure
- **AWS RDS PostgreSQL:** Amazon's managed service
- **Digital Ocean:** PostgreSQL droplets
- **Heroku Postgres:** Built-in with Heroku

## Update .env for Your PostgreSQL Setup

Edit `.env` and update these values based on your PostgreSQL setup:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1          # Change if using remote server
DB_PORT=5432
DB_DATABASE=bnhs_edocument
DB_USERNAME=postgres       # Change if different
DB_PASSWORD=               # Add password if set
```

## Migrate Your Data

### From SQLite to PostgreSQL

If you have existing data in SQLite:

```bash
# 1. Set connection to sqlite temporarily
# Edit .env: DB_CONNECTION=sqlite

# 2. Export data (dump)
php artisan migrate
php artisan db:seed

# 3. Switch back to PostgreSQL
# Edit .env: DB_CONNECTION=pgsql

# 4. Run migrations on PostgreSQL
php artisan migrate:fresh

# 5. Consider manual data migration or script if needed
```

### Fresh Start (Recommended)

If you're starting fresh with PostgreSQL:

```bash
# Run all migrations on PostgreSQL
php artisan migrate

# Seed data if needed
php artisan db:seed
```

## Test Connection

```bash
# Try to access database through Tinker
php artisan tinker

# In Tinker prompt, run:
> DB::connection('pgsql')->getPdo()
// Should return PDOConnection object

# Check if migrations table exists:
> DB::table('migrations')->get()
```

## Performance Benefits You'll Get

✅ **Better Concurrency** - PostgreSQL handles multiple concurrent connections efficiently  
✅ **MVCC** - Multi-Version Concurrency Control for better lock management  
✅ **Prepared Statements** - Native support for prepared statements  
✅ **Advanced Indexing** - GiST, GIN, BRIN index types  
✅ **JSON Support** - Native JSON/JSONB columns for flexible data  
✅ **Full-Text Search** - Built-in advanced search capabilities  
✅ **Transactions** - Better transaction isolation levels  

## Troubleshooting

### "SQLSTATE[08006]" - Connection Failed
- Verify PostgreSQL is running
- Check DB_HOST, DB_PORT in .env
- Check DB_USERNAME and DB_PASSWORD

### "SQLSTATE[42P01]" - Table Doesn't Exist
- Run migrations: `php artisan migrate`
- Check migrations: `php artisan migrate:status`

### "role 'postgres' does not exist"
- Create postgres user: `createuser postgres`
- Grant permissions: `createdb -O postgres bnhs_edocument`

## Rollback to SQLite (If Needed)

```bash
# Restore backup
cp .env.backup .env

# This reverts to SQLite configuration
```

## Additional PostgreSQL Configuration

### For Production (.env.production):

```env
DB_CONNECTION=pgsql
DB_HOST=your-db-host.postgres.database.azure.com  # or your host
DB_PORT=5432
DB_DATABASE=bnhs_edocument
DB_USERNAME=postgres@servername              # Azure format example
DB_PASSWORD=your-strong-password
DB_SSLMODE=require
```

### Recommended Composer Script

Add to `composer.json` `scripts` section for easy setup:

```json
"postgres-setup": [
    "php artisan migrate:fresh",
    "php artisan db:seed",
    "php artisan cache:clear"
]
```

Then run: `composer postgres-setup`

## Next: Test Your Application

```bash
# Start your dev server
composer dev

# Open browser to http://127.0.0.1:8000
```

---

**Questions?** Check PostgreSQL logs with `docker logs bnhs-postgres` (if using Docker)
