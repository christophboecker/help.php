<?php
/**
 *  This file is part of the REDAXO-AddOn "....".
 *
 *  @author      FriendsOfREDAXO @ GitHub <https://github.com/FriendsOfREDAXO/....>
 *  @version     x.y
 *  @copyright   FriendsOfREDAXO <https://friendsofredaxo.github.io/>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  ------------------------------------------------------------------------------------------------
 *  package.yml
 *
 *          ...
 *          help:
 *              mode: docs | readme         # default: docs
 *              fallback: «locale»          # primary fallback-language
 *              content: main_intro.md      # default-page for content (docs only)
 *              navigation: main_navi.md    # default-page for navigation (docs only)
 *              markdown_break_enabled: 0|1 # default is false (parsedown-standard)
 *              title:                      # optional page title (try to avoid)
 *
 * «addon/docs
 *      README.«locale».md          # language specific README (readme-mode)
 *      «file(s)»                   # language independent (image-)files (docs-mode)
 *      «locale»                    # language specific files (docs-mode)
 *          main_intro.md
 *          main_navi.md
 *          «file(s)»
 *

 */

// label for specific URL-parameters
const URL_FILE = 'doc_file';
const URL_IMAGE = 'doc_img';

// parameters in package.yml
const YML_SECTION = 'help';
const YML_MODE = 'mode';
const YML_LANG = 'fallback';
const YML_NAVI = 'navigation';
const YML_CONT = 'content';
const YML_MDBE = 'markdown_break_enabled';
const YML_HEAD = 'title';

// others
const PATH_DOC = 'docs/';
const DEF_MODE = 'readme';
const DOC_MODE = 'docs';
const DEF_NAVI = 'main_navi.md';
const DEF_CONT = 'main_intro.md';
const DEF_MDBE = '0';
const MDBE_ON = '1';
const DEF_HEAD = '';


//## helpfile
// read defaults and configuration
$defaults = array_merge (
    [YML_MODE=>DEF_MODE,YML_LANG=>rex_i18n::getLocale(),YML_NAVI=>DEF_NAVI,YML_CONT=>DEF_CONT,YML_MDBE=>DEF_MDBE,YML_HEAD=>DEF_HEAD],
    (array)$this->getProperty( YML_SECTION, [] )
);

// override defaults with page-specific configuration
$page = rex_request('page','string');

// page = addon/.../...
if( $this->getName() == rex_be_controller::getCurrentPagePart(1) ) {
    $i = 1;
    $pageDefaults = (array)$this->getProperty('page');
    while ( ($x=rex_be_controller::getCurrentPagePart(++$i)) !== null) {
        $pageDefaults = (array)$pageDefaults['subpages'][$x];
    }
// or somewhere else in the "pages"
} elseif( isset( $this->getProperty('pages')[$page] )) {
    $pageDefaults = (array)$this->getProperty('pages')[$page];
} else {
    $pageDefaults = [];
}
$defaults = array_merge (
    $defaults,
    isset($pageDefaults[YML_SECTION]) ? (array)$pageDefaults[YML_SECTION] : []
);

//## helpfile
// language and fallback
$language = $defaults[YML_LANG];
$fallback = array_merge( [$defaults[YML_LANG]], rex::getProperty('lang_fallback', []) );
$fallback = array_values(array_unique(array_diff( $fallback, [$language,''] )));

//## helpfile
// directory names
$docPath = $this->getPath( PATH_DOC );
$langPath = $docPath . "$language/";
$hasDocDir = is_dir( $docPath );

//## helpfile
// if image-call: send image
// relevant only in docMode -> assure language-fallback
if ( ($image = rex_request( URL_IMAGE, 'string')) != '' )
{
    ob_end_clean();
    if( is_file($imagefile="$langPath$image") || is_file ( $imagefile="$docPath$image") ) {
        rex_response::sendFile( $imagefile, mime_content_type( $imagefile ) );
        exit;
    } else {
        foreach( $fallback as $v ) {
            if( is_file($imagefile="$docPath$v/$image" ) ) {
                rex_response::sendFile( $imagefile, mime_content_type( $imagefile ) );
                exit;
            }
        }
    }
    header( 'HTTP/1.1 Not Found' );
    exit;
}

// set File_not_found-Message
$file_not_found = $this->i18n('docs_not_found');

// generate the fixed part of the link-url
parse_str($_SERVER['QUERY_STRING'], $url);
$url = array_diff_key( $url, [URL_FILE=>0,URL_IMAGE=>0] );
$url = rex_url::backendController( $url, false );
$urlDoc = $url . '&' . URL_FILE . '=';
$urlImg = $url . '&' . URL_IMAGE . '=';


// detect operation modus: pure readme vs. multifile-documentation
// no docPath? switch to readme-Mode

// Documentation-Mode ------------------------------------------------------------------------------
if( $defaults[YML_MODE] === DOC_MODE && $hasDocDir )
{
    // correct language-directory
    // if none found: keep existing value
    if( !is_dir( $langPath ) ) {
        foreach( $fallback  as $v ) {
            if( !(is_dir( $dir="$docPath$v/")) ) continue;
            $langPath = $dir;
            break;
        }
    }

    // get filenames
    $navigationFile = $defaults[YML_NAVI];
    $contentFile = rex_request( URL_FILE, 'string', $defaults[YML_CONT] );

    // read content- and navigation-file
    $content = rex_file::get( "$langPath$contentFile" );                // addon/docs/de_de/xy.md
    if( !$content ) $content = rex_file::get( "$docPath$contentFile" ); // addon/docs/xy.md
    if( !$content ) $content = $file_not_found . " $docPath$contentFile";
    $navigation = rex_file::get( "$langPath$navigationFile" );
    if( !$navigation ) $navigation = rex_file::get( "$docPath$navigationFile" );

    // Mark currently selected Chapter in the Menü
    $navigation = preg_replace( '/\[(.*)\]\(('.preg_quote ($contentFile).'.*)\)/','[$1]($2){.bg-primary}',$navigation);

    // set system-internal links
    //  1) get system-internal filenames
    $files = [];
    $iterator = rex_finder::factory( $docPath );
    foreach( $iterator as $v ) $files[$v->getFilename()] = $v;
    $iterator = rex_finder::factory( $langPath );
    foreach( $iterator as $v ) $files[$v->getFilename()] = $v;

    //  2) exchange links for files found
    foreach( $files as $k=>$v) {
        $search = '/(!)?\[(.*)\]\(('.preg_quote ( $k ).'.*)\)/';
        $replace = function($m) use ($urlDoc,$urlImg){ return " {$m[1]}[{$m[2]}](".($m[1]=='!'?$urlImg:$urlDoc)."{$m[3]})"; };
        $content = preg_replace_callback( $search, $replace, $content );
        $navigation = preg_replace_callback( $search, $replace, $navigation );
    }


// Readme-Mode ------------------------------------------------------------------------------
} else {
    // for hooks
    $marker = 'readme-md-';

    // read content-file with language-fallback
    $filename="{$docPath}README.$language.md";
    if( !is_file( $filename ) ) {
        $filename = $this->getPath( 'README.md' );
        foreach( $fallback as $v ) {
            if( is_file( $f="{$docPath}README.$v.md" ) ) {
                $filename = $f;
                $language = $v;
                break;
            }
        }
    }
    $content = rex_file::get( $filename );
    // treat empty file as "not found"
    if( $content ) {
        $content = PHP_EOL.$content;
    } else {
        $content = $file_not_found . " $filename";
    }

    // Identify headlines of Level 2 and 3 from content-file including preceeding hooks and make $match handy
    preg_match_all( '/[\r\n]((<a name="(.*)"><\/a>\s*[\r\n]*)*)(#{2,3})\s(.*)/', $content, $match, PREG_OFFSET_CAPTURE );
    foreach( $match[0] as $k=>$v ) {
        $level = strlen($match[4][$k][0]);
        if( $level < 2 || $level > 3) ;
        $match[0][$k] = ['level'=>strlen($match[4][$k][0]), 'titel'=>$match[5][$k][0], 'offset'=>$v[1]];
    }
    $match = $match[0];
    $endOfChapter = strlen($content);
    $endOfLevelTwoChapter = strlen($content);
    foreach( array_reverse($match,true) as $k=>$v ) {
        $match[$k]['end'] = $endOfChapter;
        $endOfChapter = $v['offset'] - 1;
        if( $match[$k]['level'] == 2 ) {
            $match[$k]['end2'] = $endOfLevelTwoChapter;
            $endOfLevelTwoChapter = $endOfChapter;
        }
    }

    // identify internal hooks and the corresponding chapter
    preg_match_all( '/<a name="(.*)">/', $content, $dummy, PREG_OFFSET_CAPTURE );
    $hook = [];
    foreach( $dummy[1] as $v) {
        $offset = $v[1];
        $offset = array_filter( $match, function($v) use($offset){ return $v['offset']<=$offset && $offset<=$v['end']; });
        $hook['#'.$v[0]] = key( $offset );
    }

    // section/chapter requested by URL
    $section = rex_request( URL_FILE, 'int', 0 );

    // Generate navigation
    // only use headlines of level 2 and 3
    // Mark currently selected Chapter in the Menü
    $matchNav = array_filter( $match, function($m){return $m['level']==2 || $m['level']==3; });
    $navigation = '';
    foreach( $matchNav as $k=>$v ) {
        $navigation .= str_repeat( " ", $v['level']-1 ) . "- [{$v['titel']}]($urlDoc$k#$marker$k)"
            .( $k == $section ? '{.bg-primary}': '' )
            . PHP_EOL;
    }

    // identify and clip out active Section
    if( $section )  {
        while ( $match[$section]['level'] > 2 && $section > 0 ) $section--;
    } else {
        $section = 0;
    }
    $clipOffset = $match[$section]['offset'];
    $clipEnd = $match[$section]['end2'];
    $content = substr( $content,$clipOffset,$clipEnd-$clipOffset);

    // add hooks for identified chapters inside the selected level-2-chapter
    foreach( array_reverse($matchNav,true) as $k=>$v ) {
        if( $v['offset'] < $clipOffset ) continue;
        if( $v['offset'] > $clipEnd ) continue;
        $content = substr_replace( $content, '<a name="'.$marker.$k.'"></a>'.PHP_EOL, $v['offset']-$clipOffset, 0);
    }

    // correct internal references to chapters outside the clipped one
    preg_match_all( '/\[.*\]\((#.*)\)/', $content, $links, PREG_OFFSET_CAPTURE );
    foreach( array_reverse($links[1],true) as $k=>$v )
    {
        if( isset( $links[$v[0]] ) ) {
            $content = substr_replace( $content, $urlDoc.$links[$v[0]], $v[1], 0);
        }
    }

    // correct image-links to doc-files
    // detect filenames in the docPath-Dir
    // correct image-links for detected filenames
    if( $hasDocDir ) {
        $iterator = rex_finder::factory( $docPath );
        $replace = '![$1]('.$urlImg.'$2)';
        foreach( $iterator as $v) {
            $search = '/!\[(.*)\]\(('.preg_quote ( $v->getFilename() ).'.*)\)/';
            $content = preg_replace( $search, $replace, $content );
        }
    }
}

// Output ------------------------------------------------------------------------------------------

// parse markdown
$parser = new ParsedownExtra();
if( YML_MDBE == MDBE_ON ) $parser->setBreaksEnabled(true);
$content = $parser->text($content);
$navigation = $parser->text($navigation);
unset( $parser );

if( $defaults[YML_HEAD] ) {
    echo rex_view::title(rex_i18n::translate($defaults[YML_HEAD]));
}

echo '<section class="rex-yform-docs"><div class="row">';

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('docs_content'), false );
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');
if( $navigation )        {
    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('docs_navigation'), false);
    $fragment->setVar('body', $navigation, false);
    echo '<div class="col-md-4 yform-docs-navi">'.$fragment->parse('core/page/section.php').'</div>';
    echo '<div class="col-md-8 yform-docs-content">'.$content.'</div>';
} else {
    echo '<div class="col-md-12 yform-docs-content">'.$content.'</div>';
}
echo '</div></section>';
//echo '<link rel="stylesheet" type="text/css" media="all" href="../assets/addons/yform/plugins/docs/docs.css?buster=1530520815">';
echo '<style> .rex-yform-docs img {max-width:100%;}</style>';
?>
<style>
.rex-yform-docs h1 {
    font-size: 22px;
    margin-top: 5px;
    margin-bottom: 20px;
}
.rex-yform-docs h2 {
    font-size: 16px;
    margin-top: 40px;
    text-transform: uppercase;
    margin-bottom: 20px;
    letter-spacing: 0.02em;
    border-bottom: 1px solid #ccc;
    padding: 13px 15px 10px;
    background: #eee;
}
.rex-yform-docs h3 {
    margin-top: 40px;
    margin-bottom: 5px;
}

.rex-yform-docs blockquote {
    margin: 20px 0;
    background: #f3f6fb;
}
.rex-yform-docs blockquote h2 {
    margin: -10px -20px 20px;
    background: transparent;
	border-top: 1px #ccc;
}

.rex-yform-docs ol {
    padding-left: 18px;
}

.rex-yform-docs ul {
    margin-bottom: 10px;
	padding-bottom: 5px;;
    padding-left: 16px;
}
.rex-yform-docs ul li {
    list-style-type: square;
    list-style-position: outside;
}
.rex-yform-docs ul ul {
    padding-top: 5px;
}
.rex-yform-docs ul ul li {
    list-style-type: circle;
    list-style-position: outside;
    padding-bottom: 0;
}

.rex-yform-docs p,
.rex-yform-docs li {
    font-size: 14px;
    line-height: 1.6;
}

.rex-yform-docs hr {
    margin-top: 40px;
    border-top: 1px solid #ddd;
}

.rex-yform-docs table {
    width: 100%;
    max-width: 100%;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    margin: 20px 0 30px;
}
.rex-yform-docs th {
    background: #f7f7f7;
    border-bottom: 2px solid #ddd;
    border-collapse: separate;
}
.rex-yform-docs th,
.rex-yform-docs td {
    border-top: 1px solid #ddd;
    padding: 8px;
    line-height: 1.42857143;
    vertical-align: top;
    font-size: 13px;
}


.rex-yform-docs .yform-docs-navi ul {
    margin-bottom: 10px;
    padding-left: 0;
}
.rex-yform-docs .yform-docs-navi ul li {
    list-style-type: none;
    background: #eee;
    padding: 0 15px;
    line-height: 40px;
}
.rex-yform-docs .yform-docs-navi ul {
    background: #fff;
    margin-left: -15px;
    margin-right: -15px;
}
.rex-yform-docs .yform-docs-navi ul li li {
    list-style-type: none;
    background: #fff;
    line-height: 30px;
}
.rex-yform-docs .yform-docs-navi ul li li:before {
font-family: FontAwesome;
    content: '\f0a9';
    margin-right: 10px;
}
.rex-yform-docs .yform-docs-navi ul sup {
    display: none;
}
</style>
<?php /**/
