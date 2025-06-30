# Enshrouded Server Manager (ESM)

## What is it?

**Enshrouded Server Manager (ESM)** is a web-based management tool for Enshrouded Server save games.
The tool enables you to:
- **Automated backups** of your save games via FTP (with rotation and schedule)
- **Automated reset** regularly restores your save game to a desired file.
- **Convenient configuration** via a modern web interface
- **Multilingual support** and a customizable design

Ideal for anyone who wants to manage their Enshrouded Server data securely and conveniently!

---

## Preparation

### 1. **Download required software**

- **XAMPP for Windows:**
[https://www.apachefriends.org/de/download.html](https://www.apachefriends.org/de/download.html)

- **Enshrouded Server Manager (ESM):**
Download the tool as a ZIP file from your repository or release.

### 2. **Install XAMPP**

- Install XAMPP (recommended: default path `C:\xampp`).
- Start the XAMPP Control Panel.

### 3. **Configure XAMPP** (optional, if you want to zip your backups)

- in XAMPP Control Panel click on `Config` for Apache
- Select PHP (php.ini)
- Search for ;extension=zip
- Remove the ;
- Result: extension=zip
- Save the file

### 4. **Unpack and set up ESM**

- Unpack the ESM tool into the folder:
`C:\xampp\htdocs\ESM`
- The structure should look like this:
```
C:\xampp\htdocs\ESM
├── /assets
├── /locales
├── /script
│ ├── /logs
│ └── /pids
├── config.json
├── index.php
└── readme.md
```
The subfolders `logs` and `pids` will be created automatically on first start if they do not exist.
---

## Start the environment

1. **Open the XAMPP Control Panel**
2. **Start Apache**
3. **Open your browser** and go to:
[http://localhost/ESM/](http://localhost/ESM/)

---

## How it works

- The tool runs completely locally on your computer in the browser.
- It uses PHP scripts to perform backups and scheduled tasks in the background.
- Configuration is done via the web interface and is saved in a `config.json` file.
- Backups are downloaded via FTP from your Enshrouded server and stored locally.
- The interface is multilingual and can be visually customized.

---

## User manual

### **1. Options (Tab: Options)**

Here you can configure the basic settings:

- **FTP Server:** Address of your Enshrouded server (e.g., 38.242.208.125)
- **FTP Port:** Usually 21 or as specified by the host
- **FTP User/Password:** Login credentials for FTP access
- **Savegame Directory:** Usually /savegame (depending on the server)
- **Design:** Language, Theme (light/dark), Slideshow (background images)

---

### **2. Backuper (Tab: Backups)**

Here you control the automatic backups:

- **Start:** Starts the backup process in the background
- **Stop:** Ends the running backup process
- **Restart:** Stops and restarts the backup process
- **Backup Schedule:**
- **Seconds Interval:** e.g., `every 3600s` (every 3600 seconds)
- **Times:** e.g., `03:00, 15:00` (backups at 3:00 and 15:00)
- **Backup Rotation:** How many backups are kept
- **ZIP:** Whether the backups are saved as ZIP files

**Console:**
Displays the status, progress, and errors of the backups.

The next scheduled backup is displayed.

---

### **3. Scheduler (Tab: Scheduler)**

The scheduler is intended for users who want to regularly make a specific save game available on the server (e.g., for a storage server or to reset to a specific game state).

**Here's how:**

- **Specify the save game path:**

In the "Local save game path" field, enter the full path to your save game file (e.g., `C:\Path\to\Savegame\3ad85aea-1`).

- **Save game is recognized:**

The save game number is automatically recognized from the file name and displayed in the corresponding field (this field is read-only).

- **Set the repetition time:**

Specify the interval or time at which the save game should be transferred to the server (e.g., `every 3600s` or `03:00, 15:00`).

- **Start Scheduler:**
Start the scheduler. The tool will then automatically transfer the specified save game to the server at the desired intervals and set the index file accordingly.

---

## Troubleshooting

- **Backup/Scheduler doesn't start:**
Check the FTP credentials and whether the server is accessible.
- **Buttons don't respond:**
Reload the page, clear your browser cache if necessary.
- **PHP error:**
Check the XAMPP log (`C:\xampp\apache\logs\error.log`).

---

## Security

- This tool is intended for local use.
- Do not share your FTP credentials with third parties.
- Set a strong password for your FTP access.

---

Have fun with the Enshrouded Server Manager!
