# HELP.PHP für REDAXO-Addons

(under construction)


## Zielsetzung

Hilfe und Dokumentation in unterschiedlichen Granulierungen standardisiert bereitstellen

- einfach zu nutzen (aufbereitete README.md)
- komplexe Dokumentation wo erforderlich ( «addon»/doc )
- flexibel in der Handhabung ( doc-Variante mit Kontext-bezogenem Umfang)
- konfigurierbar (package.yml)

Zeigt Multifile-Dokumentationen/Hilfen/Handbücher aus dem Verzeichnis

```
  «addon»/
    docs/
      sprachübergreifende Dateien (.md und Bilder)
      «lang»/
        sprachbezogene Dateien (.md und bilder)
```

Zeigt die zentrale oder sprachbezogene README.md aufbereitet an.

```
  «addon»/
    README.«lang».md
    README.md
```

Berücksichtigt die aktuelle Spacheinstellung inkl. Fallback.

Flexibel einbindbar, Konfiguration via package.yml

Splittet README-Dateien in Kapitel auf (virtuelle .md), generiert die Navigation aus den Überschriften


## Vorteile:

- alle bisher bestehenden Dokumentationslösungen für Addons, die auf dem Docs-PlugIn beruhen,
können oft ohne bzw. mit sehr geringem Anpassungsaufwand umgestellt werden
- README.md, falls keine erweiterte Dokumentation vorgesehen

## bisherige Unverträglichkeiten

### Markdown

Alle Manipulationen finden im Markdown statt. Es wird jeweils Markdown-Code generiert, kein Mischcode aus HTML und Markdown. 
Einige AddOn haben in der Intro der Doku folgenden Code, der tadellos mit den bestehenden DOC-Plugins harmoniert. 
```
<p style="text-align:center">
![FriendsOfREDAXO](for.png)
</p>
```
Der Grund ist einfach: der Bilderlink wird schon vor der Markdown-Umwandlung in einen HTML-Link umgesetzt. Da `help.php` auf Markdown-Ebene arbeitet, wird 'parseDownExtra' den im p-Tag eingeschlossenen Code nicht umwandeln.

Empfehlung: 
```
![FriendsOfREDAXO](for.png){.center-block}
```

### pages/index.php

In den meisten Fällen werden die Unterseiten eines Addon über ein Script `«addon»/pages/index.php`
aufgerufen, dass sowohl einen Seitentitel als auch die jeweilige Seite aufbaut. 

```
echo rex_view::title(rex_i18n::msg('title'));
include rex_be_controller::getCurrentPageObject()->getSubPath();
```
bzw. in der neuen Notation

```
echo rex_view::title(rex_i18n::msg('title'));
rex_be_controller::includeCurrentPageSubPath();
```

Grundsätzlich ist ja auch möglich, dass sich ein AddOn mit Seiten in ein anderes AddOn einhängt. Das wird von `help.php` unterstützt.
Aber nur für AddOn, die nach der neuen Notation Sub-Seiten einbinden, werden die seitenindividuellen Parameter gelesen. 

```
pages:
  addon_b/seite_von_a:
    title: 'seite_von_a'
    subPath: help.php
    help:
      mode: docs
      navigation: b_navi.php
      content: b_content.php
        addon_b/seite_von_a:
  addon_c/seite_von_a:
    title: 'seite_von_a'
    subPath: help.php
    help:
      mode: docs
      navigation: c_navi.php
      content: c_content.php
```
Wenn Addon "B" nach der alten Notation arbeitet, wird die README.MD von Addon "B" angezeigt (Default, weil keine individuellen Parameter gefunden). Wenn AddOn "C" nach der neuen Notation arbeitet, wird ein für "C" festgelegter Subsetz der A-Docs angezeigt.

Vorgeschlagene Lösung: index.php anpassen; Addons allgemein auf Stand der Technik bringen.
