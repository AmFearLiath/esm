// Minimaler JS-Code für Konfiguration, Sprache und Anzeige "Nächstes Backup"

// --- i18n Initialisierung ---
const languages = [
  { code: "de", name: "Deutsch", icon: "fi-de" },
  { code: "en", name: "English", icon: "fi-gb" },
  { code: "fr", name: "Français", icon: "fi-fr" },
  { code: "es", name: "Español", icon: "fi-es" },
  { code: "it", name: "Italiano", icon: "fi-it" }
];
let currentLang = localStorage.getItem("esmLang") || "en";
let i18nLoaded = false;

function loadI18n(lang, cb) {
  fetch(`locales/${lang}.json`)
    .then(r => r.json())
    .then(data => {
      window.i18nData = data;
      i18nLoaded = true;
      cb && cb();
    });
}
function applyI18n() {
  document.querySelectorAll("[data-i18n]").forEach(el => {
    const key = el.getAttribute("data-i18n");
    let val = key.split('.').reduce((o, i) => o && o[i], window.i18nData);
    if (val) el.innerHTML = val;
  });
  document.getElementById("esm-title").innerText = window.i18nData?.title || "Enshrouded Server Manager";
}
function fillLanguageSelect() {
  const sel = document.getElementById("languageSelect");
  sel.innerHTML = "";
  languages.forEach(l => {
    const opt = document.createElement("option");
    opt.value = l.code;
    opt.textContent = l.name;
    sel.appendChild(opt);
  });
  sel.value = currentLang;
  updateLanguageFlag(sel.value);
}

function updateLanguageFlag(lang) {
  const flag = document.getElementById("languageFlag");
  // Englisch = gb.svg, sonst code.svg
  flag.src = `assets/flags/${lang === 'en' ? 'gb' : lang}.svg`;
  flag.alt = lang;
}
function setLanguage(lang) {
  currentLang = lang;
  localStorage.setItem("esmLang", lang);
  loadI18n(lang, () => {
    applyI18n();
    fillLanguageSelect();
  });
}

// --- Alert/Console/Logger ---
function showAlert(type, msg) {
  const alert = document.getElementById("alert");
  const alertMsg = document.getElementById("alert-message");
  alert.className = "alert alert-" + (type || "primary") + " alert-dismissible";
  alertMsg.innerHTML = msg;
  alert.classList.remove("d-none");
  setTimeout(() => alert.classList.add("d-none"), 3000);
}
function logToConsole(consoleId, msg, type = "info") {
  const c = document.getElementById(consoleId);
  const line = document.createElement("div");
  line.className = "console-line text-" + (type === "error" ? "danger" : type === "warn" ? "warning" : "secondary");
  line.innerText = `[${new Date().toLocaleTimeString()}] ${msg}`;
  c.appendChild(line);
  c.scrollTop = c.scrollHeight;
}

function renderConsole(log, consoleId) {
  const lines = log.split('\n').filter(Boolean);
  const consoleDiv = document.getElementById(consoleId);
  const consoleProgress = document.getElementById('consoleProgress');
  consoleDiv.innerHTML = "";

  // Hilfsfunktion für korrektes Parsen von key:param (alles nach erstem : als param)
  function extractKeyParam(str, prefixLen) {
    const rest = str.substring(prefixLen);
    const idx = rest.indexOf(":");
    if (idx !== -1) {
      return [rest.substring(0, idx), rest.substring(idx + 1)];
    } else {
      return [rest, ""];
    }
  }

  lines.forEach(line => {
    let type = "secondary";
    let msg = "";
    // Zeitstempel entfernen
    const content = line.replace(/^\[.*?\]\s*/, "");
    // Typ und Schlüssel extrahieren
    let key = content, param = "";
    if (content.startsWith("EVENT:")) {
      type = "success";
      [key, param] = extractKeyParam(content, 6);
    } else if (content.startsWith("WARN:")) {
      type = "warning";
      [key, param] = extractKeyParam(content, 5);
    } else if (content.startsWith("ERROR:")) {
      type = "danger";
      [key, param] = extractKeyParam(content, 6);
    } else if (content.startsWith("PROGRESS:")) {
      type = "primary";
      const [done, total] = content.substring(9).split("/");
      const percent = Math.round((parseInt(done) / parseInt(total)) * 100);
      if (consoleProgress) {
        consoleProgress.style.width = percent + "%";
        consoleProgress.innerText = `${done}/${total} (${percent}%)`;
      }
      return;
    } else if (content.startsWith("NEXT:")) {
      type = "info";
      [key, param] = extractKeyParam(content, 5);
    } else {
      msg = content;
    }

    // Nur Dateiname für bestimmte Events
    if (
      ["zipCreated", "oldBackupDeletedFile", "oldBackupDeletedFolder"].includes(key) &&
      param
    ) {
      param = param.trim();
      // Nur Dateiname extrahieren
      param = param.split(/[\\/]/).pop();
    } else if (param) {
      param = param.trim();
    }

    // Übersetzung holen
    let i18n = window.i18nData?.console?.[key] || key;
    msg = replacePlaceholders(i18n, param);

    consoleDiv.innerHTML += `<div class="console-line text-${type}">${msg}</div>`;
  });
}

function pollBackupLog() {
  controlTask('backup', 'log', data => {
    if (data.log) renderConsole(data.log, "backupConsole");
  });
}
function pollSchedulerLog() {
  controlTask('schedule', 'log', data => {
    if (data.log) renderConsole(data.log, "scheduleConsole");
  });
}

// --- Optionen speichern/laden ---
function getOptions() {
  return {
    ftpServer: document.getElementById("ftpServer").value,
    ftpPort: document.getElementById("ftpPort").value,
    ftpUser: document.getElementById("ftpUser").value,
    ftpPass: document.getElementById("ftpPass").value,
    ftpDir: document.getElementById("ftpDir").value,
    lang: document.getElementById("languageSelect").value,
    theme: document.getElementById("themeSelect").value,
    slideshow: document.getElementById("slideshowSelect").value,
    backupLocalPath: document.getElementById("backupLocalPath")?.value || "",
    backupRotation: document.getElementById("backupRotation")?.value || "",
    backupZip: document.getElementById("backupZip")?.value || "",
    backupSchedule: document.getElementById("backupSchedule")?.value || "",
    scheduleSavegame: document.getElementById("scheduleSavegame")?.value || "",
    scheduleSchedule: document.getElementById("scheduleSchedule")?.value || "",
    scheduleLocalSavegame: document.getElementById("scheduleLocalSavegame")?.value || ""
  };
}
function setOptions(opts) {
  document.getElementById("ftpServer").value = opts.ftpServer || "";
  document.getElementById("ftpPort").value = opts.ftpPort || "21";
  document.getElementById("ftpUser").value = opts.ftpUser || "";
  document.getElementById("ftpPass").value = opts.ftpPass || "";
  document.getElementById("ftpDir").value = opts.ftpDir || "";
  document.getElementById("languageSelect").value = opts.lang || currentLang;
  document.getElementById("themeSelect").value = opts.theme || "dark";
  document.getElementById("slideshowSelect").value = opts.slideshow || "on";
  if (document.getElementById("backupLocalPath")) document.getElementById("backupLocalPath").value = opts.backupLocalPath || "";
  if (document.getElementById("backupRotation")) document.getElementById("backupRotation").value = opts.backupRotation || "5";
  if (document.getElementById("backupZip")) document.getElementById("backupZip").value = opts.backupZip || "1";
  if (document.getElementById("backupSchedule")) document.getElementById("backupSchedule").value = opts.backupSchedule || "";
  if (document.getElementById("scheduleSavegame")) document.getElementById("scheduleSavegame").value = opts.scheduleSavegame || "";
  if (document.getElementById("scheduleSchedule")) document.getElementById("scheduleSchedule").value = opts.scheduleSchedule || "";
  if (document.getElementById("scheduleLocalSavegame")) document.getElementById("scheduleLocalSavegame").value = opts.scheduleLocalSavegame || "";
}

// Config speichern/laden via API
function saveConfig(opts, cb, errcb) {
  fetch('script/api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action: 'saveConfig', data: JSON.stringify(opts) })
  })
    .then(r => r.json())
    .then(data => {
      if (data.ok) cb && cb(data);
      else errcb && errcb(data.msg || 'Error');
    })
    .catch(() => errcb && errcb('Network error'));
}
function loadConfig(cb, errcb) {
  fetch('script/api.php?action=loadConfig')
    .then(r => r.json())
    .then(data => {
      if (data.ok) cb && cb(data.data);
      else errcb && errcb(data.msg || 'Error');
    })
    .catch(() => errcb && errcb('Network error'));
}

// Hilfsfunktion: Nächstes Backup berechnen
function getNextBackupTime(schedule) {
  if (!schedule) return null;
  schedule = schedule.trim();
  const now = new Date();
  if (schedule.startsWith("every")) {
    const match = schedule.match(/every\s+(\d+)\s*s?/i);
    if (match) {
      const seconds = parseInt(match[1], 10);
      const next = new Date(now.getTime() + seconds * 1000);
      return next;
    }
  } else if (/^\d{1,2}:\d{2}/.test(schedule)) {
    const times = schedule.split(",").map(t => t.trim());
    let nextTime = null;
    for (const t of times) {
      const [h, m] = t.split(":").map(Number);
      const candidate = new Date(now);
      candidate.setHours(h, m, 0, 0);
      if (candidate <= now) candidate.setDate(candidate.getDate() + 1);
      if (!nextTime || candidate < nextTime) nextTime = candidate;
    }
    return nextTime;
  }
  return null;
}

function showNextBackupConsole() {
  const schedule = document.getElementById("backupSchedule")?.value;
  const next = getNextBackupTime(schedule);
  if (next) {
    const msg = (window.i18nData?.console?.nextBackup || "Nächstes Backup:") + " " +
      next.toLocaleString();
    logToConsole("backupConsole", msg, "info");
  }
}



// --- Event-Handler und Initialisierung ---
document.addEventListener("DOMContentLoaded", () => {

  // Sprach-Auswahl füllen und Flag setzen
  fillLanguageSelect();

  // Sprache initial setzen und Übersetzungen laden
  setLanguage(currentLang);

  // i18n laden und initialisieren
  document.getElementById("languageSelect").addEventListener("change", e => {
    setLanguage(e.target.value);
    updateLanguageFlag(e.target.value);
  });

  // Theme ändern: sofort sichtbar machen (ohne speichern)
  document.getElementById("themeSelect").addEventListener("change", e => {
    document.body.setAttribute("data-bs-theme", e.target.value);
  });

  // Slideshow an/aus: sofort sichtbar machen (ohne speichern)
  document.getElementById("slideshowSelect").addEventListener("change", e => {
    document.querySelector(".background-slideshow").style.display = (e.target.value === "on") ? "" : "none";
  });

  // Slideshow-Initialisierung
  const slides = Array.from(document.querySelectorAll("#backgroundSlideshow img"));
  let idx = 0;
  let paused = false;
  let interval = null;

  // Config laden und ins Formular eintragen
  loadConfig(opts => {
    setOptions(opts);
    showNextBackupConsole();
    document.body.setAttribute("data-bs-theme", opts.theme || "dark");
    document.querySelector(".background-slideshow").style.display = (opts.slideshow || "on") === "on" ? "" : "none";
  });

  // Backup-Formular speichern
  document.getElementById("backupForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const opts = getOptions();
    saveConfig(opts, () => {
      showAlert("success", window.i18nData?.alert?.optionsSaved || "Backup settings saved.");
      showNextBackupConsole();
    }, (err) => {
      showAlert("danger", err);
    });
  });

  // Scheduler-Formular speichern
  document.getElementById("scheduleForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const opts = getOptions();
    saveConfig(opts, () => {
      showAlert("success", window.i18nData?.alert?.optionsSaved || "Schedule settings saved.");
    }, (err) => {
      showAlert("danger", err);
    });
  });

  // Optionen speichern
  document.getElementById("optionsForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const opts = getOptions();
    saveConfig(opts, () => {
      showAlert("success", window.i18nData?.alert?.optionsSaved || "Options saved.");
      showNextBackupConsole();
    }, (err) => {
      showAlert("danger", err);
    });
  });

  // Backup-Pfad-Auswahl (nur Desktop-Browser, kein echtes Browsing im Web möglich)
  const selectBtn = document.getElementById("selectBackupPathBtn");
  if (selectBtn) {
    selectBtn.addEventListener("click", async function () {
      if ('showDirectoryPicker' in window) {
        try {
          const dirHandle = await window.showDirectoryPicker();
          document.getElementById("backupLocalPath").value = dirHandle.name;
        } catch (e) {
          showAlert("danger", "Pfad-Auswahl abgebrochen oder nicht erlaubt.");
        }
      } else {
        showAlert("danger", "Die Pfadauswahl wird von diesem Browser nicht unterstützt.");
      }
    });
  }

  // Umschalten Pause/Play Icon
  const pauseBtn = document.getElementById('slideshowPause');
  pauseBtn.addEventListener('click', function() {
    const icon = this.querySelector('span');
    if (icon.classList.contains('bx-pause')) {
      icon.classList.remove('bx-pause');
      icon.classList.add('bx-play');
      // Slideshow pausieren...
    } else {
      icon.classList.remove('bx-play');
      icon.classList.add('bx-pause');
      // Slideshow fortsetzen...
    }
  });

  // --- Slideshow-Funktionen ---
  function showSlide(i) {
    slides.forEach((img, n) => img.classList.toggle("active", n === i));
    idx = i;
  }
  function nextSlide() {
    showSlide((idx + 1) % slides.length);
  }
  function prevSlide() {
    showSlide((idx - 1 + slides.length) % slides.length);
  }
  function play() {
    paused = false;
    document.getElementById("slideshowPause").innerHTML = "&#10073;&#10073;";
    if (interval) clearInterval(interval);
    interval = setInterval(() => { if (!paused) nextSlide(); }, 6000);
  }
  function pause() {
    paused = true;
    document.getElementById("slideshowPause").innerHTML = "&#9654;";
    if (interval) clearInterval(interval);
  }

  // Backup Buttons
  document.getElementById("backupStart").onclick = () =>
    controlTask('backup', 'start', data => {
      logToConsole("backupConsole", "Backup gestartet.", "info");
      updateBackupButtons();
    }, err => logToConsole("backupConsole", err, "error"));

  document.getElementById("backupStop").onclick = () =>
    controlTask('backup', 'stop', data => {
      logToConsole("backupConsole", "Backup gestoppt.", "warn");
      updateBackupButtons();
    }, err => logToConsole("backupConsole", err, "error"));

  document.getElementById("backupRestart").onclick = () =>
    controlTask('backup', 'restart', data => {
      logToConsole("backupConsole", "Backup neugestartet.", "info");
      updateBackupButtons();
    }, err => logToConsole("backupConsole", err, "error"));

  // Scheduler Buttons
  document.getElementById("scheduleStart").onclick = () => {
    controlTask('schedule', 'start', data => {
      logToConsole("scheduleConsole", "Scheduler gestartet.", "info");
      updateSchedulerButtons();
    }, err => logToConsole("scheduleConsole", err, "error"));
  };
  document.getElementById("scheduleStop").onclick = () => {
    controlTask('schedule', 'stop', data => {
      logToConsole("scheduleConsole", "Scheduler gestoppt.", "warn");
      updateSchedulerButtons();
    }, err => logToConsole("scheduleConsole", err, "error"));
  };
  document.getElementById("scheduleRestart").onclick = () => {
    controlTask('schedule', 'restart', data => {
      logToConsole("scheduleConsole", "Scheduler neugestartet.", "info");
      updateSchedulerButtons();
    }, err => logToConsole("scheduleConsole", err, "error"));
  };

  document.getElementById("slideshowNext").onclick = () => { nextSlide(); play(); };
  document.getElementById("slideshowPrev").onclick = () => { prevSlide(); play(); };
  document.getElementById("slideshowPause").onclick = () => {
    if (paused) play(); else pause();
  };

  updateBackupButtons();
  updateSchedulerButtons();
  
  setInterval(pollBackupLog, 2000);
  setInterval(pollSchedulerLog, 2000);
  setInterval(updateBackupButtons, 2000);
  setInterval(updateSchedulerButtons, 2000);

  showSlide(0);
  play();
});

// --- API-Funktionen für Backup/Scheduler ---
function controlTask(type, action, cb, errcb) {
  fetch('script/api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ type, action })
  })
    .then(r => r.json())
    .then(data => {
      if (data.ok) cb && cb(data);
      else errcb && errcb(data.msg || 'Error');
    })
    .catch(() => errcb && errcb('Network error'));
}

function updateBackupButtons() {
  controlTask('backup', 'status', data => {
    const running = data.running;
    document.getElementById("backupStart").disabled = running;
    document.getElementById("backupStop").disabled = !running;
    document.getElementById("backupRestart").disabled = !running;
  });
}

function updateSchedulerButtons() {
  controlTask('schedule', 'status', data => {
    const running = data.running;
    document.getElementById("scheduleStart").disabled = running;
    document.getElementById("scheduleStop").disabled = !running;
    document.getElementById("scheduleRestart").disabled = !running;
  });
}

// Automatische Savegame-Nummer-Erkennung
document.getElementById("scheduleLocalSavegame").addEventListener("input", function() {
  const val = this.value.trim();
  let num = "";
  // Erkennung: 3ad85aea (0), 3ad85aea-1 ... 3ad85aea-9
  const match = val.match(/3ad85aea(?:-(\d))?$/);
  if (match) {
    num = match[1] || "0";
  }
  document.getElementById("scheduleSavegame").value = num;
});

// Log leeren Buttons
document.getElementById("backupClearLog").addEventListener("click", function() {
  document.getElementById("backupConsole").innerHTML = "";
  fetch('script/api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ type: 'backup', action: 'clearlog' })
  });
});
document.getElementById("scheduleClearLog").addEventListener("click", function() {
  document.getElementById("scheduleConsole").innerHTML = "";
  fetch('script/api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ type: 'schedule', action: 'clearlog' })
  });
});

function replacePlaceholders(str, param) {
  // Versuche, den Param als JSON zu parsen, falls mehrere Werte übergeben werden
  if (param && param.startsWith("{") && param.endsWith("}")) {
    try {
      const obj = JSON.parse(param);
      for (const k in obj) {
        str = str.replace(new RegExp(`{${k}}`, "g"), obj[k]);
      }
      return str;
    } catch {}
  }
  // Fallback: Ersetze Standard-Keys
  return str
    .replace("{file}", param)
    .replace("{dir}", param)
    .replace("{time}", param)
    .replace("{fields}", param)
    .replace("{pid}", param)
    .replace("{latest}", param)
    .replace("{interval}", param)
    .replace("{wait}", param)
    .replace("{savegame}", param);
}