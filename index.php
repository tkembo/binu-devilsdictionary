<?php 
require_once('const.php');

$NAV = array( 'action'   => array( 'text' => '', 'url' => '', 'spider' => SPIDER_DEFAULT ),
              'navigate' => array( 'text' => '', 'url' => '', 'spider' => SPIDER_DEFAULT )
            );

init();
              
if ( ! isset($_GET['a']) )             { start_page();   }
elseif ( $_GET['a'] == 'r'   )         { read();   }
elseif ( $_GET['a'] == 'ssaf')         { select_sura_aya_form();   }
elseif ( $_GET['a'] == 'ssa' )         { select_sura_aya();   }
elseif ( $_GET['a'] == 'ctlf' )        { choose_translation_lang_form();   }
elseif ( $_GET['a'] == 'ctf' )         { choose_translation_form();   }
elseif ( $_GET['a'] == 'ct'  )         { choose_translation();   }
elseif ( $_GET['a'] == 'culf')         { choose_ui_language_form();   }
elseif ( $_GET['a'] == 'cul' )         { choose_ui_language();   }
elseif ( $_GET['a'] == 'a'   )         { about();   }
else { 
  error_log('quran: unknown action: ' . $_GET['a']);
  start_page(); 
}

setcookie('screen', SCREEN_WIDTH . 'x' . SCREEN_HEIGHT, time()+86400*356);
header('Content-Type: text/xml; charset="utf-8"');
echo '<?xml version="1.0" encoding="utf-8"?>'; //fix for vim syntax highlight bug <?
echo $BML;

######################################################################################################

function start_page() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;


  $NAV['action']['text'] = $LANG_KEYS['menu'];
  $NAV['action']['url']  = gen_start_page_menu();
  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['actiontype'] = 'back';


  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = START_PAGE_TTL;
  $bml_page['heading'] = $LANG_KEYS['the_holy_quran'];
  $bml_page['text'][0] = $LANG_KEYS['start'];
  $bml_page['link'][0] = $SCRIPT . 'a=r&i=1';
  $bml_page['text'][1] = $LANG_KEYS['select_sura_aya'];
  $bml_page['link'][1] = $SCRIPT . 'a=ssaf';
  $bml_page['text'][2] = $LANG_KEYS['choose_translation'];
  $bml_page['link'][2] = $SCRIPT . 'a=ctlf';

  $BML = gen_bml_page('start_page', $bml_page); 
}

function read() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;
  global $USER_PROFILE;

  if ( isset($_GET['i']) ) {
    $index = $_GET['i'];
  } else {
    $index = 1;
  }

  list($text, $sura) = get_translation_text($index);

  // tab 0 ( arabic tab ) is always rtl
  $rtl = $USER_PROFILE['tab'] === '0' ? 1 : $USER_PROFILE['trans_rtl'];

  $NAV['action']['text'] = $LANG_KEYS['menu'];

  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['url']  = $SCRIPT;


  if ( $rtl ) {
    $NAV['4']['url'] = $SCRIPT . 'a=r&i=' . get_next_index($index);
    $NAV['6']['url'] = $SCRIPT . 'a=r&i=' . get_prev_index($index);
    $NAV['action']['url']  = gen_read_page_menu($SCRIPT . 'a=r&i=' . get_next_index($index), '4', $SCRIPT . 'a=r&i=' . get_prev_index($index), '6');
    $bml_page['l_url'] = $NAV['4']['url'];
    $bml_page['r_url'] = $NAV['6']['url'];
    $bml_page['l_label'] = $LANG_KEYS['next'];
    $bml_page['r_label'] = $LANG_KEYS['prev'];
  } else {
    $NAV['4']['url'] = $SCRIPT . 'a=r&i=' . get_prev_index($index);
    $NAV['6']['url'] = $SCRIPT . 'a=r&i=' . get_next_index($index);
    $NAV['action']['url']  = gen_read_page_menu($SCRIPT . 'a=r&i=' . get_next_index($index), '6', $SCRIPT . 'a=r&i=' . get_prev_index($index), '4');
    $bml_page['l_url'] = $NAV['4']['url'];
    $bml_page['r_url'] = $NAV['6']['url'];
    $bml_page['l_label'] = $LANG_KEYS['prev'];
    $bml_page['r_label'] = $LANG_KEYS['next'];
  }


  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = READ_TTL;
  $bml_page['text'] = $text;
  $bml_page['tab'] = $USER_PROFILE['tab'];
  $bml_page['sura'] = $sura;
  $bml_page['rtl'] = $rtl;
  $bml_page['trans_lang_name'] = $USER_PROFILE['trans_lang_name'];
  $bml_page['trans_lang_id'] = $USER_PROFILE['trans_lang_id'];
  $bml_page['trans_id'] = $USER_PROFILE['trans_id'];

  $BML = gen_bml_page('read', $bml_page); 

}

function get_translation_text($index) {
  global $USER_PROFILE;
  $text = array();

  if ( $USER_PROFILE['tab'] === '0' ) {
    $trans_table = DEAFAULT_ARABIC_TRANS_TABLE;
  } else {
    $trans_table = $USER_PROFILE['trans_table'];
  }

  $res = mysql_query('select ' . $trans_table . '.index, sura, aya, text from ' . $trans_table . ' where ' . $trans_table . '.index >= ' . mysql_real_escape_string($index) . ' order by ' . $trans_table . '.index asc limit ' . AYA_PER_PAGE);

  if ( ! $res ) {
    error_log('quran: error selecting index from ' . $trans_table . ' : ' .  mysql_error() );
    exit;
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    $text[] = $row;
    $max_sura = $row['sura'];
  } 

  return(array($text, $max_sura));
}

function get_next_index($index) {
  if ( $index + AYA_PER_PAGE <= 6236 ) {
    return($index + AYA_PER_PAGE);
  } else {
    return(6236); //final index
  }
}

function get_prev_index($index) {
  if ( $index - AYA_PER_PAGE >= 1 ) {
    return($index - AYA_PER_PAGE);
  } else {
    return(1);
  }
}

function select_sura_aya_form() {
  global $NAV;
  global $SCRIPT;
  global $BML;
  global $LANG_KEYS;


  $NAV['action']['text']   = 'Submit';
  $NAV['action']['url']    = $SCRIPT . 'a=ssa';
  $NAV['action']['spider'] = 'N';

  unset($NAV['navigate']);

  $bml_page['form'] = array( 'title' => $LANG_KEYS['select_sura_aya'],
                              'text_fields' => array(
                                                      array('name' => $LANG_KEYS['sura'], 'value' => '', 'fullscreen' => 'false', 'mandatory' => 'true', 'maxlength' => '3'),
                                                      array('name' => $LANG_KEYS['aya'], 'value' => '', 'fullscreen' => 'false', 'mandatory' => 'false', 'maxlength' => '3') 
                                                    )
                            );
  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = STATIC_TTL;


  $BML = gen_bml_page('text_entry', $bml_page); 
}

function select_sura_aya() {

  $sura = isset($_GET['1']) ? $_GET['1'] : 1;
  $aya = isset($_GET['2']) ? $_GET['2'] : 1;

  if ( ! preg_match('/^\d+$/', $sura) ) {
    $sura = 1;
  } 

  if ( ! preg_match('/^\d+$/', $aya) ) {
    $aya = 1;
  } 

  // the table we select from doesn't matter, we just need the index, I'm assuming the sura aya numbers / text are
  // constant across all translations
  $res = mysql_query('select ' . DEAFAULT_ARABIC_TRANS_TABLE . '.index from ' . DEAFAULT_ARABIC_TRANS_TABLE . ' where sura=' . $sura . ' and aya=' . $aya );

  if ( ! $res || mysql_num_rows($res) !== 1 ) {
    error_log('quran: error selecting sura/aya ' . $sura . '/' . $aya . ' from ' . DEAFAULT_ARABIC_TRANS_TABLE . ' : ' .  mysql_error() );
    $index = 1;
  } else {
    $row = mysql_fetch_assoc($res);
    $index = $row['index'];
  }

  $_GET['i'] = $index;
  read();

}


function about() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;

  $NAV['action']['text'] = $LANG_KEYS['menu'];
  $NAV['action']['url']  = gen_start_page_menu();
  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['actiontype'] = 'back';

  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = STATIC_TTL;
  $bml_page['heading'] = $LANG_KEYS['the_holy_quran'];
  $bml_page['text'][0] = $LANG_KEYS['about_text'];

  $BML = gen_bml_page('general_text', $bml_page); 
}


function choose_translation_lang_form() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;
  $list = array();

  $NAV['action']['text'] = $LANG_KEYS['menu'];
  $NAV['action']['url']  = gen_start_page_menu();
  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['actiontype'] = 'back';

  $res = mysql_query('select distinct t.translation_language, l.translation as translation_language_name from translations t, language_keys l where l.language_key=\'lang_name_native\' and l.language=t.translation_language order by translation_language asc');

  if ( ! $res ) {
    error_log('quran: couldnt select translation list' . mysql_error());
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    array_push($list, array( 'text' =>  '(' . $row['translation_language'] . ') ' . $row['translation_language_name'],
                             'url'  => $SCRIPT . 'a=ctf&ntl=' . $row['translation_language']
                           )
              );

  }



  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = STATIC_TTL;
  $bml_page['list'] = $list;
  $bml_page['heading'] = $LANG_KEYS['choose_translation'];

  $BML = gen_bml_page('settings_list', $bml_page); 
}

function choose_translation_form() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;
  $list = array();

  $NAV['action']['text'] = $LANG_KEYS['menu'];
  $NAV['action']['url']  = gen_start_page_menu();
  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['actiontype'] = 'back';

  if ( isset($_GET['ntl']) && preg_match('/^\w{2}$/', $_GET['ntl']) ) {
    $new_trans_lang = $_GET['ntl'];
  } else {
    // should only happen when people are trying to hack url parameters
    error_log('quran: bad language selection: ' . $_GET['ntl']);
    $new_trans_lang = DEFAULT_INTERFACE_LANGUAGE; 
  } 

  $res = mysql_query('select t.translation_name, t.translation_id, t.translation_language, l.translation as translation_language_name from translations t, language_keys l where l.language_key=\'lang_name_native\' and l.language=t.translation_language and t.translation_language=\'' .  $new_trans_lang . '\' order by t.translation_language asc');

  if ( ! $res ) {
    error_log('quran: couldnt select translation list' . mysql_error());
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    //don't offer the default arabic translation as an option to translate to
    if ( $row['translation_id'] == DEFAULT_ARABIC_TRANSLATION_ID) {
      continue;
    }

    $url = $SCRIPT;
    $url = preg_replace('/trans_id=\d+/', 'trans_id=' . $row['translation_id'], $url) . '&save=1';

    array_push($list, array( 'text' =>  '(' . $row['translation_language'] . ') ' . $row['translation_name'],
                             'url'  => $url
                           )
              );

  }



  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = STATIC_TTL;
  $bml_page['list'] = $list;
  $bml_page['heading'] = $LANG_KEYS['choose_translation'];

  $BML = gen_bml_page('settings_list', $bml_page); 
}

function choose_ui_language_form() {
  global $NAV;
  global $BML;
  global $SCRIPT;
  global $LANG_KEYS;
  $list = array();

  $NAV['action']['text'] = $LANG_KEYS['menu'];
  $NAV['action']['url']  = gen_start_page_menu();
  $NAV['navigate']['text'] = $LANG_KEYS['back'];
  $NAV['navigate']['actiontype'] = 'back';

  $res = mysql_query('select language_key, translation, language from language_keys where language_key=\'lang_name_native\' or language_key=\'selectable_app_lang\'');

  if ( ! $res ) {
    error_log('quran: couldnt select ui language list' . mysql_error());
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    if ( $row['language_key'] == 'lang_name_native' ) {
      $languages[$row['language']]['lang_name_native'] = $row['translation'];
    } else {
      $languages[$row['language']]['selectable_app_lang'] = $row['translation'];
    }
  }

  foreach ( $languages as $lang_code => $language ) {

    if ( $language['selectable_app_lang'] ) {
      $url = $SCRIPT;
      $url = preg_replace('/lang=\w{2}/', 'lang=' . $lang_code, $url) . '&save=1';

      array_push($list, array( 'text' => '(' . $lang_code . ') ' . $language['lang_name_native'],
                               'url'  => $url
                             )
                );
    }

  }



  $bml_page['nav'] = $NAV;
  $bml_page['ttl'] = STATIC_TTL;
  $bml_page['list'] = $list;
  $bml_page['heading'] = $LANG_KEYS['application_language'];

  $BML = gen_bml_page('settings_list', $bml_page); 
}


######################
# Display code
#####################

function gen_bml_page($page_type, $bml_page) {

  $styles = get_styles();
  $footer = gen_bml_footer($bml_page['nav']);
  $footer_actions = gen_nav_controls($bml_page['nav']);

  if ( $page_type == 'read' ) {
    $bml_body = gen_read_bml($bml_page);
  } elseif ( $page_type == 'text_entry' ) {
    $bml_body = gen_text_entry_bml($bml_page);
    $footer = '';
    $styles = '';
    $footer_actions = gen_te_nav_controls($bml_page['nav']);
  } elseif ( $page_type == 'start_page' ) {
    $bml_body = gen_start_page_bml($bml_page);
  } elseif ( $page_type == 'general_text' ) {
    $bml_body = gen_general_text_bml($bml_page);
  } elseif ( $page_type == 'settings_list' ) {
    $bml_body = gen_settings_list_bml($bml_page);
  } else {
    error_log('quran: unknown page type: ' . $page_type);
  }


  if ( isset($bml_page['list']) ) {
    $listing = gen_bml_listing($bml_page['list']);
  } else {
    $listing = '';
  }


  $bml = '
<binu ttl="' . $bml_page['ttl'] . '" app="' . APP_ID . '" developer="' . DEV_ID . '">' . "
$styles
<page>
$bml_body
$footer
</page>
$footer_actions
$listing
</binu>
";

  return($bml);

}

function gen_general_header_bml($title_text='') {
  global $app;

  $bml = '
<pageSegment x="0" y="0">
  <fixed>
    <text x="' . $app['title_indent'] . '" y="' . $app['indent']  . '" w="' . SCREEN_WIDTH . '" style="title_text" mode="wrap">' . htmlspecialchars($title_text, ENT_QUOTES, 'UTF-8') . '</text>
  </fixed>
</pageSegment>' . "\n";

  return($bml);

}



function gen_general_text_bml($data) {
  global $app;
  $init_y = 0;

  $bml = gen_background_image();
  $bml .= gen_general_header_bml($data['heading']);

  $bml .= '
<pageSegment x="0" y="y" h="-' . $app['line_height'] . '">
  <panning w="' . SCREEN_WIDTH . '" >';

  for ( $i=0; $i < count($data['text']); $i++ ) {
    $bml .= '<mark name="line" y="' . $init_y . '" />';

    if ( isset($data['link'][$i] ) ) { // there is a link
      $bml .= '
<link url="' . htmlspecialchars($data['link'][$i], ENT_COMPAT, 'UTF-8') . '" spider="' . SPIDER_DEFAULT . '" x="' . $app['indent'] . '" y="line + ' . ($app['line_height'] / 2) . '" mode="truncate" style="body_text" >
<text x="' . $app['indent'] . '" y="line + ' . ( $app['line_height'] / 2 ) . '" w="width-10" mode="wrap" style="body_text">' . htmlspecialchars(html_entity_decode($data['text'][$i], ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '</text>
</link>';
    } else {
      $bml .= '<text x="' . $app['indent'] . '" y="line + ' . ( $app['line_height'] / 2 ) . '" w="width-10" mode="wrap" style="body_text">' . htmlspecialchars(html_entity_decode($data['text'][$i], ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '</text>';
    }

    $init_y = 'y';

  }

  $bml .= '
  </panning>
</pageSegment>';

  return($bml);
}

function gen_settings_list_bml($data) {
  global $app;

  $bml = gen_background_image();
  $bml .= gen_general_header_bml($data['heading']);

  $bml .= '
<pageSegment x="0" y="y" h="-' . $app['line_height'] . '" w="' . SCREEN_WIDTH . '">
  <listing listName="listing1" type="simple">
    <link name="url" x="' . $app['indent'] . '" y="0" mode="truncate" style="body_text" spider="N" >
      <field name="text" w="width - 9" mode="wrap" style="body_text" align="left"/>
    </link>
  </listing>
</pageSegment>';

  return($bml);
}

function gen_start_page_bml($data) {
  global $app;

  $init_y = $app['title_indent'] * 1.5;

  $bml = gen_background_image();

  $bml .= '
<pageSegment x="0" y="y" h="-' . $app['line_height'] . '" w="' . SCREEN_WIDTH . '">
  <panning w="' . SCREEN_WIDTH . '" >

    <text x="' . $app['title_indent'] . '" y="' . $app['indent']  . '" style="title_text" mode="wrap">' . $data['heading'] . '</text>';


  for ( $i=0; $i < count($data['text']); $i++ ) {
    $bml .= '<mark name="line" y="' . $init_y . '" />';

    if ( isset($data['link'][$i] ) ) { // there is a link
      $bml .= '
<link url="' . htmlspecialchars($data['link'][$i], ENT_COMPAT, 'UTF-8') . '" spider="' . SPIDER_DEFAULT . '" x="' . $app['indent'] . '" y="line" mode="truncate" style="body_text" >
  <text mode="wrap" style="body_text">' . htmlspecialchars(html_entity_decode($data['text'][$i], ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '</text>
</link>' . "\n";
    } else {
      $bml .= '<text x="' . $app['indent'] . '" y="line" w="width-10" mode="wrap" style="body_text">' . htmlspecialchars(html_entity_decode($data['text'][$i], ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '</text>' . "\n";
    }

    $init_y = 'y';

  }

  $bml .= '
  </panning>
</pageSegment>';


  return($bml);
}

function gen_background_image() {
  global $app;

  if ( APP_SIZE == 'L' ) {
    $bg_image = '<image x="' . ((SCREEN_WIDTH - 182) / 2 ) . '" y="' . ((SCREEN_HEIGHT - 165 - $app['line_height'] ) / 2 ) . '" w="182" h="165" mode="crop" url="' . IMG_PATH . 'quran_bg_l_4.png" />';
  } elseif ( APP_SIZE == 'M' ) {
    $bg_image = '<image x="' . ((SCREEN_WIDTH - 150) / 2 )  . '" y="' . ((SCREEN_HEIGHT - 136 - $app['line_height']) / 2 ) . '" w="150" h="136" mode="crop" url="' . IMG_PATH . 'quran_bg_m_4.png" />';
  } else {
    $bg_image = '<image x="' . ((SCREEN_WIDTH - 120) / 2 ) . '" y="' . ((SCREEN_HEIGHT - 109 - $app['line_height']) / 2 ) . '" w="120" h="109" mode="crop" url="' . IMG_PATH . 'quran_bg_s_4.png" />';
  }


  $bml = '
<pageSegment x="0" y="y" h="-' . $app['line_height'] . '" w="' . SCREEN_WIDTH . '">
  <panning w="' . SCREEN_WIDTH . '" >
    ' . $bg_image . '
 </panning>
</pageSegment>';

  return($bml);
}

function gen_read_bml($data) {
  global $app;
  $prose = '';

  $bml = gen_read_header_bml($data['sura'], $data['tab'], $data['trans_lang_name'], $data['trans_id']);
  $align = $data['rtl'] ? 'right' : 'left';
  $vscroll = $data['rtl'] ? 'left' : 'right';

  foreach ( $data['text'] as $aya ) {
    if ( $data['tab'] == 0 || $data['trans_lang_id'] == 'ar' || $data['trans_lang_id'] == 'fa') {
      $aya['aya'] = arabic_to_hidi_digits($aya['aya']);
    }
    $prose .= $aya['text'] . ' (' . $aya['aya'] . ') ';
  }

  $bml .= '
<pageSegment x="' . $app['indent'] . '" y="y" h="-' . $app['line_height'] . '" w="' . (SCREEN_WIDTH - $app['indent'] ) . '">
  <panning vscroll="' . $vscroll . '">
  <text y="y" w="width-10" mode="wrap" style="body_text" align="' . $align . '">' . htmlspecialchars(html_entity_decode(strip_tags($prose), ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '</text>

  <mark name="page_nav" y="y + ' . $app['line_height'] . '" />

  <link key="4" x="' . (SCREEN_WIDTH / 4) . '" y="page_nav" url="' . htmlspecialchars($data['l_url'], ENT_COMPAT, 'UTF-8') . '" spider="' . SPIDER_DEFAULT . '" >
    <image x="0" y="0" w="70" h="40" mode="scale" url="' . IMG_PATH . 'quran_l_arrow.png" />
    <text mode="wrap" style="body_text" align="left">' . htmlspecialchars($data['l_label'], ENT_COMPAT, 'UTF-8') . '</text>
  </link>
  <link  x="textx + ' . ( $app['title_indent'] / 2 ) . '" y="page_nav" key="6" url="' . htmlspecialchars($data['r_url'], ENT_COMPAT, 'UTF-8') . '" spider="' . SPIDER_DEFAULT . '" >
    <text mode="wrap" style="body_text" align="left">' . htmlspecialchars($data['r_label'], ENT_COMPAT, 'UTF-8') . '</text>
  </link>

  </panning>
</pageSegment>
';

  return($bml);
}

function arabic_to_hidi_digits($arabic_num) {
  $hindi_digits = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
  $hindi_num = '';

  $num_arr = str_split($arabic_num);

  foreach ( $num_arr as $arabic_digit ) {
    $hindi_num .= (string) $hindi_digits[$arabic_digit];
  }

  return($hindi_num);
}

function gen_read_header_bml($sura, $tab, $trans_lang_name, $trans_id ) {
  global $app;
  global $USER_PROFILE;
  global $LANG_KEYS;

  $tab_width = ( SCREEN_WIDTH - ( 2 * $app['title_indent'] ) ) / 2;
  $quran_tab_start = $app['title_indent'];
  $trans_tab_start = $quran_tab_start + $tab_width + round(SCREEN_WIDTH / 18, 0);;
  $tab_base_height = 4;
  $quran_tab_link = preg_replace('/([\?\&])tab=\d/', '$1tab=0', $_SERVER['REQUEST_URI']);
  $trans_tab_link = preg_replace('/([\?\&])tab=\d/', '$1tab=1', $_SERVER['REQUEST_URI']);

  if ( ! strpos($quran_tab_link, 'save=1') ) {
    $quran_tab_link .= '&save=1';
  }

  if ( ! strpos($trans_tab_link, 'save=1') ) {
    $trans_tab_link .= '&save=1';
  }

  $nav_tabs = '<mark name="tabs" x="x" y="y + 4"/>';

  if ( $tab === '0' ) { //quran active
    $nav_tabs .= '
    <!-- quran active -->
    <rectangle x="' . $trans_tab_start . '" y="tabs" w="' . $tab_width . '" h="' . $app['line_height'] . '" style="bg_tab" />
    <rectangle x="0" y="tabs + ' . $app['line_height'] . '" w="' . $quran_tab_start . '" h="1" style="black" />
    <rectangle x="' . $quran_tab_start . '" y="tabs" w="1" h="' . $app['line_height'] . '" style="black" />
    <rectangle x="' . $quran_tab_start . '" y="tabs" w="' . $tab_width . '" h="1" style="black" />
    <rectangle x="' . ($quran_tab_start + $tab_width) . '" y="tabs" w="1" h="' . $app['line_height'] . '" style="black" />
    <rectangle x="' . ($quran_tab_start + $tab_width) . '" y="tabs + ' . $app['line_height'] . '" w="' . ( SCREEN_WIDTH - ($quran_tab_start + $tab_width) ) . '" h="1" style="black" />';
  } else { // translation active
    $nav_tabs .= '
    <!-- trans active -->
    <rectangle x="' . $quran_tab_start . '" y="tabs" w="' . $tab_width . '" h="' . $app['line_height'] . '" style="bg_tab" />
    <rectangle x="0" y="tabs + ' . $app['line_height'] . '" w="' . $trans_tab_start . '" h="1" style="black" />
    <rectangle x="' . $trans_tab_start . '" y="tabs" w="1" h="' . $app['line_height'] . '" style="black" />
    <rectangle x="' . $trans_tab_start . '" y="tabs" w="' . $tab_width . '" h="1" style="black" />
    <rectangle x="' . ($trans_tab_start + $tab_width) . '" y="tabs" w="1" h="' . $app['line_height'] . '" style="black" />
    <rectangle x="' . ($trans_tab_start + $tab_width) . '" y="tabs + ' . $app['line_height'] . '" w="' . ( SCREEN_WIDTH - ($trans_tab_start + $tab_width) ) . '" h="1" style="black" />';
  }

  $nav_tabs .= '
  <link x="' . ($quran_tab_start + 2 ) . '" y="tabs" w="' . $tab_width . '" h="' . ( $app['line_height'] +2 ) . '" url="' . htmlspecialchars($quran_tab_link, ENT_COMPAT, 'UTF-8') . '" spider="N" >
    <text mode="truncate" style="tab_text" w="' . $tab_width . '" align="left">القرآن</text>
  </link>
  <link x="' . ($trans_tab_start + 2) . '" y="tabs"  w="' . $tab_width . '" h="' . ( $app['line_height'] +2 ) . '" url="' . htmlspecialchars($trans_tab_link, ENT_COMPAT, 'UTF-8') . '" spider="N" >
    <text mode="truncate" style="tab_text" align="left">' . htmlspecialchars($trans_lang_name, ENT_QUOTES, 'UTF-8') . '</text>
  </link>
';


  $bml = '
<pageSegment x="0" y="0">
  <fixed>
    ' . $nav_tabs . '
  <text x="' . $app['indent'] . '" y="y" align="left" style="sura_head" mode="wrap">' . $LANG_KEYS['sura'] . ' ' . $sura . '</text>
  </fixed>
</pageSegment>' . "\n";

  return($bml);

}



function gen_text_entry_bml($bml_page) {

  $text_entry = $bml_page['form'];

  $bml = '
<pageSegment x="0" y="0">
  <textEntry title="' . $text_entry['title'] . '">
';

  foreach ( $text_entry['text_fields'] as $text_field ) { 
    $bml .= '    <textEntryField name="' . $text_field['name'] . 
             '" value="' . $text_field['value'] . 
             '" fullScreen="' . $text_field['fullscreen'] . 
             '" mandatory="' . $text_field['mandatory'] . 
             '" maxLength="' . $text_field['maxlength'] . '"/>' . "\n";
  }

  $bml .= '
  </textEntry>
</pageSegment>
';

  return($bml);
}


# generate the visible footer menu 
function gen_bml_footer($nav) {
  global $app;
  $softkeys = array('action', 'navigate');

  $footer = '
    <pageSegment x="0" y="-' . ($app['line_height'] + 2) . '" >
       <fixed>
        <rectangle x="0" y="2" h="1" radius="0" style="grey_line" />
        <rectangle x="0" y="3" h="' . $app['line_height'] . '" radius="0" style="footer_bg"/>
';

  foreach ( $softkeys as $key ) {
    if ( isset($nav[$key]['text']) ) {
      if ( isset($nav[$key]['actiontype']) || is_string($nav[$key]['url']) ) { # not a menu
        if ( isset($nav[$key]['actiontype'] ) ) {
          if ( $nav[$key]['actiontype'] ==  'back' ) {
            $action = 'actionType="back"';
          } elseif ( $nav[$key]['actiontype'] == 'browser' ) {
            $action = 'actionType="browser" url="' . htmlspecialchars($nav[$key]['url'], ENT_COMPAT, 'UTF-8') . '"';
          } elseif ( $nav[$key]['actiontype'] == 'home' ) {
            $action = 'actionType="home"';
          } elseif ( $menu_item['actiontype'] == 'exit' ) {
            $action = 'actionType="exit"' . "\n";
          } else {
            error_log( "bad actiontype: $nav[$key]['actiontype'] ignoring");
          }
        } else {
          $action = 'url="' . htmlspecialchars($nav[$key]['url'], ENT_COMPAT, 'UTF-8') . '"';
        }
      } else { // is a menu
        $action = 'actionType="menu" menu="' . $key . '_menu"';
      }
  
      if ( ! isset($nav[$key]['spider']) ) {
        $nav[$key]['spider'] = SPIDER_DEFAULT;
      }

      if ( $key == 'action') {
        $align = 'left';
        $select_start = 0;
      } elseif ( $key == 'navigate' ) {
        $align = 'right';
        $select_start = 'width * 2/3';
      }
  
      $footer .= '
  <link key="' . $key . '" spider="' . $nav[$key]['spider'] . '" ' . $action . ' x="' . $select_start . '" y="0" w="width / 3" align="' . $align . '" >
    <text x="' . $app['indent'] . '" y="0" w="width / 3 - ' . ( $app['indent'] * 2 ) . '" mode="truncate" style="footer_text" align="' . $align . '">' . htmlspecialchars($nav[$key]['text'], ENT_COMPAT, 'UTF-8') . '</text>
  </link>';
    }

  
  }

  $footer .= '
      </fixed>
    </pageSegment>';

  return($footer);
}


# generate the control directives 
function gen_nav_controls($nav) {

  $footer = '
  <control>
    <actions>
';

  foreach ( $nav as $key => $val ) {

    if ( isset($nav[$key]['actiontype']) || is_string($nav[$key]['url'] ) ) { # standard menu item

      if ( $key == 'action' || $key == 'navigate' ) {
        continue;
      }

      if ( ! isset($nav[$key]['spider']) ) {
        $nav[$key]['spider'] = SPIDER_DEFAULT;
      }

      $footer .= '<action key="' . $key . '" spider="' . $nav[$key]['spider'] . '" ';


      if ( isset($nav[$key]['actiontype'] ) ) {
        if ( $nav[$key]['actiontype'] ==  'back' ) {
          $footer .= 'actionType="back" />' . "\n";
        } elseif ( $nav[$key]['actiontype'] == 'browser' ) {
          $footer .= 'actionType="browser" url="' . htmlspecialchars($nav[$key]['url'], ENT_COMPAT, 'UTF-8') . '" />' . "\n";
        } elseif ( $nav[$key]['actiontype'] == 'home' ) {
          $footer .= 'actionType="home" />' . "\n";
        } elseif ( $menu_item['actiontype'] == 'exit' ) {
          $footer .= 'actionType="exit" />' . "\n";
        } else {
          error_log( "bad actiontype: $nav[$key]['actiontype'] ignoring");
        }
      } else {
        $footer .= '>' .  htmlspecialchars($nav[$key]['url'], ENT_COMPAT, 'UTF-8') . '</action>' . "\n";
      }

    } else { // menu
      $footer .= gen_popup_menu_bml($key, $nav[$key]['url']);
    }

  }


  $footer .= '
    </actions>
  </control>
';

  return($footer);

}

# generate the text entry control directives 
function gen_te_nav_controls($nav) {

  $footer = '
  <control>
    <actions>
      <action key="action" spider="N">' . htmlspecialchars($nav['action']['url'], ENT_COMPAT, 'UTF-8') . '</action>
    </actions>
  </control>
';

  return($footer);

}


function gen_popup_menu_bml($action, $menu) {
  $menu_bml;
  $align;
  $menu_item;

  $align = $action == 'action' ? 'Left' : 'Right';
  $menu_bml = '<menu align="' . $align . '" name="' . $action . '_menu" >' . "\n";

  foreach ( $menu as $menu_item ) {
    $menu_bml .= '<action key="' . $menu_item['key'] . '" text="' . htmlspecialchars($menu_item['text'], ENT_COMPAT, 'UTF-8') . '"';
    if ( array_key_exists('actiontype', $menu_item) ) {
      if ( $menu_item['actiontype'] == 'back' ) {
        $menu_bml .= ' actionType="back" />' . "\n";
      } elseif ( $menu_item['actiontype'] == 'browser' ) {
        $menu_bml .= ' actionType="browser">' . htmlspecialchars($menu_item['target'], ENT_COMPAT, 'UTF-8') . '</action>' . "\n";
      } elseif ( $menu_item['actiontype'] == 'exit' ) {
        $menu_bml .= ' actionType="exit" />' . "\n";
      } elseif ( $menu_item['actiontype'] == 'home' ) {
        $menu_bml .= ' actionType="home" />' . "\n";
      } else {
        error_log('bad menu actiontype: ' . $menu_item['actiontype'] );
        exit();
      }
    } else {
      $menu_bml .= '>' . htmlspecialchars($menu_item['target'], ENT_COMPAT, 'UTF-8') . '</action>' . "\n";
    }
  }

  $menu_bml .= '</menu>' . "\n";

  return($menu_bml);

}



function gen_bml_listing($list) {

  $bml_list = '<list name="listing1">' . "\n";

  foreach ( $list as $list_item ) {
    $bml_list .= '<listItem>' . "\n";

    foreach ( $list_item as $key => $val ) {
      $bml_list .= '  <itemField name="' . $key . '" value="' . htmlspecialchars(html_entity_decode($val, ENT_QUOTES, 'UTF-8'), ENT_COMPAT, 'UTF-8') . '"/>' . "\n";
    }

    $bml_list .= '</listItem>' . "\n";
  }

  $bml_list .= '</list>'; 

  return($bml_list);
}


function get_styles() {
  global $app;

  $font_size_s = $app['font_size'] - 2;
  $font_size_m = $app['font_size'] - 1;
  $font_size_l = $app['font_size'];
  $font_size_title = $app['font_size'] + 2;

return '
<styles>
  <style name="body_text">
    <color value="#FF000000"/>
    <font face="Arial Unicode MS" size="' . $font_size_l . '"/>
  </style>
  <style name="title_text">
    <color value="#FF000000"/>
    <font face="Arial Unicode MS" size="' . $font_size_title . '"/>
  </style>
  <style name="footer_text">
    <color value="#FF000000"/>
    <font face="Arial Unicode MS" size="' . $font_size_m . '"/>
  </style>
  <style name="tab_text">
    <color value="#000000"/>
    <font face="Arial Unicode MS" size="' . $font_size_m . '"/>
  </style>
  <style name="sura_head">
    <color value="#000000"/>
    <font face="Arial Unicode MS" size="' . $font_size_s . '"/>
  </style>
  <style name="grey_line">
    <color value="#FF9E9FA2"/>
  </style>
  <style name="black">
    <color value="#000000"/>
  </style>
  <style name="bg_tab">
    <color value="#bbbbbb"/>
  </style>
  <style name="footer_bg">
    <color value="#' . FOOTER_BG . '"/>
  </style>
</styles>
';
}


function gen_start_page_menu() {
  global $SCRIPT;
  global $LANG_KEYS;
  $menu = array();
  $i = 1;

  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['home'],    'target' => $SCRIPT ));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['about'],   'target' => $SCRIPT . 'a=a'  ));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['help'],    'target' => 'http://apps.binu.net/test/apps/client_docs/info.php?homePageUrl=' . urlencode($SCRIPT) . '&appName=The%20Quran'));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['choose_application_language'],    'target' => $SCRIPT . 'a=culf'));

  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['my_binu'], 'target' => MY_BINU_URL ));

  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['exit'],    'actiontype'=> 'exit'));

  return($menu);
}


function gen_read_page_menu($next_url, $next_key, $prev_url, $prev_key) {
  global $SCRIPT;
  global $LANG_KEYS;
  $menu = array();
  $i = 1;

  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['next'] . ' (' . $next_key . ') ',    'target' => $next_url));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['prev'] . ' (' . $prev_key . ') ',    'target' => $prev_url));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['home'],    'target' => $SCRIPT ));
  array_push( $menu, array( 'key'=>$i++, 'text'=> $LANG_KEYS['exit'],    'actiontype'=> 'exit'));

  return($menu);
}
#################################
# utility
################################

// retrieve user settings and
// translation details
function init () {
  global $USER_PROFILE;
  global $SCRIPT;
  global $LANG_KEYS;

  db_connect();

  // if we've come from within the app ( inapp ) set the profile parames directly from the
  //URL parameters
  if ( valid_url_params() && isset($_GET['inapp']) ) {
    $USER_PROFILE['trans_id'] = $_GET['trans_id'];
    $USER_PROFILE['tab'] = $_GET['tab'];
    $USER_PROFILE['lang'] = $_GET['lang'];

    if ( isset($_GET['save']) ) {
      save_user_profile($USER_PROFILE);
    }

  } else { //otherwise load the profile params from the user profile in the db
    $USER_PROFILE = set_profile_from_db();

    if ( ! $USER_PROFILE ) {
      $USER_PROFILE['trans_id'] = DEFAULT_TRANSLATION_ID;
      $USER_PROFILE['tab'] = DEFAULT_TAB;
      $USER_PROFILE['lang'] = DEFAULT_INTERFACE_LANGUAGE;
    }

  }

  $USER_PROFILE['trans_table'] = QURAN_TRANS_PREFIX . $USER_PROFILE['trans_id'];

  list($USER_PROFILE['trans_rtl'], $USER_PROFILE['trans_lang_id'], $USER_PROFILE['trans_name']) = get_trans_details($USER_PROFILE['trans_id']);
  $USER_PROFILE['trans_lang_name'] = get_trans_name($USER_PROFILE['trans_lang_id']);

  $SCRIPT = $_SERVER['SCRIPT_NAME'] . '?trans_id=' . $USER_PROFILE['trans_id'] . '&tab=' . $USER_PROFILE['tab'] . '&lang=' . $USER_PROFILE['lang'] . '&inapp=1&';


  $LANG_KEYS = get_interface_lang_keys($USER_PROFILE['lang']);

}

function valid_url_params() {
  $valid = false;

  if ( isset($_GET['trans_id']) && isset($_GET['tab']) && isset($_GET['lang']) ) {
    if (  preg_match('/^\d+$/', $_GET['trans_id']) && preg_match('/^\d+$/', $_GET['tab']) && preg_match('/^\w{2}$/', $_GET['lang']) ) {
      $valid = true;
    }
  }

  return($valid);
}


function save_user_profile($user_profile) {

  // user_profile values been previously checked to be clean
  $res = mysql_query('insert into user_profiles (device_id, translation_id, interface_language, tab, screen_res, IP, UA) values (' . 
                         mysql_real_escape_string(DEVICE_ID) . ',' .
                        $user_profile['trans_id'] . ', \'' .
                        $user_profile['lang'] . '\',' .
                        $user_profile['tab'] . ',\'' . 
                        mysql_real_escape_string(SCREEN_WIDTH . 'x' . SCREEN_HEIGHT) . '\', \'' .
                        mysql_real_escape_string(DEVICE_IP) . '\' , \'' .
                        mysql_real_escape_string(USER_AGENT) . '\') ' .
                     'on duplicate key update ' .
                        'translation_id=' . $user_profile['trans_id'] . ',' .
                        'interface_language=\'' . $user_profile['lang'] . '\',' .
                        'tab=' . $user_profile['tab'] . ',' .
                        'screen_res=\'' . mysql_real_escape_string(SCREEN_WIDTH . 'x' . SCREEN_HEIGHT) . '\',' .
                        'IP=\'' . mysql_real_escape_string(DEVICE_IP) . '\',' .
                        'UA=\'' .  mysql_real_escape_string(USER_AGENT) . '\''
                     );


  if ( ! $res && mysql_affected_rows() !== 1 ) {
    error_log('quran: error saving user profile to db. ' . mysql_error());
  }
                     
                        
}

// try to get the users saved profile from the db,
// if it's not there return false
function set_profile_from_db() {
  $user_profile = false;

  $res = mysql_query('select translation_id, interface_language, tab from user_profiles where device_id=' . mysql_real_escape_string(DEVICE_ID) );

  if ( $res && mysql_num_rows($res) == 1 ) {
    $row = mysql_fetch_assoc($res);

    $user_profile['trans_id'] = $row['translation_id'];
    $user_profile['tab'] = $row['tab'];
    $user_profile['lang'] = $row['interface_language'];
  }
   
  return($user_profile);
}

function get_trans_details($trans_id) {
  $res = mysql_query('select translation_language, translation_name from translations where translation_id=' . mysql_real_escape_string($trans_id) );

  if ( ! $res ) {
    error_log('quran: couldnt get is_trans_rtl');
    return(0);
  }

  $row = mysql_fetch_assoc($res);


  // is the language right to left
  if ( strpos(RTL_LANGUAGES, $row['translation_language'] ) ) {
    $rtl = 1;
  } else {
    $rtl = 0;
  }

  return(array($rtl, $row['translation_language'],  $row['translation_name']));
}

function get_trans_name($trans_lang_code) {
  $res = mysql_query('select translation from language_keys where language=\'' . $trans_lang_code . '\' and language_key=\'lang_name_native\'');

  if ( ! $res ) {
    error_log('quran: couldnt get trans name');
    return(0);
  }

  $row = mysql_fetch_assoc($res);

  return($row['translation']);
}

function db_connect() {
  if ( ! mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD) ) {
    exit('quran: Could not connect: ' . mysql_error());
  }

  if ( ! mysql_select_db(MYSQL_DB) ) {
    exit('quran: Could not select db '. MYSQL_DB . '. ' . mysql_error());
  }
}


// get all the text for the user interface
function get_interface_lang_keys($lang) {
  $lang_keys = array();

  $res = mysql_query('select language_key, translation from language_keys where language=\'' . mysql_real_escape_string($lang) . '\'' );

  if ( ! $res ) {
    error_log('quran: error selecting language: ' . mysql_error());
  }
  
  while ( $row = mysql_fetch_assoc($res) ) {
    $lang_keys[$row['language_key']] = $row['translation'];
  }

  return($lang_keys);
}



?>
