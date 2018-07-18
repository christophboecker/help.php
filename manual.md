# HELP.PHP Anwender-Handbuch

> ## Inhalt
> - [Betriebsarten](#mode)
>   - [**Readme-Modus** - README.MD als Ein-Datei-Dokumentation](#doc-readme)
>   - [**Doc-Modus** - Multi-Datei-Dokumentation](#mode-doc)
> - [Konfiguration - **package.yml**](#conf)
>   - [Allgemeine Parameter](#conf-all)
>   - [Parameter für den **Readme**-Modus](#conf-readme)
>   - [Parameter für den **Doc-Modus**](#conf-doc)
>   - [Beispiele](#conf-examples)
> - [Konfiguration - Sprachdatei](#lang)
> - [Autorenanleitung für README.md](#write-readme)

<a name="mode"></a>
# Betriebarten

Mit `help.php` können unterschiedlich komplexe Dokumentationen im gleichen Look&Feel
angezeigt werden.

Voreingestellt ist der Readme-Modus. Der Doc-Modus muss in der `package.yml` aktiviert werden.


<a name="mode-readme"></a>
## Readme-Modus - README.MD als Ein-Datei-Dokumentation

Eine Readme-Datei ist i.d.R. ohnehin Teil des Projektes. In manchen Fällen ist es auch völlig
ausreichend, die nötigen Hinweise und Erklärungen in nur einer Datei abzuhandeln.

Um die Darstellung übersichtlich zu halten, bereitet `help.php` die README.md auf:

- **Navigation:** Aus den Überschriften der README.md wird eine Navigation aufgebaut, die in der linken
Spalte angezeigt wird.
- **Kapitel/Inhalt:** Ein Kapitel der README.md wird - gewissermaßen als virtuelle Datei -  in der breiteren rechten Spalte angezeigt.

Die Datei README.md wird im Root eines Addons erwartet. Sofern es sprachspezifische Versionen gibt, müssen sie
im Verzeichnis `/docs` stehen.

    «addon»/
        docs/
            REDAME.de_de.md
            README.en_gb.md
            bilddateien etc.
        README.md

 Das Verfahren erfordert eine gewisse Disziplin und Grundstruktur beim [Schreiben der README.md](#write-readme), damit die
 Inhalte optimal dargestellt werden.


<a name="mode-doc"></a>
## Doc-Modus - Multi-Datei-Dokumentation

Im Doc-Modus werden komplexe Dokumentationen aufbereitet, die aus mehreren Dateien bestehen. I.d.R.
werden einzelne Kapitel als eine Datei geführt. Ein zentrales Menü erlaudt den raschen Kapitelwechsel.

- **Navigation:** Die Navigation wird als Markdown-Datei bereitgestellt. Sofern nicht anders konfiguriert ist es
die Datei `main_navi.md`. Die Navigation wird in der linken Spalte angezeigt.
- **Kapitel/Inhalt:** Der jeweilige Text eines Kapitels wird in der breiteren rechten Spalte angezeigt.

Die Dateien werden im Verzeichnis `docs` in sprachspezifischen Unterverzeichnissen abgelegt.

    «addon»/
        docs/
            de_de/
                main_navi.md
                main_intro.md
                kapitel1.md
                ...
                bilddateien (sprachspezifisch)
            en_gb/
                ...
        bilddateien (sprachneutral)


<a name="conf"></a>
# Konfiguration - **package.yml**

Die Anpassung an verschiedene Anforderungen wird in der `package.yml` eines Addons
vorgenommen. Neben allgemeinen Parametern (für beide Betriebsarten) gibt es auch spezifische je Betriebsart.

Das System an sich kann sehr flexibel eingesetzt werden. Insbesondere im Doc-Modus ist das von Vorteil.

Die Parameter sind in einer Sektion `help:` zusammengefasst. Die Sektion kann mehrfach vorkommen:

- alle allgmeine Konfiguration für das Addon
- je Seite (`page:` bzw. `pages:`)

Die seitenspezifische Konfiguration hat Vorrang vor der Addon-Konfiguration. Sind keine Parameter angegeben, werden die System-Defaults herangezogen. Hier eine Übersicht:

|Parameter|Gruppe|default|Verwendung|
|---|---|---|---|
|[mode](#conf-parameter-mode)||readme|Auswahl der Betriebsart('docs' oder 'readme')|
|[fallback](#conf-parameter-fallback)||«leer»|Fallback-Language|
|[title](#conf-parameter-title)||«leer»|Seitentitel|
|[repository](#conf-parameter-repo)||«leer»|Link zum Text in einem Repository wie GitHb|
|[markdown_break_enabled](#conf-parameter-mbe)||0|Umgang mit Zeilenumbrüchen in Markdown|
|[level](#conf-parameter-level)|readme|1|Steuert den Anzeigeumfang durch Kapitelauswahl (1= # und ##)|
|[scope](#conf-parameter-scope)|readme|section| sollen alle Kapitel einzeln behandelt oder auf der oberen Ebene zusammengefasst werden ('chapter')|
|[navigation](#conf-parameter-mnavi)|docs|main_navi.md|Anzuzeigende Navigationsdatei|
|[content](#conf-parameter-cont)|docs|main_intro.md|erste anzuzeigende Textdatei|

<a name="conf-all"></a>
## Allgemeine Parameter

<a name="conf-parameter-mode"></a>
### mode

Die der Auswahl der Betriebsart. Die Details zu den Betriebsarten sind in einem separaten [Kapitel](#mode) beschrieben.

Zulässing sind `mode: readme` und `mode: docs`, wobei jeder Wert anders als 'docs' als 'readme'
interpretiert wird.

```
help:
    mode: readme
```


<a name="conf-parameter-fallback"></a>
### fallback

Per Default wird immer versucht, die Texte in der für den User gültigen i18n-Sprache anzuzeigen. Der
akzulle wird wird mit `rex_i18n::getLocale()` abgerufen. Sollten keine Texte für diese Sprache gefunden
werden sucht das System zunächst Rextten, die in der mit `fallback: «sprache»` angegebenen Sprache
verfasst sind. Existiert auch der nicht, wird das in der REDAXO-Instanz konfigurierte Fallback herangezogen
(`rex::getProperty('lang_fallback', [])`).

```
help:
    fallback: pt_br
```

Angenommen, die aktuelle Sprache wäre "de_de" und der im System eingestellte Fallback "en_gb,de_de", ergibt
sich Reihenfolge der Sprachen zu:

- de_de
- pt_br
- en_gb

Im Readme-Modus wird danach auf die README.MD im AddOn-Root zurückgegriffen. Im Docs-Modus wird danach
die Fehlereldung ([file_not_found](#lang)) angezeigt

<a name="conf-parameter-title"></a>
### title

In den meisten Fällen werden die Unterseiten eines Addon über ein Script `«addon»/pages/index.php`
aufgerufen, dass sowohl einen Seitentitel als auch die
jeweilige Seite aufbaut. Zum Seitentitel gehört auch das AddOn-Menü.

```
echo rex_view::title(rex_i18n::msg('title'));
include rex_be_controller::getCurrentPageObject()->getSubPath();
```
bzw. in der neuen Notation

```
echo rex_view::title(rex_i18n::msg('title'));
rex_be_controller::includeCurrentPageSubPath();
```
Es gibt aber auch AddOns, die den Titel nicht mit anzeigen, sondern je Seite individuelle Anzeigen aufbauen. In dem Fall wäre die Help-Seite "kopflos". Als Notnagel ist es für diesem Fall möglich, einen Titel zu setzen.

```
page:
    title: 'translate:«addon»'
    subpages:
        seite:
            subPath: help.php
            title: 'Anleitung'
            help:
                mode: docs,
                title: 'translate:«addon»'

pages:
    anderesaddon/seite:
        subPath: help.php
        title: 'Anleitung'
        help:
            mode: docs
            title: 'translate:«addon»'
```

> Der zweite Fall (pages) funktioniert so zur Zeit nur bei einigen AddOn, nicht z.B. bei SKED oder YForm. Der Grund ist die Notation in der
Datei `pages/index.php`. Dort werden die Sub-Pages noch in der alten Notation eingebunden. Mit der
"neuen" Schreibweise, auf die noch nicht alle AddOn umgestellt sind, klapt es wie erwartet


Es sollte aber wirklich nur ein Notnagel sein.

<a name="conf-parameter-repo"></a>
### repository

Über dne Parameter wird eine URL festgelegt, unter der das AddOn in einem Repository (z.B. GitHub) zu finden ist.

Die Stelle, an der der Dateiname eingetragen wird, ist mit %% markiert.

Beispiel:
```
page:
    subpages:
        seite:
            subPath: help.php
            title: 'Anleitung'
            help:
                mode: docs
                repository: https://github.com/FriendsOfREDAXO/focuspoint/%%
```
Der Dateiname, der statt %% in den Link eingebaut wird, enthält immer die zugehörige Verzeichnisstruktur 
im Root des AddOn.

- docs/de_de/abc.md
- docs/README.de_de.md
- README.md



<a name="conf-parameter-mbe"></a>
### markdown_break_enabled

Textdateien werden im Format "Markdown" erwartet und mit dem Vendor-Tool 'ParsedownExtra' in HTML umgewandelt.
REDAXO bietet dafür eine Klasse `rex_markdown` an, die unveränderlich den Parameter `markdown_break_enabled` auf `true` setzt.

Jedes `return` am Ende eine Zeile innerhalb eines Absatzes wird als `<br>` in den HTML-Code übernommen.

Das ist nicht immer sinnvoll. Daher wird in der `help.php` ermöglicht, den Parameter nicht auf `true` zu setzen,
sondern den Default-Wert von ParsedownExtra beizubehalten.

Konkreter gesagt: `help.php` arbeitet so, wie es in ParsedownExtra vorgesehen ist. Wenn das Verhalten
von `rex_markdown` benötigt wird, kann es durch den Parameter `markdown_break_enabled` eingeschaltet werden.
Ein Wert `markdown_break_enabled: 1` schaltet das Verhalten ein, jeder andere Wert belässt den Default.

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

<a name="conf-readme"></a>
## Parameter für den **Readme**-Modus

<a name="conf-parameter-level"></a>
### level

Der Parameter erlaubt die einfache Anpassung des Ausgabeverhaltens an unterschiedliche README-Strukturen.
Es gibt zwei grundlegende Varianten:

1) Nur eine Überschrift der obersten Ebene (# headline) als Gesamttitel und darunter mehrere Kapitel der
Ebene 2 (## Kapitel x), wiederum mit Unterkapiteln (### Subkapitel x.y).

2) Alle Kapitel sind konsequent auf der obersten Ebene (# Kapitel x), wiederum mit Unterkapiteln (## Subkapitel x.y).

Im ersten Fall kann mit `level: 2` veranlasst werden, das der vor dem ersten Ebene-2-Kapitel stehende
Teil ignoriert wird.

> Aber vorsicht! Es werden dann konsequent alle Teile der Ebene 1 ausgeblendet. Besser ist es, die
Readme von vorne herein gemäß Variante 2 zu schreiben.  

Mit `level: 0` erreicht man, dass die Navigation gar nicht angezeigt wird - sinnvoll z.B. bei sehr kleinen
README.md

<a name="conf-parameter-scope"></a>
### scope



<a name="conf-doc"></a>
## Parameter für den **Doc**-Modus

<a name="conf-parameter-navi"></a>
### navigation

<a name="conf-parameter-cont"></a>
### content

<a name="conf-examples"></a>
## Beispiele

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




<a name="lang"></a>
# Konfiguration - Sprachdatei

An vier Stellen sind Verweise auf die jeweilige lang-Datei des Addons (`$this->18n(...);`);

| lang-Eintrag | Verwendung |
| ------------ | ---------- |
| docs_navigation | Titel der Navigatonsspalte (z.B. "Navigation")  |
| docs_content | Titel der Inhaltsspalte (z.B. "Inhalt") |
| docs_not_found | Fehlermeldung wenn eine Datei auch durch Fallback nicht gefunden wurde |
| docs_repository_button | Text für den Bearbeiten-Button im [Repository-Link](#conf-parameter-repo)   |

<a name="write-readme"></a>
# Autorenanleitung für README.md

Die README.md wird kapitelweise aufgeschlüsselt. Jedes Kapitel der beiden oberen Ebenen wird zu
einem Eintrag in der Navigation.

Hier ein Beispiel:

```
# Addon-Name

## Übersicht

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

## Features

* erstens
* zweitens
* drittens

## Installation

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

# Erste Schritte

## Setup

Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.

## Konfiguration
...
```

Daraus wird eine Navigation im Markdown-Format erzeugt

```
- Addon-Name
 - Übersicht
 - Features
 - Installation
- Erste Schritte
 - Setup
 - Konfiguration
```

> to be continued
