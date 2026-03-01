# Attendance Tracker

Simple PHP + MySQL attendance app with organized folders.

## Folder Structure

```text
attendance-tracker/
|-- public/                          # Web root (serve this folder)
|   |-- index.php                    # Root entry point (loads pages/index.php)
|   |-- pages/
|   |   |-- index.php                # Home + login + attendance logs
|   |   |-- users.php                # Registered users page
|   |   `-- report.php               # Date range report page
|   |-- actions/
|   |   `-- mark.php                 # POST actions: login/logout/mark/delete
|   |-- service-worker.js            # Offline cache logic (root scope)
|   `-- assets/
|       |-- styles.css               # App styles
|       |-- manifest.json            # PWA manifest
|       |-- offline.html             # Offline fallback page
|       |-- app_icon.png             # App icon
|       `-- icons/                   # PWA icon set
|
|-- config/
|   `-- config.php                   # DB connection + session + timezone config
|
|-- database/
|   `-- schema.sql                   # Database schema
|
`-- assets/
    `-- archive/
        `-- icons.zip                # Icon archive backup
```

## Run Locally

```powershell
cd d:\Github\attendance-tracker
mysql -u root -p attendance_tracker < database\schema.sql
php -S 127.0.0.1:8000 -t public
```

Open: `http://127.0.0.1:8000`

## Notes

- Update DB credentials/port in `config/config.php` if needed.
- App routes now use:
  - `/` (home)
  - `/pages/users.php`
  - `/pages/report.php`
  - `/pages/heatmaps.php`
  - `/actions/mark.php`
