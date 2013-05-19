<?php

require('../const.php');
require('const.php');
db_connect();

$SCRIPT = $_SERVER['SCRIPT_NAME'];

if ( ! isset($_REQUEST['action']) )       { start_page();   }
elseif ( $_REQUEST['action'] == 'alf' )   { add_language_form();   }
elseif ( $_REQUEST['action'] == 'al'  )   { add_language();   }
elseif ( $_REQUEST['action'] == 'ellf')   { edit_language_list_form();   }
elseif ( $_REQUEST['action'] == 'elf' )   { edit_language_form();   }
elseif ( $_REQUEST['action'] == 'el'  )   { edit_language();   }
elseif ( $_REQUEST['action'] == 'dl'  )   { delete_language();   }
elseif ( $_REQUEST['action'] == 'aef' )   { add_translation_form();   }
elseif ( $_REQUEST['action'] == 'at'  )   { add_translation();   }
elseif ( $_REQUEST['action'] == 'etlf')   { edit_translation_list_form();   }
elseif ( $_REQUEST['action'] == 'etf' )   { edit_translation_form();   }
elseif ( $_REQUEST['action'] == 'et'  )   { edit_translation();   }
elseif ( $_REQUEST['action'] == 'dt'  )   { delete_translation();   }
else {
  error_log('quran admin: unknown action: ' . $_REQUEST['action']);
  start_page();
}

header('Content-Type: text/html; charset="utf-8"');
echo $HTML;

#########################################################

function start_page() {


  $page_data['title'] = 'Quran Administration';

  gen_html_page('start_page', $page_data);

}

function add_translation_form($errors=array()) {

  $page_data['title'] = 'New Translation';
  $page_data['errors'] = $errors;
  $page_data['languages'] = get_language_options();

  gen_html_page('add_translation_form', $page_data);
}

//get the language options for the language select dropdown
function get_language_options() {
  $language_options = array();

  $res = mysql_query('select translation, language from language_keys where language_key=\'lang_name_english\'');

  if ( ! $res ) {
    error_log('error selecting : ' . mysql_error());
    exit();
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    //array_push($language_options, array('value' => $row['language'], 'option' => $row['translation'] ));  
    $language_options[] = array('value' => $row['language'], 'option' => $row['translation']);
  }

  usort($language_options, 'lang_options_sort');

  return($language_options);
}

// sort the language keys and translations
function lang_options_sort($a, $b) {

  if ( $a['option'] > $b['option'] ) {
    return(1);
  } else if ( $a['option'] < $b['option'] ) {
    return(-1);
  } else {
    return(0);
  }

}

function add_translation() {
  $errors = array();
  $translation_name = trim($_REQUEST['translation_name']);
  $translator = trim($_REQUEST['translator']);
  $translation_language = trim($_REQUEST['translation_language']);

  if ( ! $translation_name ) {
    $errors['translation_name'] = 'Translation Name is required';
  }

  if ( ! $translator ) {
    $errors['translator'] = 'Translator is required';
  }

  if ( ! $translation_language ) {
    $errors['translation_language'] = 'Translation language is required';
  }

  if ( $_FILES['translation_sql_data']['error'] == UPLOAD_ERR_NO_FILE ) {
    $errors['translation_sql_data'] = 'Translation SQL is required';
  }

  if ( count($errors) > 0 ) {
    add_translation_form($errors);
    return;
  }

  $res = mysql_query('insert into translations (translation_name, translator, translation_language) values (\'' .
                          mysql_real_escape_string($translation_name) . '\', \'' .
                          mysql_real_escape_string($translator) . '\', \'' .
                          mysql_real_escape_string($translation_language) .
                          '\')'
                        );


  if ( ! $res ) {
    error_log('error adding translation: ' . mysql_error());
    start_page();
    return;
  }

  $translation_id = mysql_insert_id();

  $errors = load_quran_to_db('translation_sql_data', $translation_id);

  $page_data['title'] = 'Translation added';
  $page_data['errors'] = $errors;

  gen_html_page('start_page', $page_data);

}


function edit_translation() {
  $errors = array();
  $translation_name = trim($_REQUEST['translation_name']);
  $translator = trim($_REQUEST['translator']);
  $translation_language = trim($_REQUEST['translation_language']);
  $translation_id = trim($_REQUEST['translation_id']);

  if ( ! $translation_name ) {
    $errors['translation_name'] = 'Translation Name is required';
  }

  if ( ! $translator ) {
    $errors['translator'] = 'Translator is required';
  }

  if ( ! $translation_language ) {
    $errors['translation_language'] = 'Translation language is required';
  }

  if ( count($errors) > 0 ) {
    edit_translation_form($errors);
    return;
  }

  $res = mysql_query('update translations set translation_name=\'' . mysql_real_escape_string($translation_name) . '\', ' . 
                                              'translator=\'' . mysql_real_escape_string($translator) . '\', ' .
                                              'translation_language=\'' . mysql_real_escape_string($translation_language) . '\' ' .
                                              'where translation_id=\'' . mysql_real_escape_string($translation_id) . '\'' );


  if ( ! $res ) {
    error_log('error adding translation: ' . mysql_error());
    start_page();
    return;
  }

  $errors = load_quran_to_db('translation_sql_data', $translation_id);

  $page_data['title'] = 'Translation added';
  $page_data['errors'] = $errors;

  gen_html_page('start_page', $page_data);

}

function load_quran_to_db($file, $id) {
  $errors = array();
  $output = array();
  $quran_text_table = QURAN_TRANS_PREFIX . $id;
  $upload = SQL_UPLOAD_DIR . 'quran' . $id . '.sql';

  if ( $_FILES[$file]['error'] != UPLOAD_ERR_NO_FILE ) {
    if ($_FILES[$file]['error'] == UPLOAD_ERR_OK ) {
      move_uploaded_file($_FILES[$file]['tmp_name'], $upload);
      $mysql_dump = file_get_contents($upload);
      $mysql_dump = preg_replace('/DROP TABLE IF EXISTS `.*?`/', 'DROP TABLE IF EXISTS `' . $quran_text_table . '`', $mysql_dump);
      $mysql_dump = preg_replace('/CREATE TABLE `.*?`/', 'CREATE TABLE `' . $quran_text_table . '`', $mysql_dump);

      $mysql_dump = preg_replace('/INSERT INTO `.*?`/', '`' . $quran_text_table . '`', $mysql_dump);
      file_put_contents($upload, $mysql_dump);

      exec(MYSQL_BINARY . ' -h ' . MYSQL_HOST . ' -u ' . MYSQL_USER . ' -p' . MYSQL_PASSWORD . ' ' . MYSQL_DB . ' < ' . $upload, $output); 

      if ( count($output) > 0 ) {
        error_log('quran: error importing translation sql tables ' . print_r($output,1));
      }

    } else {
      $errors[$file] = 'Error uploading file: ' . $_FILES[$file]['error'];
      error_log('error with file: ' . $_FILES[$file]['error']);
    }

  } else {
   error_log('no file');
   // checked in the calling function
   //$errors[$file] = 'No file specified';
  } 

  return($errors);

}


function add_language_form($errors=array()) {

  $page_data['title'] = 'New Language';
  $page_data['errors'] = $errors;

  gen_html_page('add_language_form', $page_data);
}

function add_language() {
  $errors = array();
  $language_code = trim($_REQUEST['language_code']);
  $lang_name_english = trim($_REQUEST['lang_name_english']);
  $lang_name_native = trim($_REQUEST['lang_name_native']);

  if ( ! preg_match('/^\w{2}$/', $language_code) ) {
    $errors['language_code'] = 'Language code must be 2 letter code';
  }

  if ( ! $lang_name_english ) {
    $errors['lang_name_english'] = 'Language name ( English ) is required';
  }

  if ( ! $lang_name_native ) {
    $errors['lang_name_native'] = 'Language name ( native ) is required';
  }

  if ( count($errors) > 0 ) {
    add_language_form($errors);
    return;
  }

  $res = mysql_query('insert into language_keys (language, language_key, translation) values ' . 
                          '(\'' .
                          mysql_real_escape_string($language_code) . '\', \'' .
                          'lang_name_english' . '\', \'' .
                          mysql_real_escape_string($lang_name_english) .
                          '\'), ' .

                          '(\'' .
                          mysql_real_escape_string($language_code) . '\', \'' .
                          'lang_name_native' . '\', \'' .
                          mysql_real_escape_string($lang_name_native) .
                          '\'), ' .

                          '(\'' .
                          mysql_real_escape_string($language_code) . '\', \'' .
                          'selectable_app_lang' . '\', \'' .
                          '0' .
                          '\')'

                        );

  if ( ! $res ) {
    error_log('error inserting comment: ' . mysql_error());
  }


  $page_data['title'] = 'Language added';

  gen_html_page('start_page', $page_data);
}

function edit_language_list_form() {

  $res = mysql_query('select translation, language from language_keys where language_key=\'lang_name_english\' order by translation asc');

  if ( ! $res ) {
    error_log('error selecting : ' . mysql_error());
    exit();
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    $language_options[] = array('value' => $row['language'], 'option' => $row['translation']);
  }

  $page_data['title'] = 'Edit Languages';
  $page_data['languages'] = $language_options;

  gen_html_page('edit_language_list_form', $page_data);
}

function edit_language_form() {
  $language_keys = array();
  $lang = $_GET['lang'];

  //$res = mysql_query('select distinct language_key from language_keys'); // to do, use this to pre populate fields
  $res = mysql_query('select translation, language_key from language_keys where language=\'' . mysql_real_escape_string($lang) . '\'');

  if ( ! $res ) {
    error_log('error selecting : ' . mysql_error());
    exit();
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    if ( $row['language_key'] == 'selectable_app_lang' ) { //selectable_app_lang is a special key used to flag if the language is selectable in the app
      $selectable_app_lang = $row['translation'];
    } else {
      array_push($language_keys, array('language_key' => $row['language_key'], 'translation' => $row['translation'] ));
    }

    if ( $row['language_key'] == 'lang_name_english' ) {
      $lang_name_english = $row['translation'];
    }

  }

  usort($language_keys, 'lang_key_sort');

  $page_data['title'] = 'Edit ' . $lang_name_english;
  $page_data['lang_name_english'] = $lang_name_english;
  $page_data['lang'] = $lang;
  $page_data['language_keys'] = $language_keys;
  $page_data['selectable_app_lang'] = $selectable_app_lang;

  gen_html_page('edit_language_form', $page_data);

}

function edit_language() {
  $delete_keys = isset($_POST['delete']) ? $_POST['delete'] : array();
  $language_code = $_POST['lang'];
  $new_language_key = isset($_POST['new_language_key']) ? $_POST['new_language_key'] : '';
  $new_translation = isset($_POST['new_translation']) ? $_POST['new_translation'] : '';
  $lang_name_english = $_POST['lang_name_english'];
  $selectable_app_lang = isset($_POST['selectable_app_lang']) ? $_POST['selectable_app_lang'] = '1' : $_POST['selectable_app_lang'] = '0';

  // delere keys marked to be deleted 
  delete_lang_keys($delete_keys, $language_code);

  // ad the new language key if present
  $language_keys = add_new_lang_key($new_language_key, $new_translation, $language_code);

  //unset post params so we can loop through the rest and add to the db
  unset($_POST['new_language_key']);
  unset($_POST['new_translation']);
  unset($_POST['lang']);
  unset($_POST['action']);
  unset($_POST['delete']);
  unset($_POST['selectable_app_lang']);

  //update the selectable_app_lang flag
  $res = mysql_query('update language_keys set translation=\'' . $selectable_app_lang .  '\'
                      where language_key=\'selectable_app_lang\'
                      and language=\'' . mysql_real_escape_string($language_code) . '\''
                    );

  // update all the existing keys
  foreach ( $_POST as $language_key => $translation ) {
    $language_key = trim($language_key);
    $language_key = preg_replace('/\s+/', '_', $language_key);
    $language_key = preg_replace('/\W/', '', $language_key);
    $language_key = strtolower($language_key);
    $translation = trim($translation);

    $res = mysql_query('update language_keys set translation=\'' . mysql_real_escape_string($translation) .  '\'
                        where language_key=\'' . mysql_real_escape_string($language_key) . '\'
                        and language=\'' . mysql_real_escape_string($language_code) . '\''
                      );

    if ( ! $res || mysql_affected_rows() != 1 ) {
      //error_log('problem updating language key: ' . mysql_error());
    }

    if ( $language_key != 'selectable_app_lang' ) {
      $language_keys[] = array('language_key' => $language_key, 'translation' => $translation);
    }

  }
  
  $language_keys = remove_deleted_keys($language_keys, $delete_keys);

  usort($language_keys, 'lang_key_sort');

  $page_data['title'] = 'Edit ' . $lang_name_english;
  $page_data['lang_name_english'] = $lang_name_english;
  $page_data['lang'] = $language_code;
  $page_data['language_keys'] = $language_keys;
  $page_data['selectable_app_lang'] = $selectable_app_lang;
  gen_html_page('edit_language_form', $page_data);
}

// sort the language keys and translations
function lang_key_sort($a, $b) {

  if ( $a['language_key'] > $b['language_key'] ) {
    return(1);
  } else if ( $a['language_key'] < $b['language_key'] ) {
    return(-1);
  } else {
    return(0);
  }

}

//remove the keys that have just been deleted from the array, so that
//we don't re display them again
function remove_deleted_keys($language_keys, $delete_keys) {
  $new_language_keys = array();

  for ($i=0; $i<count($language_keys); $i++) { 
    $keep = 1;

    foreach ($delete_keys as $delete_key ) {
      if ( $language_keys[$i]['language_key'] ==  $delete_key  ) {
        $keep = 0;
      }
    }

    if ( $keep ) {
      array_push($new_language_keys, array('language_key' => $language_keys[$i]['language_key'], 'translation' => $language_keys[$i]['translation']));
    }

  }

  return($new_language_keys);

}

function delete_lang_keys($language_keys=array(), $lang) {

  foreach ( $language_keys as $language_key ) {
    $res = mysql_query('delete from language_keys where language=\'' .  mysql_real_escape_string($lang) . '\' and language_key=\'' . mysql_real_escape_string($language_key) . '\'');

    if ( ! $res || mysql_affected_rows() != 1 ) {
      error_log('problem deleting language key: ' .  mysql_error());
    }

  }

}

function add_new_lang_key($language_key, $translation, $language_code) {
  $language_keys = array();

  $language_key = trim($language_key);
  $language_key = preg_replace('/\s+/', '_', $language_key);
  $translation = trim($translation);

  if ( $language_key && $translation ) {

    $res = mysql_query('insert into language_keys (language, language_key, translation) values (\'' .
                          mysql_real_escape_string($language_code) . '\', \'' .
                          mysql_real_escape_string($language_key) . '\', \'' .
                          mysql_real_escape_string($translation) . '\')
                          on duplicate key update translation=\'' . mysql_real_escape_string($translation) . '\''
                        );

    if ( ! $res || mysql_affected_rows() != 1 ) {
      error_log('problem adding language key: ' . mysql_error());
    } else {
      $language_keys[] = array('language_key' => $language_key, 'translation' => $translation);
    }

  }

  return($language_keys);

}

function delete_language() {
  $lang = $_GET['lang'];

  $res = mysql_query('delete from language_keys where language=\'' . mysql_real_escape_string($lang) . '\'');

  edit_language_list_form();
}


function edit_translation_list_form() {
  $translations = array();

  //$res = mysql_query('select translation_name, translation_id, translator, translation_language, last_update from translations order by translation_name asc');
  $res = mysql_query('select t.translation_name, t.translation_id, t.translation_language, t.translator, l.translation as translation_language_name from translations t, language_keys l where l.language_key=\'lang_name_native\' and l.language=t.translation_language order by t.translation_language asc');

  if ( ! $res ) {
    error_log('error selecting translation list: ' . mysql_error());
    exit();
  }

  while ( $row = mysql_fetch_assoc($res) ) {
    $translations[] = $row;
  }

  $page_data['title'] = 'Edit Translations';
  $page_data['translations'] = $translations;

  gen_html_page('edit_translation_list_form', $page_data);

}

function edit_translation_form() {
  $id = $_GET['id'];

  $res = mysql_query('select translation_name, translation_id, translator, translation_language, last_update from translations where translation_id=\'' . mysql_real_escape_string($id) . '\'');

  if ( ! $res ) {
    error_log('error selecting translation: ' . mysql_error());
    exit();
  }

  $row = mysql_fetch_assoc($res);

  $page_data['title'] = 'Edit ' . $row['translation_name'];
  $page_data['translation_data'] = $row;
  $page_data['languages'] = get_language_options();

  gen_html_page('edit_translation_form', $page_data);

}

function delete_translation() {
  $id = $_GET['id'];
  $quran_text_table = 'quran_translation_' . $id;

  $res = mysql_query('delete from translations where translation_id=\'' . mysql_real_escape_string($id) . '\'');
  $res = mysql_query('drop table if exists ' . mysql_real_escape_string($quran_text_table) );

  edit_translation_list_form();
}


##############################################
# display code
##############################################

function gen_html_page($page_type, $page_data) {
  global $HTML;

  $styles = get_styles();
  $header = gen_html_header($styles, $page_data);
  $footer = gen_html_footer();

  if ( $page_type == 'start_page' ) {
    $body = gen_start_page_html($page_data);
  } else if ( $page_type == 'add_translation_form' ) {
    $body = gen_add_translation_form_html($page_data);
  } else if ( $page_type == 'add_language_form' ) {
    $body = gen_add_language_form_html($page_data);
  } else if ( $page_type == 'edit_language_list_form' ) {
    $body = gen_edit_language_list_form_html($page_data);
  } else if ( $page_type == 'edit_language_form' ) {
    $body = gen_edit_language_form_html($page_data);
  } else if ( $page_type == 'edit_translation_list_form' ) {
    $body = gen_edit_translation_list_form_html($page_data);
  } else if ( $page_type == 'edit_translation_form' ) {
    $body = gen_edit_translation_form_html($page_data);
  } else {
    error_log('quran admin: unknown page type: ' . $page_type);
  }

  $HTML = $header;
  $HTML .= $body;
  $HTML .= $footer;
}

function get_styles() {
  $styles = '<style type="text/css">
body {
  font-family:verdana,arial,sans-serif;
  font-size:12px;
}

td.form,th.form {
  font-size:14px;
  white-space:nowrap;
}

th {
  text-align: left;
  background-color: #bbbb00;
}

table.query_table {
  border:1px solid green;
  border-collapse:collapse;
}

td.q_s {
  padding:1;
}

td.q_c {
  padding:1;
}

button.edit {
  #background-color: lightgreen;
}

a.delete {
  color: red;
}
</style>
';

  return($styles);

}

function gen_html_header($styles, $page_data) {
  $header = '<html>
  <head>
    <title>' . htmlspecialchars($page_data['title'], ENT_QUOTES, 'UTF-8') . '</title>
' . $styles . '
  </head>
  <body>
';

  return($header);
}

function gen_html_footer() {
  global $SCRIPT;

  $footer = '
<br/><br/>
<hr/>
<a href="' . $SCRIPT . '">Home</a>
</body>
</html>';

  return($footer);
}

function gen_start_page_html($page_data) {
  global $SCRIPT;

  $html = '<h1>Quran App Administration</h1>';

  $html .= '<a href="' . $SCRIPT . '?action=aef">Add Qur\'an Translation</a>';
  $html .= '<br />';
  $html .= '<a href="' . $SCRIPT . '?action=etlf">Edit Qur\'an Translation</a>';
  $html .= '<hr width="20%" align="left"/>';
  $html .= '<a href="' . $SCRIPT . '?action=alf">Add App Language</a>';
  $html .= '<br />';
  $html .= '<a href="' . $SCRIPT . '?action=ellf">Edit App Language</a>';
  $html .= '<hr width="20%" align="left"/>';
  $html .= 'App URL:';
  $html .= '<br />';
  $html .= '<a href="http://apps.binu.net/prod/apps/quran/index.php?trans_id=5&tab=0&lang=en">http://apps.binu.net/prod/apps/quran/index.php?trans_id=5&tab=0&lang=en</a>';

  return($html);

}

function gen_add_language_form_html($page_data) {
  global $SCRIPT;

  $html = '<h1>New App Language</h1>';

  $html .= '<form action="' . $SCRIPT . '" method="post">
<table class="form">
  <tr>
    <td>Language code: </td>
    <td><input type="text" name="language_code" size="30" maxlength="2" /></td>
    <td><small>Use 2 letter <a href="http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">ISO 639-1 code</a></small></td>
  </tr>
  <tr>
    <td>Language name: (English)</td>
    <td><input type="text" name="lang_name_english" size="30" maxlength="' . LANG_TRANS_MAX . '" /></td>
    <td><small>Used in this administration tool</small></td>
  </tr>
  <tr>
    <td>Language name: (Native)</td>
    <td><input type="text" name="lang_name_native" size="30" maxlength="' . LANG_TRANS_MAX . '" /></td>
    <td><small>Used in the mobile app</small></td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" value="Submit" /></td>
  </tr>
</table>

<input type="hidden" name="action" value="al" />

</form>
';
  return($html);

}

function gen_add_translation_form_html($page_data) {
  global $SCRIPT;

  $html = '<h1>Add Qur\'an Translation</h1>';

  $html .= '<form action="' . $SCRIPT . '" method="post" enctype="multipart/form-data">
<table class="form">
  <tr>
    <td>Translation name: </td>
    <td><input type="text" name="translation_name" size="30" maxlength="100" /></td>
    <td><small>Name of the translation that wil appear in the mobile app</small></td>
  </tr>
  <tr>
    <td>Translator: </td>
    <td><input type="text" name="translator" size="30" maxlength="100" /></td>
    <td><small>Name of the translator that wil appear in the mobile app</small></td>
  </tr>
  <tr>
    <td>Translation Language: </td>
    <td>' . html_dropdown($page_data['languages'], 'translation_language') . '</td>
    <td><small>The language of this translation</small></td>
  </tr>
  <tr>
    <td>Translation SQL: </td>
    <td><input type="file" name="translation_sql_data" size="30" maxlength="100" /></td>
    <td><small>The MySQL translation dump from <a href="http://tanzil.info/trans">http://tanzil.info/trans</a></small></td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" value="Submit" /></td>
  </tr>
</table>

<input type="hidden" name="action" value="at" />

</form>
';

  return($html);

}

function gen_edit_language_list_form_html($page_data) {
  global $SCRIPT;

  $html = '<h1>Edit App Languages</h1>';
  $html .= '<table class="form">
<tr>
  <th>id</th>
  <th>name</th>
  <th></th>
  <th></th>
</tr>
';

  foreach ( $page_data['languages'] as $language ) {
    $html .= '
<tr>
  <td><small><strong>' . $language['value'] . '</strong></small></td>
  <td>' . htmlspecialchars($language['option'], ENT_QUOTES, 'UTF-8') . '</td>
  <td><button class="edit" onClick="window.location=\'' . $SCRIPT . '?action=elf&lang=' . $language['value'] . '\'">Edit</button></td>
  <td><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="delete" href="' . $SCRIPT . '?action=dl&lang=' . $language['value'] . '">Delete</small></td>
</tr>' . "\n";
  }

  $html .= '</table>';

  return($html);

}

function gen_edit_language_form_html($page_data) {
  global $SCRIPT;

  if ( $page_data['selectable_app_lang'] ) {
    $selectable_app_lang_checked = 'checked';
  } else {
    $selectable_app_lang_checked = '';
  }

  $html = '<h1>Edit ' . $page_data['lang_name_english'] . '</h1>';
  $html .= '<form action="' . $SCRIPT . '" method="post">
<table class="form">
<tr>
  <td>Selectable App Language</td>
  <td colspan="2"><input type="checkbox" name="selectable_app_lang" value="1" ' . $selectable_app_lang_checked . '/></td>
</tr>
<tr>
  <td colspan="3">&nbsp;</td>
</tr>
<tr>
  <th>Language Key</th>
  <th>Translation</th>
  <th>Delete</th>
</tr>
';

  foreach ( $page_data['language_keys'] as $language ) {
    $html .= '
<tr>
  <td>' . htmlspecialchars($language['language_key'], ENT_QUOTES, 'UTF-8') . '</td>
  <td><input type="text" name="' . htmlspecialchars($language['language_key'], ENT_QUOTES, 'UTF-8') . '" size="30" maxlength="' . LANG_TRANS_MAX . '" value="' . htmlspecialchars($language['translation'], ENT_QUOTES, 'UTF-8') . '" /></td>
  <td><input type="checkbox" name="delete[]" value="' . htmlspecialchars($language['language_key'], ENT_QUOTES, 'UTF-8') . '"></td>
</tr>' . "\n";
  }

  $html .= '
<tr>
  <td><input type="text" name="new_language_key" size="30" maxlength="' . LANG_KEY_MAX . '" /></td>
  <td colspan="2"><input type="text" name="new_translation" size="30" maxlength="' . LANG_TRANS_MAX . '" /></td>
</tr>
<tr>
  <td colspan="3"><input type="submit" value="submit" /></td>
</tr>
';

  $html .= '</table>
<input type="hidden" name="lang" value="' . htmlspecialchars($page_data['lang'], ENT_QUOTES, 'UTF-8') . '" />
<input type="hidden" name="action" value="el" />
</form>';

  return($html);

}

function html_dropdown($option_list, $input_name, $default_option='') {

  $dropdown = '<select name="' . $input_name . '">' . "\n";

  foreach ( $option_list as $option ) {
    $selected = $option['value'] === $default_option ? 'selected' : '';
    $dropdown .= '<option value="' . htmlspecialchars($option['value'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . htmlspecialchars($option['option'], ENT_QUOTES, 'UTF-8') . '</option>' . "\n";
  }

  $dropdown .= '</select>' . "\n";

  return($dropdown);

}

function gen_edit_translation_list_form_html($page_data) {
  global $SCRIPT;

  $html = '<h1>Edit Qur\'an Translation</h1>';
  $html .= '<table class="form">
<tr>
  <th>id</th>
  <th>lang</th>
  <th>name</th>
  <th></th>
  <th></th>
</tr>' . "\n";

  foreach ( $page_data['translations'] as $translation ) {
    $html .= '
<tr>
  <td><small><strong>' . $translation['translation_id'] . '</small></strong></td>
  <td>' . $translation['translation_language'] . '</td>
  <td>' . htmlspecialchars($translation['translation_name'], ENT_QUOTES, 'UTF-8') . '</td>
  <td><button class="edit" onClick="window.location=\'' . $SCRIPT . '?action=etf&id=' . $translation['translation_id'] . '\'">Edit</button></td>
  <td><small>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class="delete" href="' . $SCRIPT . '?action=dt&id=' . $translation['translation_id'] . '">Delete</small></td>
</tr>' . "\n";
  }

  $html .= '</table>';

  return($html);

}

function gen_edit_translation_form_html($page_data) {
  global $SCRIPT;

  $row = $page_data['translation_data'];

  $html = '<h1>Edit Qur\'an Translation</h1>';

  $html .= '<form action="' . $SCRIPT . '" method="post" enctype="multipart/form-data">
<table class="form">
  <tr>
    <td>Translation name: </td>
    <td><input type="text" name="translation_name" size="30" maxlength="100" value="' . htmlspecialchars($row['translation_name'], ENT_QUOTES, 'UTF-8') . '"/></td>
    <td><small>Name of the translation that wil appear in the mobile app</small></td>
  </tr>
  <tr>
    <td>Translator: </td>
    <td><input type="text" name="translator" size="30" maxlength="100" value="' . htmlspecialchars($row['translator'], ENT_QUOTES, 'UTF-8') . '"/></td>
    <td><small>Name of the translator that wil appear in the mobile app</small></td>
  </tr>
  <tr>
    <td>Translation Language: </td>
    <td>' . html_dropdown($page_data['languages'], 'translation_language', $row['translation_language']) . '</td>
    <td><small>The language of this translation</small></td>
  </tr>
  <tr>
    <td>Translation SQL: </td>
    <td><input type="file" name="translation_sql_data" size="30" maxlength="100" /></td>
    <td><small>The MySQL translation dump from <a href="http://tanzil.info/trans">http://tanzil.info/trans</a></small></td>
  </tr>
  <tr>
    <td colspan="3"><input type="submit" value="Submit" /></td>
  </tr>
</table>

<input type="hidden" name="action" value="et" />
<input type="hidden" name="translation_id" value="' . $row['translation_id'] . '" />

</form>
';

  return($html);

}
function db_connect() {

  if ( ! mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD) ) {
    exit('Could not connect: ' . mysql_error());
  }

  if ( ! mysql_select_db(MYSQL_DB) ) {
    exit('Could not select db '. MYSQL_DB . '. ' . mysql_error());
  }

}


?>
