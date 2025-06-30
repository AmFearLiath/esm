<!DOCTYPE html>
<html
  class="layout-navbar-fixed layout-menu-fixed layout-compact"
  data-assets-path="assets/"
  data-bs-theme="dark"
  data-skin="default"
  dir="ltr"
  lang="en"
>
  <head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" name="viewport" />
    <meta content="noindex, nofollow" name="robots" />
    <title>Enshrouded Server Manager</title>
    <link href="assets/img/favicon.ico" rel="icon" type="image/x-icon" />
    <link href="https://fonts.googleapis.com/css2?family=Ephesis&display=swap" rel="stylesheet">
    <link href="assets/css/iconify-icons.css" rel="stylesheet" />
    <link href="assets/css/core.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
  </head>
  <body>
    <!-- Slideshow background -->
    <div class="background-slideshow" id="backgroundSlideshow" aria-hidden="true">
      <img src="assets/img/wallpaper_1.jpg" class="active" />
      <img src="assets/img/wallpaper_2.jpg" />
      <img src="assets/img/wallpaper_3.jpg" />
      <img src="assets/img/wallpaper_4.jpg" />
      <img src="assets/img/wallpaper_5.jpg" />
      <img src="assets/img/wallpaper_6.jpg" />
      <img src="assets/img/wallpaper_7.jpg" />
      <img src="assets/img/wallpaper_8.jpg" />
      <!--video src="assets/video/bg3.mp4" muted loop></video-->
      <div class="slideshow-controls" id="slideshowControls">
        <button id="slideshowPrev" title="Vorheriges Bild">
          <span class="bx bx-chevron-left"></span>
        </button>
        <button id="slideshowPause" title="Pause/Play">
          <span class="bx bx-pause"></span>
        </button>
        <button id="slideshowNext" title="Nächstes Bild">
          <span class="bx bx-chevron-right"></span>
        </button>
      </div>
    </div>
    <!-- Hauptinhalt -->
    <div class="layout-wrapper">
      <div class="layout-container">
        <div class="container centered-content-wrapper">
          <div class="content-wrapper">
            <div class="content">
              <div class="row row-title g-6">
                <div class="col-xl-1">
                  <img src="assets/img/logo.png" alt="Logo" class="logo" />
                </div>
                <div class="col-xl-4">
                  <h5 class="ephesis-regular" id="esm-title">Enshrouded Server Manager</h5>
                  <span class="ephesis-regular-small" id="esm-author">by Liath</span>
                </div>
                <div class="col-xl-7">
                  <div id="alert" class="alert alert-primary d-none" role="alert">
                    <p class="mb-0" id="alert-message"></p>
                  </div>
                </div>
              </div>
              <div class="row row-tabs g-6">
                <div class="col-xl-12">
                  <div class="nav-align-top">
                    <div class="d-flex justify-content-between align-items-center">
                      <!-- Tabs -->
                      <ul class="nav nav-pills" role="tablist" id="esmTabListLeft" style="flex:1 1 auto;">
                        <li class="nav-item tabs-align-left">
                          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-backup-manager" type="button" role="tab" aria-controls="nav-backup-manager" aria-selected="true">
                            <span data-i18n="tab.backup"></span>
                          </button>
                        </li>
                        <li class="nav-item tabs-align-left">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-schedule-manager" type="button" role="tab" aria-controls="nav-schedule-manager" aria-selected="false">
                            <span data-i18n="tab.schedule"></span>
                          </button>
                        </li>
                        <li class="nav-item tabs-align-right">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-options" type="button" role="tab" aria-controls="nav-options" aria-selected="false">
                            <span data-i18n="tab.options"></span>
                          </button>
                        </li>
                      </ul>
                    </div>
                    <div class="tab-content">
                      <!-- Backup Manager -->
                      <div class="tab-pane fade show active" id="nav-backup-manager" role="tabpanel">
                        <form id="backupForm" autocomplete="off">
                          <div class="row">
                            <div class="mb-3 col-xl-6">
                              <label for="backupLocalPath" class="form-label" data-i18n="backup.localPath"></label>
                              <div class="input-group">
                                <input type="text" class="form-control" id="backupLocalPath" required>
                                <button type="button" class="btn btn-outline-secondary" id="selectBackupPathBtn" title="Pfad auswählen">
                                  <span class="bx bx-folder-open"></span>
                                </button>
                              </div>
                            </div>
                            <div class="mb-3 col-xl-6">
                              <label for="backupZip" class="form-label" data-i18n="backup.zip"></label>
                              <select class="form-select" id="backupZip">
                                <option value="1" data-i18n="yes"></option>
                                <option value="0" data-i18n="no"></option>
                              </select>
                            </div>
                            <div class="mb-3 col-xl-6">
                              <label for="backupRotation" class="form-label" data-i18n="backup.rotation"></label>
                              <input type="number" class="form-control" id="backupRotation" min="1" max="99" value="5" required>
                            </div>
                            <div class="mb-3 col-xl-6">
                              <label for="backupSchedule" class="form-label" data-i18n="backup.schedule"></label>
                              <input type="text" class="form-control" id="backupSchedule" placeholder="e.g. 02:00, 14:00 or every 3600s">
                            </div>
                          </div>
                          <div class="mb-3 d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary" id="backupSave" data-i18n="save"></button>
                            <div class="btn-group ms-auto">
                              <button type="button" class="btn btn-success" id="backupStart" data-i18n="start"></button>
                              <button type="button" class="btn btn-warning" id="backupRestart" data-i18n="restart"></button>
                              <button type="button" class="btn btn-danger" id="backupStop" data-i18n="stop"></button>
                              <button type="button" class="btn btn-secondary" id="backupClearLog" title="Log leeren">
                                <span class="bx bx-eraser"></span>
                              </button>
                            </div>
                          </div>
                        </form>
                        <div class="console mt-3" id="backupConsole"></div>
                        <div class="console-progress">
                          <div class="progress my-1">
                            <div id="consoleProgress" class="progress-bar" role="progressbar" style="width: 0px">0%</div>
                          </div>
                        </div>
                      </div>
                      <!-- Schedule Manager -->
                      <div class="tab-pane fade" id="nav-schedule-manager" role="tabpanel">
                        <form id="scheduleForm" autocomplete="off">
                          <div class="row">
                            <div class="mb-3 col-xl-8">
                              <label for="scheduleLocalSavegame" class="form-label" data-i18n="schedule.scheduleLocalSavegame"></label>
                              <input type="text" class="form-control" id="scheduleLocalSavegame" placeholder="z.B. C:\Pfad\zum\Savegame\3ad85aea-1" required>
                            </div>
                            <div class="mb-3 col-xl-4">
                              <label for="scheduleSavegame" class="form-label" data-i18n="schedule.savegame"></label>
                              <input type="number" class="form-control" id="scheduleSavegame" min="0" max="9" readonly required>
                            </div>
                          </div>
                          <div class="mb-3">
                            <label for="scheduleSchedule" class="form-label" data-i18n="schedule.schedule"></label>
                            <input type="text" class="form-control" id="scheduleSchedule" placeholder="e.g. 03:00, 15:00 or every 7200s">
                          </div>
                          <div class="mb-3 d-flex justify-content-between align-items-center">
                            <button type="submit" class="btn btn-primary" id="scheduleSave" data-i18n="save"></button>
                            <div class="btn-group ms-auto">
                              <button type="button" class="btn btn-success" id="scheduleStart" data-i18n="start"></button>
                              <button type="button" class="btn btn-warning" id="scheduleRestart" data-i18n="restart"></button>
                              <button type="button" class="btn btn-danger" id="scheduleStop" data-i18n="stop"></button>
                              <button type="button" class="btn btn-secondary" id="scheduleClearLog" title="Log leeren">
                                <span class="bx bx-eraser"></span>
                              </button>
                            </div>
                          </div>
                        </form>
                        <div class="console mt-3" id="scheduleConsole"></div>
                      </div>
                      <!-- Options -->
                      <div class="tab-pane fade" id="nav-options" role="tabpanel">
                        <form id="optionsForm" autocomplete="off">
                          <fieldset class="row mb-4">
                            <legend data-i18n="options.ftp"></legend>
                            <div class="mb-2 col-xl-10">
                              <label for="ftpServer" class="form-label" data-i18n="options.ftpServer"></label>
                              <input type="text" class="form-control" id="ftpServer" required>
                            </div>
                            <div class="mb-2 col-xl-2">
                              <label for="ftpPort" class="form-label" data-i18n="options.ftpPort"></label>
                              <input type="number" class="form-control" id="ftpPort" value="21" required>
                            </div>
                            <div class="mb-2 col-xl-6">
                              <label for="ftpUser" class="form-label" data-i18n="options.ftpUser"></label>
                              <input type="text" class="form-control" id="ftpUser" required>
                            </div>
                            <div class="mb-2 col-xl-6">
                              <label for="ftpPass" class="form-label" data-i18n="options.ftpPass"></label>
                              <input type="password" class="form-control" id="ftpPass" required>
                            </div>
                            <div class="mb-2">
                              <label for="ftpDir" class="form-label" data-i18n="options.ftpDir"></label>
                              <input type="text" class="form-control" id="ftpDir" required>
                            </div>
                          </fieldset>
                          <fieldset class="mb-4">
                            <legend data-i18n="options.design"></legend>
                            <div class="row">
                              <div class="mb-2 col-xl-4">
                                <label for="languageSelect" class="form-label" data-i18n="options.language"></label>
                                <div class="input-group">
                                  <select class="form-select" id="languageSelect" aria-label="Language"></select>
                                  <span class="input-group-text" id="languageIcon" style="padding:0 0.5em;">
                                    <img src="assets/flags/de.svg" alt="Flag" id="languageFlag" style="width:1.5em;height:1.5em;object-fit:cover;">
                                  </span>
                                </div>
                              </div>
                              <div class="mb-2 col-xl-4">
                                <label for="themeSelect" class="form-label" data-i18n="options.theme"></label>
                                <select class="form-select" id="themeSelect">
                                  <option value="dark" data-i18n="options.dark"></option>
                                  <option value="light" data-i18n="options.light"></option>
                                </select>
                              </div>
                              <div class="mb-2 col-xl-4">
                                <label for="slideshowSelect" class="form-label" data-i18n="options.slideshow"></label>
                                <select class="form-select" id="slideshowSelect">
                                  <option value="on" data-i18n="yes"></option>
                                  <option value="off" data-i18n="no"></option>
                                </select>
                              </div>
                            </div>
                          </fieldset>
                          <button type="submit" class="btn btn-primary" data-i18n="save"></button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="assets/js/bootstrap.js"></script>
    <script src="assets/js/i18n.js"></script>
    <script src="assets/js/esm-api.js"></script>
  </body>
</html>
