# Enshrouded Server Manager (ESM)

## Was ist das?

**Enshrouded Server Manager (ESM)** ist ein webbasiertes Verwaltungstool für Enshrouded-Server-Spielstände.  
Das Tool ermöglicht dir:
- **Automatisierte Backups** deiner Savegames per FTP (mit Rotation und Zeitplan)
- **Geplante Aufgaben** (Scheduler) für Savegames
- **Komfortable Konfiguration** über eine moderne Weboberfläche
- **Mehrsprachigkeit** und ein anpassbares Design

Ideal für alle, die ihre Enshrouded-Serverdaten sicher und komfortabel verwalten möchten!

---

## Vorbereitung

### 1. **Benötigte Software herunterladen**

- **XAMPP für Windows:**  
  [https://www.apachefriends.org/de/download.html](https://www.apachefriends.org/de/download.html)
- **Enshrouded Server Manager (ESM):**  
  Lade das Tool als ZIP von deinem Repository oder Release herunter.

### 2. **XAMPP installieren**

- Installiere XAMPP (empfohlen: Standardpfad `C:\xampp`).
- Starte das XAMPP Control Panel.

### 3. **ESM entpacken und einrichten**

- Entpacke das ESM-Tool in den Ordner:  
  `C:\xampp\htdocs\ESM`
- Die Struktur sollte so aussehen:
```
    C:\xampp\htdocs\ESM
    ├── /assets
    ├── /locales
    ├── /script
    │   ├── /logs
    │   └── /pids
    ├── config.json
    ├── index.php
    └── readme.md
```
Die Unterordner `logs` und `pids` werden beim ersten Start automatisch
angelegt, falls sie nicht existieren.
---

## Umgebung starten

1. **Starte XAMPP Control Panel**
2. **Starte Apache**
3. **Öffne deinen Browser** und rufe auf:  
 [http://localhost/ESM/](http://localhost/ESM/)

---

## Funktionsweise

- Das Tool läuft komplett lokal auf deinem Rechner im Browser.
- Es nutzt PHP-Skripte, um Backups und geplante Aufgaben im Hintergrund auszuführen.
- Die Konfiguration erfolgt über die Weboberfläche und wird in einer `config.json` gespeichert.
- Backups werden per FTP von deinem Enshrouded-Server heruntergeladen und lokal gespeichert.
- Die Oberfläche ist mehrsprachig und kann optisch angepasst werden.

---

## Bedienungsanleitung

### **1. Optionen (Tab: Optionen)**

Hier stellst du die grundlegenden Einstellungen ein:

- **FTP-Server:** Adresse deines Enshrouded-Servers (z.B. `38.242.208.125`)
- **FTP-Port:** Meist `21` oder wie vom Hoster angegeben
- **FTP-Benutzer/Passwort:** Zugangsdaten für den FTP-Zugang
- **Savegame-Verzeichnis:** Meist `/savegame` (je nach Server)
- **Design:** Sprache, Theme (hell/dunkel), Slideshow (Hintergrundbilder)
- **Backup-Speicherort:** Lokaler Pfad, wo die Backups abgelegt werden
- **Backup-Rotation:** Wie viele Backups behalten werden (ältere werden gelöscht)
- **Backup zippen:** Sollen die Backups als ZIP gespeichert werden?
- **Backup-Zeitplan:** Wann/wie oft ein Backup gemacht wird (siehe unten)
- **Scheduler-Optionen:** Savegame-Nummer und Zeitplan für geplante Aufgaben

---

### **2. Backuper (Tab: Backups)**

Hier steuerst du die automatischen Backups:

- **Start:** Startet den Backup-Prozess im Hintergrund
- **Stop:** Beendet den laufenden Backup-Prozess
- **Neustarten:** Stoppt und startet den Backup-Prozess neu
- **Backup-Zeitplan:**  
  - **Sekunden-Intervall:** z.B. `every 3600s` (alle 3600 Sekunden)
  - **Uhrzeiten:** z.B. `03:00, 15:00` (Backups um 3:00 und 15:00 Uhr)
- **Backup-Rotation:** Wie viele Backups behalten werden
- **ZIP:** Ob die Backups als ZIP gespeichert werden

**Konsole:**  
Zeigt Status, Fortschritt und Fehler der Backups an.  
Das nächste geplante Backup wird angezeigt.

---

### **3. Scheduler (Tab: Scheduler)**

Der Scheduler ist für Nutzer gedacht, die regelmäßig einen bestimmten Speicherstand auf dem Server bereitstellen möchten (z.B. für einen Lagerserver oder zum Zurücksetzen auf einen bestimmten Spielstand).

**So gehst du vor:**

- **Pfad zum Savegame angeben:**  
   Gib im Feld „Lokaler Savegame-Pfad“ den vollständigen Pfad zu deiner Savegame-Datei an (z.B. `C:\Pfad\zum\Savegame\3ad85aea-1`).

- **Savegame wird erkannt:**  
   Die Savegame-Nummer wird automatisch aus dem Dateinamen erkannt und im entsprechenden Feld angezeigt (dieses Feld ist schreibgeschützt).

- **Wiederholungszeit festlegen:**  
   Gib an, in welchem Intervall oder zu welchen Uhrzeiten das Savegame auf den Server übertragen werden soll (z.B. `every 3600s` oder `03:00, 15:00`).

- **Scheduler starten:**  
   Starte den Scheduler. Das Tool überträgt dann automatisch in den gewünschten Abständen das angegebene Savegame auf den Server und setzt die Indexdatei entsprechend.

---

## Fehlerbehebung

- **Backup/Scheduler startet nicht:**  
  Prüfe die FTP-Daten und ob der Server erreichbar ist.
- **Buttons reagieren nicht:**  
  Seite neu laden, ggf. Browser-Cache leeren.
- **PHP-Fehler:**  
  Prüfe das XAMPP-Log (`C:\xampp\apache\logs\error.log`).

---

## Sicherheit

- Das Tool ist für den lokalen Gebrauch gedacht.
- Gib deine FTP-Daten nicht an Dritte weiter.
- Setze ein starkes Passwort für deinen FTP-Zugang.

---

Viel Spaß mit dem Enshrouded Server Manager!
