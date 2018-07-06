# HELP.PHP für REDAXO-Addons

## Zielsetzung

Hilfe und Dokumentation in unterschiedlichen Granulierungen standardisiert bereitstellen

- einfach zu nutzen (aufbereitete README.md)
- komplexe Dokumentation wo erforderlich ( «addon»/doc )
- flexibel in der Handhabung ( doc-Variante mit Kontext-bezogenem Umfang)
- konfigurierbar (package.yml)

Zeigt Multifile-Dokumentationen/Hilfen/Handbücher aus dem Verzeichnis 

  «addon»/docs
    sprachübergreifende Dateien (z.B. Bilder)
    README.«lang».md
    «lang»
      sprachbezogene Dateien

Zeigt die zentrale oder sprachbezogene README.md aufbereitet an.

Berücksichtigt die aktuelle Spacheinstellung inkl. Fallback. 

Flexibel einbindbar, Konfiguration via package.yml


## Vorteile:

- alle bisher bestehenden Dokumentationslösungen für Addons, die auf dem Docs-PlugIn beruhen,
können sofort umgestellt werden (vermutlich ohne oder nur mit sehr geringem Anpassungsaufwand)
- README.md, falls keine erweiterte Dokumentation vorgesehen
- Kompatibel zu GitHub (Readme).


## Lösungsdetails:

### Verzeichnis `docs`

Im Verzeichnis können sowohl die erweiterten README als auch die Dateien der komplexen Dokumentation 
abgelegt werden. 

|   |   |   |   |
|---|---|---|---|
|«addon»/docs/|Stammverzeichnis|README.«language».md|sprachspezifische README-Dateien|
|||«datei»|Dateien einer Dokumentation, die nicht sprachspezifisch sind (meist Bilder)|
|«addon»/docs/«language»|Sprachverzeichnis|_intro.md|Default-Text|
|||_navi.md|Navigationsdatei/Menü|
|||«page»_intro.md|Default-Text|
|||«page»_navi.md|Navigationsdatei/Menü|
|||«datei»|Dokumentationsdatei|

### Konfiguration über `package.yml`

**Einfach (README) oder komplexe Dokumentation**

    help: 
      mode: readme | docs     # Betriebart: Fallback auf readme falls kein «addon»/docs
      language: de_de         # Fallback-Language bevor das System-Fallback geift
      navigation: _intro.md   # Navigation (linke Spalte)
      content: _intro.md      # initiale Content-Datei (rechte Spalte)

Mit `help: docs` wird der Modus "komplexe Dokumentation" angefordert. Ansonsten wird die README-Variante genommen.

**Einbinden in die eigene Addon-Seite**

    page:
        ....
        subpages:
            ....
            help:
                title: 'translate:documentation'
                subPath: help.php


**Einbinden in andere Addon-Seiten**

Hier gezeigt am Beispiel des Media-Managers

    pages:
        # media_manager/overview muss zu einem Untermenü werden, wenn fp sich einklinkt
        # daher den Originaltext zum ersten Menüpunkt machen.
        media_manager/overview/overview:
                subPath: ../../../media_manager/pages/overview.php
                title: 'Media Manager'
                icon: rex-icon fa-info-circle
        media_manager/overview/focuspoint:
                subPath: help.php
                title: 'translate:focuspoint_doc'
                icon: rex-icon fa-info-circle
                help: { navigation: , content: mm.md }

Die Help-Seite wird als Unterseite beim Media-Manager eingehängt. Es wird keine Navigation angezeigt, nur die Seite mm.md (falls im doc-Modus)

> `navigation` ist hier leer. Daher wird keine Navigationsspalte angezeigt, nur der Inhalt, dafür aber in voller Breite

### Language-Fallback

1. nutze die aktulle Spracheinstellung
2. nutze den Wert aus help:language:
3. nutze die Werte aus rex::getProperty('lang_fallback', []) in der angegebenen Reihenfolge

### Datei-Fallback

In manchen Fällen gibtes Dateien, die definitv nicht sprachabhängig sind. Die müssen nicht im Sprachverzeichnis stehen, sondern können im
Docs-Rot stehen. Die Datei wird zunächst im Sprachverzeihnis gesucht, dann im Docs-Root, dann in den Fallback-Sprachverzeichnissen. Beispiel:

    «addon»/docs/de_de/bild.jpg
    «addon»/docs/bild.jpg
    «addon»/docs/en_gb/bild.jpg
    fehlermeldung

### xx_xx.lang

An drei Stellen sind verweise auf die jeweilige lang-Datei des Addons (`$this->18n(...);`);

| lang-Eintrag | Verwendung |
| ------------ | ---------- |
| docs_navigation | Titel der Navigatonsspalte (z.B. "Navigation")  |
| docs_content | Titel der Inhaltsspalte (z.B. "Inhalt") |
| docs_not_found | Fehlermeldung wenn eine Datei auch mit Fallback nicht gefunden wurde |

## Regeln für die README

Die Readme wird eingelesen und zerlegt. Aus den Überschriften Level 2 und 3 wird die Navigation erzeugt. Der zum aktuell angeforderten (oder ersten) L2-Kapitel gehörende Teil der README wird als Content angezeigt.

Alles vor dem ersten L2 sollte daher nur die L1- Überschrift sein; der Teil wird ignoriert.  Cest la vie.
Vorhandene interne Links und Anker werden umfänglich unterstützt.

## Tests:

getestet mit den Dokumentenverzeichnissen von [YForm](https://github.com/yakamara/redaxo_yform_docs/tree/0acc12f225649ff072146a752fe06d5618a780bf) und [SKED](https://github.com/FriendsOfREDAXO/sked/tree/master/plugins/documentation/docs/de_de).

Die Dokumentenverzeichnisse (docs) wurden in das Addon-Root kopiert. In der `package.yml`wurde der Aufruf im docs-Modus konfiguriert:

    help:
        mode: docs
        navigation: main_navi.md
        content: main_intro.md

 
