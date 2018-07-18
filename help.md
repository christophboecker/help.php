# HELP.PHP für REDAXO-Addons

(under construction)


## Zielsetzung

Hilfe und Dokumentation in unterschiedlichen Granulierungen standardisiert bereitstellen

- einfach zu nutzen (aufbereitete README.md)
- komplexe Dokumentation wo erforderlich ( «addon»/doc )
- flexibel in der Handhabung ( doc-Variante mit Kontext-bezogenem Umfang)
- konfigurierbar (package.yml)

> ## Inhalt
> - [URL](#url)
> - [Konfiguration mit `package.yml`](#yml)
> - [Sprachdateien](#lang)
> - [Verzeichnis `docs`](#docsdir)
> - [Sonstiges](#stuff)
> - [Tests](#tests)


Zeigt Multifile-Dokumentationen/Hilfen/Handbücher aus dem Verzeichnis

```
  «addon»/docs
    sprachübergreifende Dateien (z.B. Bilder)
    README.«lang».md
    «lang»
      sprachbezogene Dateien
```

Zeigt die zentrale oder sprachbezogene README.md aufbereitet an.

Berücksichtigt die aktuelle Spacheinstellung inkl. Fallback.

Flexibel einbindbar, Konfiguration via package.yml


## Vorteile:

- alle bisher bestehenden Dokumentationslösungen für Addons, die auf dem Docs-PlugIn beruhen,
können sofort umgestellt werden (vermutlich ohne oder nur mit sehr geringem Anpassungsaufwand)
- README.md, falls keine erweiterte Dokumentation vorgesehen
- Kompatibel zu GitHub (Readme).


<a name="url"></a>
## URL

Die jeweilige URL, die sich aus dem Aufrufkontext ergibt, bleibt erhalten.

### Texte (doc_file=..)

Betrifft die Markup-Abschnitte `[titel](datei)`.

Mit der URL wird die Ausgangsseite angezeigt.

**docs-Modus:** [Navigationsspalte](#yml-navi) mit 'main_navi.md' und [Inhaltsspalte](#yml-content) mit 'main_intro.md'.
**readme-Modus:** README.md und darus das erste Kapitel.

Falls der Text Links zu weiteren Dateien oder Unterkapiteln enthält, werden Links erzeugt, die die
URL um den Parameter `doc_file` erweitern.

**docs-Modus:** doc_file=dateiname

Sprachverzeichnisse müssen nicht angegeben werden, das regelt `help.php` eigenständig im Rahmen der Fallback-Verfahren.

**readme-Modus:** doc_file=«kapitelnummer».

Die Kapitelnummer ergibt sich aus den identifizierten Nummer der Level2/3-Kapitel.

### Bilder (doc_image=..)

Betrifft die Markup-Abschnitte `![titel](bilddatei)`.

Verlinkt die angegebene Grafik aus dem Dokumentenverzeichnis. Sprach-Fallback wird berücksichtigt.
Kein Dokumentenverzeichnis, keine Bilder auf diesem Wege.


<a name="yml"></a>
## Konfiguration mit `package.yml`


Als Fallback sind im Code Parameter hinterlegt, auf die `help.php` zurückgreift, wenn keine andere
Konfiguration gefunden wurde. Jeder dieser Parameter kann in der 'package.yml'
für das AddOn allgemein oder je Seite umdefiniert werden.

Die Reihenfolge ist:

1. Seitenspezifischer Parameter
2. AddOn-spezifischer Parameter
3. Allgemeiner Parameter in der `help.php`

| Parameter | default         | Verwendung  | Anmerkung  |
|-----------|-----------------|---|---|
| [mode](#yml-mode)      | docs            | docs oder readme - Auswahl der Anzeigevariante   |   |
| [fallback](#yml-lang)  | «systemsprache» | Die erste Fallback-Sprache  |   |
| [navigation](#yml-navi) | main_navi.md   | Name der Navigationsdatei  | nur für "docs" |
| [content](#yml-content)   | main_intro.md   | Name der ersten Inhaltsdatei (Intro)  | nur für "docs" |
| [markdown_break_enabled](#yml-mbe) | 0  | 0 der 1 - Parameter für ParsedownExtra  |   |
| [title](#yml-title) |      «leer»         | Seitentitel  | Nur in Notfällen verwenden  |


<a name="yml-mode"></a>
### Paramter "mode"

Per default wird versucht, im docs-Mode zu arbeiten. Dafür ist der Ordner `«addon»/docs/` notwendig.
Existiert er nicht, wird auf den readme-Modus umgeschaltet.

Beispiel für eine `package.yml`:

```
package:  myaddon
version:  '1.2.3'
author: Friends Of REDAXO
supportpage: https://github.com/FriendsOfREDAXO/focuspoint

help:
    mode: readme

page:
    title: 'translate:title'
    subpages:
        config:
            title: 'translate:config_page'
        doku:
            subPath: help.php
            title: 'translate:doku_page'
            help:
                mode: docs
                navigation: _navi.md
                content: _intro.md

pages:
    media_manager/myaddon:
        subPath: help.php
        title: 'translate:doku_page_mediamanager'
        icon: rex-icon fa-info-circle
        help:
            mode: docs
            navigation: mm_navi.md
            content: mm_intro.md
```

Mit dieser `package.yml` werden drei verschiedene Seiten über `help.php` erzeugt:

- Aufgerufen über die Addon-Verwaltung (Hilfe) wird die README.md angezeigt.
- Aufgerufen innerhalb des Addons ( subpage "doku") wird auf den Doku-Modus geschaltet. Als Navigation wird "\_navi.md" und als Starttext "\_intro.md" herangezogen.
- Die in das Addon "Media Manager" eingehängte Version arbeitet ebenfalls im Docs-Modus, aber mit anderen Startdateien("mm\_navi.md", "mm\_intro.md")


<a name="yml-lang"></a>
### Parameter "fallback"

Ermöglicht die Angabe einer eigenen Fallback-Sprache zusätzlich zu den Fallback-Sprachen, die ohnehin
im System hinterlegt sind.

1. Eingestellte Spache, die auch rex_i18n benutzt
2. per `fallback: ...` eingestellte seitenspezifiche Sprache
3. per `fallback: ...` eingestellte AddOn-Sprache
4. im System eingestellte Fallback-Sprachen (`rex::getProperty('lang_fallback')`)


<a name="yml-navi"></a>
### Parameter "navigation"

**relevant für den docs-Mode**

Der Name der Datei, die als Menü angezeigt wird. Unterschiedlichen Seiten können eigene Navigationen
zugewiesen werden. Das ermöglicht die Inhalte je nach Kontext anzupassen.


<a name="yml-content"></a>
### Parameter "content"

**relevant für den docs-Mode**

Der Name der Datei, die initial angezeigt wird. Folgeseiten sind über den URL-Parameter "doc_file"
adressiert.



<a name="yml-mbe"></a>
### Parameter "markdown_break_enabled"

Textdateien werden im Format "Markdown" erwartet und mit dem Vendor-Tool ParsedownExtra in HTML umgewandelt. REDAXO bietet dafür eine Klasse `rex_markdown` an, die unveränderlich den Parameter `markdown_break_enabled` auf `true` setzt.

Jedes `return` am Ende eine Zeile innerhalb eines Absatzes wird als `<br>` in den HTML-Code übernommen.

Das ist nicht immer sinnvoll. Daher wird in der `help.php` ermöglicht, den Parameter nicht auf True zu setzen, sondern den Default-Wert von ParsedownExtra beizubehalten.

Konkreter gesagt: `help.php` arbeitet so, wie es in ParsedownExtra vorgesehen ist. Wenn das Verhalten von `rex_markdown` benötigt wird, kann es durch den Parameter
`markdown_break_enabled` eingeschaltet werden.

```
page:
    title: 'translate:«addon»'
    subpages:
        seite: { title: 'translate:seite' }
        help:
            subPath: help.php
            title: 'Anleitung'
            help:
                mode: docs,
                markdown_break_enabled: 1
```

<a name="yml-title"></a>
### Parameter "title"

In den meisten Fällen werden die Unterseiten eines Addon über ein Script `«addon»/pages/index.php` aufgerufen, dass sowohl einen Seitentitel als auch die
jeweilige Seite aufbaut. Zum Seitentitel gehört auch das AddOn-Menü.

```
echo rex_view::title(rex_i18n::msg('sked_title'));
include rex_be_controller::getCurrentPageObject()->getSubPath();
```

Es gibt aber auch AddOns, die den Titel nicht mit anzeigen, sondern je Seite individuelle Anzeigen aufbauen. In dem Fall wäre die Help-Seite "kopflos". Als Notnagel ist es für diesem Fall möglich, einen Titel zu setzen.

```
page:
    title: 'translate:«addon»'
    subpages:
        seite: { title: 'translate:seite' }
        help:
            subPath: help.php
            title: 'Anleitung'
            help:
                mode: docs,
                title: 'translate:«addon»'
```

Es sollte aber wirklich nur ein Notnagel sein.


<a name="lang"></a>
## Sprachdateien (xx_xx.lang)

An drei Stellen sind Verweise auf die jeweilige lang-Datei des Addons (`$this->18n(...);`);

| lang-Eintrag | Verwendung |
| ------------ | ---------- |
| docs_navigation | Titel der Navigatonsspalte (z.B. "Navigation")  |
| docs_content | Titel der Inhaltsspalte (z.B. "Inhalt") |
| docs_not_found | Fehlermeldung wenn eine Datei auch mit Fallback nicht gefunden wurde |


<a name="docsdir"></a>
## Verzeichnis `docs`

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


<a name="stuff"></a>
## Sonstiges

### Einbinden in die eigene Addon-Seitn

    page:
        ....
        subpages:
            ....
            help:
                title: 'translate:documentation'
                subPath: help.php


### Einbinden in andere Addon-Seiten

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

In manchen Fällen gibt es Dateien, die definitv nicht sprachabhängig sind. Sie müssen daher nicht im jeweiligen Sprachverzeichnis stehen und mehrfach vorhanden sein, sondern können im Docs-Root stehen. Die Datei wird zunächst im Sprachverzeichnis gesucht, dann im Docs-Root, dann in den Fallback-Sprachverzeichnissen. Beispiel:

    «addon»/docs/«sprache»/bild.jpg
    «addon»/docs/bild.jpg
    «addon»/docs/«fallback-sprache»/bild.jpg
    Fehler 404

### Regeln für die README

Die Readme wird eingelesen und zerlegt. Aus den Überschriften Level 2 und 3 wird die Navigation erzeugt. Der zum aktuell angeforderten (oder ersten) L2-Kapitel gehörende Teil der README wird als Content angezeigt.

Alles vor dem ersten L2 sollte daher nur die L1- Überschrift sein; der Teil wird ignoriert.  Cest la vie.
Vorhandene interne Links und Anker werden umfänglich unterstützt.

<a name="tests"></a>
## Tests:

getestet mit den Dokumentenverzeichnissen von [YForm](https://github.com/yakamara/redaxo_yform_docs/tree/0acc12f225649ff072146a752fe06d5618a780bf) und [SKED](https://github.com/FriendsOfREDAXO/sked/tree/master/plugins/documentation/docs/de_de).

Die Dokumentenverzeichnisse (docs) wurden in das Addon-Root kopiert. In der `package.yml`wurde der Aufruf im docs-Modus konfiguriert:

**sked:**

    help:
        mode: docs
        navigation: main_navi.md
        content: main_intro.md

**YForm**

    help:
        mode: docs
        navigation: main_navi.md
        content: main_intro.md

    page:
        title: 'translate:yform'
        pjax: false
        icon: rex-icon fa-hand-spock-o
        subpages:
            overview: { title: 'translate:overview', perm: admin }
            help:
                subPath: help.php
                title: 'Manual'
                help:
                    title: 'translate:yform'
