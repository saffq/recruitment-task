# Recruitment Task Processor

A PHP CLI application built with Symfony Console that processes service messages from a JSON file and extracts two types of structured entities:
- Maintenance Reports
- Failure Reports

---

## 📦 Requirements

- Docker + Docker Compose
- No local PHP or Composer installation required

---

## 🚀 Quick Start with Docker

### 1. Prepare Input File

Place your source file at:

```
data/recruitment-task-source.json
```

### 2. Build the Docker Image

```bash
docker-compose build
```

### 3. Run the Processor

```bash
docker-compose up
```


or 
```bash 
php bin/console process:messages data/recruitment-task-source.json

```
This will process the input file and generate result files.

---

## 📂 Output Files

All output will be placed in the `output/` directory.  
If the directory does not exist, it will be created automatically by the app.

| File                | Description                                   |
|---------------------|-----------------------------------------------|
| `maintenance.json`  | Successfully processed maintenance entries    |
| `malfunctions.json` | Successfully processed malfunctions entries   |
| `failures.json`     | Entries that failed to be parsed or processed |

---

## 🛠 Logic Summary

### Entity Classification

| Condition                              | Result Type        |
|----------------------------------------|--------------------|
| `description` contains `przegląd`      | Maintenance Report |
| Otherwise                              | Malfunction Report |

### Status & Priority Rules

- **Maintenance**
  - With `dueDate`: `status = zaplanowano`
  - Without: `status = nowy`
- **Malfunction**
  - With `dueDate`: `status = termin`
  - Without: `status = nowy`
  - Priority:
    - `"bardzo pilne"` → `krytyczny`
    - `"pilne"` → `wysoki`
    - Otherwise → `normalny`

### De-duplication

Duplicate `description` entries are ignored after the first occurrence.

---

## 🧪 Running Tests

To run tests in Docker:

```bash
docker-compose run --rm test
```

Or locally (if you have PHP and Composer):

```bash
composer install
./vendor/bin/phpunit
```

---

## 📁 Project Structure

```
bin/console                    → Entry point CLI script
src/                          → Application source code
tests/                        → PHPUnit test classes
data/                         → Input directory for JSON
output/                       → Automatically generated results
Dockerfile                    → PHP 8.2.4 base image
docker-compose.yml            → App + test services
```

---

## ✅ Notes

- All logs go to STDOUT
- Errors during message parsing are logged and stored in `failures.json`
- Fully structured and extendable design for future rule/format updates
