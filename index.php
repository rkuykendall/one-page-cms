<?php
    # Install PSR-0-compatible class autoloader
    spl_autoload_register(function($class){
    	require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
    });

    use \Michelf\MarkdownExtra;

    // Define keys and defaults
    include('config.php');
    $v_keys = array('title', 'subtitle', 'body_md', 'c_bg', 'c_h', 'c_title');
    $v = array();
    $v['title'] = "Default Title";
    $v['subtitle'] = "Default Subtitle";
    $v['body_md'] = "Markdown Body";
    $v['c_bg'] = "#34495e";
    $v['c_h'] = "#8e44ad";
    $v['c_title'] = "white";
    
    $editmode = False;
    if ($_GET["editor"] == $secret) {
      $editmode = True;
    }
    
    // Set default timezone
    date_default_timezone_set('UTC');
 
    try {
      // Create (connect to) SQLite database in file
      $file_db = new PDO('sqlite:data.sqlite3');

      // Set errormode to exceptions
      $file_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Create variables table
      $file_db->exec("CREATE TABLE IF NOT EXISTS variables (key text, value text, PRIMARY KEY (key));");

      // Save new values if posted
      if ($_POST != NULL) {
        foreach ($v_keys as $v_key) {
          if (isset($_POST[$v_key])) {
            $value = $_POST[$v_key];            
            $query = "INSERT OR REPLACE INTO variables (key, value) VALUES (:key, :value);";
            $sth = $file_db->prepare($query);
            $sth->execute(array(':key' => $v_key, ':value' => $value) );
          }
        }
      }

      // Select all data from file db messages table 
      $result = $file_db->query('SELECT * FROM variables');
      
      // Save all k/v pairs into $v array
      foreach($result as $row) {
        $v[$row['key']] = $row['value'];
      }

    } catch(PDOException $e) {
      echo "<strong>PDOException:</strong> ";
      echo $e->getMessage();
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $v['title'] ?></title>
    <link href='http://fonts.googleapis.com/css?family=Roboto:100,400,400italic,500' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>
    
    <link rel="stylesheet" href="http://yui.yahooapis.com/pure/0.4.2/pure-min.css">
    <style>
      body {
        margin: 0; padding: 0;
        background-color: #ecf0f1;
        font-family: 'Source Sans Pro', Helvetica, sans-serif;
      }
      #body {
        font-size: 1.3em;
        line-height: 1.25em;
      }
      #body .round {
        height: 180px;
        width: 180px;
        border-radius: 90px;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0px 0px 5px #000;
        margin: -100px 0px 20px 20px;
        float: right;
      }
      #body .round img {
        width: 180px;
        border: none;
        box-shadow: none;
        margin: 0px;
        padding: 0px;
      }
      
      #body img {
        width: 200px;
        height: auto;
        border-radius: 5px;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: 0px 0px 5px #000;
        margin: 20px;
        margin-right: 0px;
        float: right;
        clear: right;
      }

      h1, h2, h3, h4, h5, h6 {
        font-family: 'Roboto', sans-serif;        
        color: <?= $v['c_h'] ?>;
        font-weight: 400;
        line-height: 1.1em;
        margin: 1em 0em 0em 0em;
      }
      
      #body > h1:first-of-type {
        margin-top: 0em;
      }
      
      blockquote {
        border-left: 5px solid <?= $v['c_h'] ?>;
        margin-left: 0;
        padding: 0.1em 1em;
      }
      
      a, a:link, a:visited,
      a:hover, a:active {
        color: <?= $v['c_h'] ?>;
      }
      
      hr {
        clear: both;
        border-color: transparent;
      }
      
      #header {
          background-image: url("<?= $v['c_bg'] ?>");
          background-position: center center;
          background-size: 100% auto;
          text-align: center;
          text-shadow: 0px 0px 25px black, 0px 0px 40px black, 0px 0px 10px rgba(0,0,0,0.3);
          border-bottom: 4px solid black;
          background-repeat: no-repeat;
          background-color: black;
        }
        #header h1, 
        #header h2 {
          font-size: 2em;
          margin: 0; padding: 0;
          font-weight: 100;
          margin-bottom: 0.2em;
          line-height: 1em;
          color: <?= $v['c_title'] ?>;
        }
        #header h1 {
          margin-left: -0.1em;
          font-size: 10em;
        }
        #header h2 {
          font-size: 4em;
      }
      
      #editor {
        background-color: #2c3e50;
        padding: 50px;
        }
        #editor,
        #editor a:link,
        #editor a:hover,
        #editor a:active,
        #editor a:visited {
          color: #ecf0f1;
        }
        #editor h1 {
          color: white;
          font-weight: 100;
          margin-bottom: 0.2em;
          margin: 0; padding: 0;
          line-height: 1em;
          font-size: 5em;
        }
        textarea {
          height: 285px;
        }
        .pure-g [class *="pure-u"] {
          font-family: 'Source Sans Pro', Helvetica, sans-serif;
      }
      
      
      @media (max-width: 599px) {
          #header, #body {
            padding: 20px;
            padding-bottom: 100px;
          }
          #header h1 {
            font-size: 2em;
          }
          #header h2 {
            font-size: 1em;
          }
        }
        @media (min-width: 600px) and (max-width: 919px) {
          #header, #body {
            padding: 35px;
            padding-bottom: 50px;
          }
          #header h1 {
            font-size: 4em;
          }
          #header h2 {
            font-size: 2em;
          }          
        }
        @media (min-width: 920px) {
          #header, #body {
            padding: 50px;
            padding-bottom: 50px;
          }
          #header h1 {
            font-size: 6em;
          }
          #header h2 {
            font-size: 3em;
          }          
        }
      
    </style>
</head>

<body>
    <?php if ($editmode) { ?>
      <form id="editor" action="" method="post" class="pure-form pure-form-stacked">
        <h1>Site Editor</h1>
        <div class="pure-g">

          <div class="pure-u-1-3">
          
              <label for="title">Title:</label>
              <input type="text" class="pure-input-3-4" name="title" value="<?= $v['title'] ?>">

              <label for="subtitle">Subtitle:</label>
              <input type="text" class="pure-input-3-4" name="subtitle" value="<?= $v['subtitle'] ?>">
            
                <label for="subtitle">Header background:</label>
                <input type="text" class="pure-input-3-4" name="c_bg" value="<?= $v['c_bg'] ?>">

                <label for="subtitle">Header text:</label>
                <input type="text" class="pure-input-3-4" name="c_title" value="<?= $v['c_title'] ?>">

                <label for="subtitle">Content accent:</label>
                <input type="text" class="pure-input-3-4" name="c_h" value="<?= $v['c_h'] ?>">
              
                <a href="http://flatuicolors.com/">Good color reference</a>.
            </div>

          <div class="pure-u-2-3">
            <label for="body_md">
              Body ( Markdown formatted. Syntax guides:
              <a target="_blank" href="http://en.wikipedia.org/wiki/Markdown">Wikipedia</a>, 
              <a target="_blank" href="http://five.squarespace.com/display/ShowHelp?section=Markdown">Squarespace</a>, 
              <a target="_blank" href="http://daringfireball.net/projects/markdown/syntax">Daring Fireball</a>, 
              <a target="_blank" href="http://michelf.ca/projects/php-markdown/extra/">Extra</a>
              )</label>
            <textarea class="pure-input-1" name="body_md"><?= $v['body_md'] ?></textarea>
          </div>

        </div>
        <br />
        <input type="submit" class="pure-button pure-input-1" />
      </form>
    <?php } ?>
    
    <div id="header">
      <h1><?= $v['title'] ?></h1>
      <h2><?= $v['subtitle'] ?></h2>
    </div>
    
    <div id="body">
      <?= MarkdownExtra::defaultTransform($v['body_md']) ?>
    </div>
    
    <!-- Insert Google Analytics here. -->
</body>
</html>
