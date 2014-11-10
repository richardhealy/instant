<?php
include('config.php');
include('helpers.php');
require_once('Git.php');

$message = '';
$error = '';

// RH: If the original repo name is set and new forked repo name is set then start process. 
if(isset($_POST['originalRepoName']) && isset($_POST['forkedRepoName']) ) {

  // RH: generate a clean directory name
  $directoryName = makeCleanDirectoryName($_POST['forkedRepoName']);

  $originalGitRepoURI = 'http://github.com/'.$originalGithubUser.'/'.$_POST['originalRepoName'].'.git';
  $forkedGitRepoURI = 'http://github.com/'.$forkedGithubUser.'/'.$_POST['forkedRepoName'].'.git';
  
  try {
    // RH: check if the original repo exists
    if (!urlExists($originalGitRepoURI)) {
       throw new Exception("The url $originalGitRepoURI can't be reached.");  
    } 

    // RH: check if the original repo exists
    if (!urlExists($forkedGitRepoURI)) {
       throw new Exception("The url $forkedGitRepoURI can't be reached. Does the repo exist?");  
    }

    if (file_exists(getcwd().'/tmp/themes/'.$directoryName)) {
       throw new Exception("Directory tmp/themes/$directoryName already exists! Choose a different name.");  
    }

    if (file_exists(getcwd().'/../../../templates/'.$directoryName)) {
       throw new Exception("Directory $directoryName in the TDK templates folder already exists! Choose a different name.");
    }

    // RH: try to clone the repo
    try {
      $repo = Git::create(getcwd().'/tmp/themes/'.$directoryName, $originalGitRepoURI);    
    } catch (Exception $e) {
       throw new Exception("Could not clone repo at '/tmp/themes/$directoryName'");
    }

    // RH: opens repo, sets forked origin url, creates develop branch, ready for changes!
    $repo = Git::open(getcwd().'/tmp/themes/'.$directoryName);
    $repo->remote_seturl('origin', 'https://'.$githubToken.'@github.com/'.$forkedGithubUser.'/'.$_POST['forkedRepoName'].'.git');
    $repo->create_branch('develop --track origin/develop');
    $repo->checkout('develop');
    
    // RH: If there is no forked-vars.less file, create one
    if (!(file_exists(getcwd().'/tmp/themes/'.$directoryName.'/forked-vars.less'))) {
       file_put_contents(getcwd().'/tmp/themes/'.$directoryName.'/forked-vars.less', file_get_contents(getcwd().'/forked-vars.less'));
    }

    // RH: Replace the logo and feature images with the images in the images dir
    replaceLogoFile(getcwd().'/tmp/themes/'.$directoryName.'/images/logo.png', $_FILES['logoImageUrl']['tmp_name']);
    replaceFeatureFile(getcwd().'/tmp/themes/'.$directoryName.'/images/feature-bg.jpg', $_FILES['featureImageUrl']['tmp_name']);

    // RH: Process the logo and process the feature image within the twig file
    // processFiles(getcwd().'/tmp/themes/'.$directoryName, $_POST['logoImageUrl'], $_POST['featureImageUrl']);

    // RH: Read in json meta data file 
    $metaDataRaw = file_get_contents(getcwd().'/tmp/themes/'.$directoryName.'/metadata.json');
    $metaData = json_decode($metaDataRaw, true);
    $colorSwatch1 = array("Swatch 1" => array("color1"=>$_POST['color1'],"color2"=>$_POST['color2'],"color3"=>$_POST['color3'],"color4"=>$_POST['color4'],"color5"=>$_POST['color5'],"color6"=>$_POST['color6'],"color7"=>$_POST['color7'])); 
    $metaData["name"] = $_POST['forkedRepoName'];
    $metaData["colorSwatches"] = $colorSwatch1;
    $metaData["fontSwatch"]["font1"]["font-family"] = $_POST['headerFont']; // h1
    $metaData["fontSwatch"]["font2"]["font-family"] = $_POST['headerFont']; // h2
    $metaData["fontSwatch"]["font3"]["font-family"] = $_POST['headerFont']; // h3
    $metaData["fontSwatch"]["font4"]["font-family"] = $_POST['headerFont']; // h4
    $metaData["fontSwatch"]["font5"]["font-family"] = $_POST['paragraphFont'];  // p, span, everything!
    $metaData["fontSwatch"]["font6"]["font-family"] = $_POST['paragraphFont']; //nav link
    $metaData["fontSwatch"]["font7"]["font-family"] = $_POST['paragraphFont']; // button
    $metaData["fontSwatch"]["font8"]["font-family"] = $_POST['headerFont']; // feature title
    $metaData["fontSwatch"]["font9"]["font-family"] = $_POST['paragraphFont']; // feature description
    $metaData["fontSwatch"]["font10"]["font-family"] = $_POST['headerFont']; // logo text
    file_put_contents(getcwd().'/tmp/themes/'.$directoryName.'/metadata.json', str_replace('\\\\\\', '\\', str_replace('\/', '/', json_encode($metaData, JSON_PRETTY_PRINT))));

    // RH: Copy the folder to templates directory
    recurseCopy(getcwd().'/tmp/themes/'.$directoryName, getcwd().'/../../../templates/'.$directoryName);
  
    // RH: Remove the tmp theme folder as we'll work from the TDK version from no on    
    recursiveRmdir(getcwd().'/tmp/themes/'.$directoryName);

    header("Location: preview.php?template=".$directoryName);
    exit;

  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}

?>
<!DOCTYPE html>
<html class="no-js">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>HTML Template to BaseKit Template</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap core CSS -->
        <link href="bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
<?php
        if(strlen($message) > 0) {
?>
            <div class="alert-message success">
                <p><?php echo($message) ?></p>
            </div>
<?php } else if(strlen($error) > 0) { ?>
            <div class="alert-message error">
                <p><strong><?php echo($error) ?></strong></p>
            </div>
<?php } ?>
            <!-- Add your site or application content here -->
            <h1>Instant BaseKit Theme Creator!</h1>
            <form role="form" action="index.php" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="originalRepoName">Git Repo i.e yelaudio</label>
                <input type="text" name="originalRepoName" id="originalRepoName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="forkedRepoName">New Forked Name (must match empty fork repo name)</label>
                <input type="text" name="forkedRepoName" id="forkedRepoName" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="logoImageUrl">Logo Image URL</label>
                <input type="file" name="logoImageUrl" id="logoImageUrl" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="featureImageUrl">Feature Image URL</label>
                <input type="file" name="featureImageUrl" id="featureImageUrl" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="headerFont">Choose Header Font</label>
                <select class="form-control" id="headerFont" name="headerFont" required>
                  <option value='\"Droid Sans\", Helvetica, Arial, sans-serif'>Droid Sans</option>
                  <option value='\"Arvo\", Georgia, serif'>Arvo</option>
                  <option value='\"Corben\", Georgia, serif'>Corben</option>
                  <option value='\"Lobster\", Verdana, sans-serif'>Lobster</option>
                  <option value='\"Droid Serif\", Georgia, serif'>Droid Serif</option>
                  <option value='\"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Raleway</option>
                  <option value='\"Goudy Bookletter 1911\", \"Times New Roman\", Georgia, serif'>Goudy Bookletter 1911</option>
                  <option value='\"Abril Fatface\", \"Palatino Linotype\", serif'>Abril Fatface</option>
                  <option value='\"Yanone Kaffeesatz\", Georgia, serif'>Yanone Kaffeesatz</option>
                  <option value='\"Hammersmith One\", Tahoma, Verdana, sans-serif'>Hammersmith One</option>
                  <option value='\"Lato\", Geneva, Tahoma, sans-serif'>Lato</option>
                  <option value='\"PT Sans Narrow\", Arial, sans-serif'>PT Sans Narrow</option>
                  <option value='\"Open Sans\", Helvetica, Verdana, sans-serif'>Open Sans</option>
                  <option value='\"Open Sans Condensed\", Arial, Helvetica, sans-serif'>Open Sans Condensed</option>
                  <option value='\"Old Standard TT\", \"Book Antiqua\", \"Palatino Linotype\", serif'>Old Standard TT</option>
                  <option value='\"Merriweather\", Georgia, serif'>Merriweather</option>
                  <option value='\"Montserrat\", \"Trebuchet MS\", Helvetica, sans-serif'>Montserrat</option>
                  <option value='\"Roboto\", Geneva, \"Lucida Console\", sans-serif'>Roboto</option>
                  <option value='\"Titillium Web\", Geneva, Tahoma, sans-serif'>Titillium Web</option>
                  <option value='\"Karla\", Verdana, Geneva, sans-serif'>Karla</option>
                  <option value='\"Oswald\", Arial, sans-serif'>Oswald</option>
                  <option value='\"Glegoo\", Monaco, \"Lucida Console\", monospace'>Glegoo</option>
                  <option value='\"Vollkorn\", Georgia, serif'>Volkorn</option>
                  <option value='\"Courgette\", \"Lucida Grande\", sans-serif'>Courgette</option>
                  <option value='\"Abel\", \"Lucida Console\", monospace'>Abel</option>
                  <option value='\"Sniglet\", Geneva, Gadget, sans-serif'>Sniglet</option>
                  <option value='\"Ubuntu\", Candara, Futura, sans-serif'>Ubuntu</option>
                  <option value='\"PT Sans\", Tahoma, Geneva, sans-serif'>PT Sans</option>
                  <option value='\"PT Serif\", Georgia, serif'>PT Serif</option>
                  <option value='\"PT Mono\", \"Courier New\", Courier, monospace'>PT Mono</option>
                  <option value='\"Times New Roman\", serif'>Times New Roman</option>
                  <option value='\"Tahoma\", sans-serif'>Tahoma</option>
                  <option value='\"Trebuchet\", Helvetica, sans-serif'>Trebuchet</option>
                  <option value='\"Verdana\", sans-serif'>Verdana</option>
                  <option value='\"Palatino\", serif'>Palatino</option>
                  <option value='\"Impact\", sans-serif'>Impact</option>
                  <option value='\"Helvetica\", Arial, sans-serif'>Helvetica</option>
                  <option value='\"Myriad Pro\", Helvetica, sans-serif'>Myriad Pro</option>
                  <option value='\"Georgia\", serif'>Georgia</option>
                  <option value='\"Futura\", Helvetica, sans-serif'>Futura</option>
                  <option value='\"Courier New\", monospace'>Courier New</option>
                  <option value='\"Arial\", sans-serif'>Arial</option>
                  <option value='\"Quicksand\", \"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Quicksand</option>
                  <option value='\"Josefin Sans\", \"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Josefin Sans</option>
                  <option value='\"Montserrat Alternates\", \"Montserrat\", \"Trebuchet MS\", Helvetica, sans-serif'>Montserrat alternates</option>
                  <option value='\"Oleo Script\", \"Lobster\", Verdana, sans-serif'>Oleo Script</option>
                  <option value='\"Cabin\", \"Oswald\", Arial, sans-serif'>Cabin</option>
                  <option value='\"Squada One\", \"Oswald\", Arial, sans-serif'>Squada One</option>
                  <option value='\"Pacifico\", \"Lobster\", Verdana, sans-serif'>Pacifico</option>
                  <option value='\"Oxygen\", \"Helvetica\", Arial, sans-serif'>Oxygen</option>
                  <option value='\"Vampiro One\", \"Courier New\", serif'>Vampiro One</option>
                  <option value='\"Bree Serif\", \"Merriweather\", Georgia, serif'>Bree Serif</option>
                  <option value='\"Alfa Slab One\", \"Oswald\", Arial, sans-serif'>Alfa Slab One</option>
                  <option value='\"Amatic SC\", \"Lobster\", Verdana, sans-serif'>Amatic SC</option>
                  <option value='\"Signika\", Candara, Futura, sans-serif'>Signika</option>
                  <option value='\"Crimson Text\", \"Times New Roman\", serif'>Crimson Text</option>
                  <option value='\"Lora\", \"Georgia\", serif'>Lora</option>
                  <option value='\"Playfair Display\", Times New Roman, serif'>Playfair Display</option>
                  <option value='\"Merriweather Sans\", \"Lato\", Helvetica, sans-serif'>Merriweather Sans</option>
                  <option value='\"Quantico\", \"Hammersmith One\", Helvetica, sans-serif'>Quantico</option>
                </select>
              </div>
              <div class="form-group">
                <label for="paragraphFont">Choose Paragraph Font</label>
                <select class="form-control" id="paragraphFont" name="paragraphFont" required>
                  <option value='\"Droid Sans\", Helvetica, Arial, sans-serif'>Droid Sans</option>
                  <option value='\"Arvo\", Georgia, serif'>Arvo</option>
                  <option value='\"Corben\", Georgia, serif'>Corben</option>
                  <option value='\"Lobster\", Verdana, sans-serif'>Lobster</option>
                  <option value='\"Droid Serif\", Georgia, serif'>Droid Serif</option>
                  <option value='\"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Raleway</option>
                  <option value='\"Goudy Bookletter 1911\", \"Times New Roman\", Georgia, serif'>Goudy Bookletter 1911</option>
                  <option value='\"Abril Fatface\", \"Palatino Linotype\", serif'>Abril Fatface</option>
                  <option value='\"Yanone Kaffeesatz\", Georgia, serif'>Yanone Kaffeesatz</option>
                  <option value='\"Hammersmith One\", Tahoma, Verdana, sans-serif'>Hammersmith One</option>
                  <option value='\"Lato\", Geneva, Tahoma, sans-serif'>Lato</option>
                  <option value='\"PT Sans Narrow\", Arial, sans-serif'>PT Sans Narrow</option>
                  <option value='\"Open Sans\", Helvetica, Verdana, sans-serif'>Open Sans</option>
                  <option value='\"Open Sans Condensed\", Arial, Helvetica, sans-serif'>Open Sans Condensed</option>
                  <option value='\"Old Standard TT\", \"Book Antiqua\", \"Palatino Linotype\", serif'>Old Standard TT</option>
                  <option value='\"Merriweather\", Georgia, serif'>Merriweather</option>
                  <option value='\"Montserrat\", \"Trebuchet MS\", Helvetica, sans-serif'>Montserrat</option>
                  <option value='\"Roboto\", Geneva, \"Lucida Console\", sans-serif'>Roboto</option>
                  <option value='\"Titillium Web\", Geneva, Tahoma, sans-serif'>Titillium Web</option>
                  <option value='\"Karla\", Verdana, Geneva, sans-serif'>Karla</option>
                  <option value='\"Oswald\", Arial, sans-serif'>Oswald</option>
                  <option value='\"Glegoo\", Monaco, \"Lucida Console\", monospace'>Glegoo</option>
                  <option value='\"Vollkorn\", Georgia, serif'>Volkorn</option>
                  <option value='\"Courgette\", \"Lucida Grande\", sans-serif'>Courgette</option>
                  <option value='\"Abel\", \"Lucida Console\", monospace'>Abel</option>
                  <option value='\"Sniglet\", Geneva, Gadget, sans-serif'>Sniglet</option>
                  <option value='\"Ubuntu\", Candara, Futura, sans-serif'>Ubuntu</option>
                  <option value='\"PT Sans\", Tahoma, Geneva, sans-serif'>PT Sans</option>
                  <option value='\"PT Serif\", Georgia, serif'>PT Serif</option>
                  <option value='\"PT Mono\", \"Courier New\", Courier, monospace'>PT Mono</option>
                  <option value='\"Times New Roman\", serif'>Times New Roman</option>
                  <option value='\"Tahoma\", sans-serif'>Tahoma</option>
                  <option value='\"Trebuchet\", Helvetica, sans-serif'>Trebuchet</option>
                  <option value='\"Verdana\", sans-serif'>Verdana</option>
                  <option value='\"Palatino\", serif'>Palatino</option>
                  <option value='\"Impact\", sans-serif'>Impact</option>
                  <option value='\"Helvetica\", Arial, sans-serif'>Helvetica</option>
                  <option value='\"Myriad Pro\", Helvetica, sans-serif'>Myriad Pro</option>
                  <option value='\"Georgia\", serif'>Georgia</option>
                  <option value='\"Futura\", Helvetica, sans-serif'>Futura</option>
                  <option value='\"Courier New\", monospace'>Courier New</option>
                  <option value='\"Arial\", sans-serif'>Arial</option>
                  <option value='\"Quicksand\", \"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Quicksand</option>
                  <option value='\"Josefin Sans\", \"Raleway\", \"Lucida Grande\", Helvetica, sans-serif'>Josefin Sans</option>
                  <option value='\"Montserrat Alternates\", \"Montserrat\", \"Trebuchet MS\", Helvetica, sans-serif'>Montserrat alternates</option>
                  <option value='\"Oleo Script\", \"Lobster\", Verdana, sans-serif'>Oleo Script</option>
                  <option value='\"Cabin\", \"Oswald\", Arial, sans-serif'>Cabin</option>
                  <option value='\"Squada One\", \"Oswald\", Arial, sans-serif'>Squada One</option>
                  <option value='\"Pacifico\", \"Lobster\", Verdana, sans-serif'>Pacifico</option>
                  <option value='\"Oxygen\", \"Helvetica\", Arial, sans-serif'>Oxygen</option>
                  <option value='\"Vampiro One\", \"Courier New\", serif'>Vampiro One</option>
                  <option value='\"Bree Serif\", \"Merriweather\", Georgia, serif'>Bree Serif</option>
                  <option value='\"Alfa Slab One\", \"Oswald\", Arial, sans-serif'>Alfa Slab One</option>
                  <option value='\"Amatic SC\", \"Lobster\", Verdana, sans-serif'>Amatic SC</option>
                  <option value='\"Signika\", Candara, Futura, sans-serif'>Signika</option>
                  <option value='\"Crimson Text\", \"Times New Roman\", serif'>Crimson Text</option>
                  <option value='\"Lora\", \"Georgia\", serif'>Lora</option>
                  <option value='\"Playfair Display\", Times New Roman, serif'>Playfair Display</option>
                  <option value='\"Merriweather Sans\", \"Lato\", Helvetica, sans-serif'>Merriweather Sans</option>
                  <option value='\"Quantico\", \"Hammersmith One\", Helvetica, sans-serif'>Quantico</option>
                </select>
              </div>
              <hr />
              <div class="row">
                <div class="col-md-2">
                  <h3>Color Swatch 1</h3>
                  <p>Add swatch HEX here</p>
                </div>
                <div class="col-md1 col-md2 col-md-1" style="display: block;"><div class="form-group"><label class="">Background</label><input type="text" class="form-control" name="color1" placeholder="#ffffff" required></div></div>
                <div class="col-md1 col-md2 col-md-1" style="display: block;"><div class="form-group"><label class="">Contrast</label><input type="text" class="form-control" name="color2" placeholder="#000000" required></div></div>
                <div class="col-md2 col-md1 col-md-1" style="display: block;"><div class="form-group"><label>Button</label><input type="text" class="form-control" name="color3" placeholder="#000000" required></div></div>
                <div class="col-md1 col-md-1" style="display: block;"><div class="form-group"><label>H1 - H4</label><input type="text" class="form-control" name="color4" placeholder="#000000" required></div></div>
                <div class="col-md1 col-md-1" style="display: block;"><div class="form-group"><label>Paragraph</label><input type="text" class="form-control" name="color5" placeholder="#000000" required></div></div>
                <div class="col-md-1"><div class="form-group"><label>Nav Link</label><input type="text" class="form-control" name="color6" placeholder="#000000" required></div></div>
                <div class="col-md-1"><div class="form-group"><label class="">Header</label><input type="text" class="form-control" name="color7" placeholder="#000000" required></div></div>
              </div>
              <hr />
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>

<h1>BaseKit Instant Theme Creator Plugin</h1>

<p>Used in conjuction with the Theme Development Kit.</p>

<h2>Usage</h2>

<p>The BaseKit Instant Theme Creator TDK Plugin will automatically create BaseKit ready themes for you based off an existing theme.</p>

<h3>Step-by-step Guide</h3>

<ol>
<li>Go to http://github.com/basekit-templates and find a theme you link. Copy the github repository name i.e. <code>specify</code> and enter it into the <code>Git Repo</code> field.</li>
<li>Create a repository your github account. For example, we would create a repository at http://github.com/basekit-templates-fork/ called <code>testing</code>. (Replace <code>basekit-templates-fork</code> with your github username). IMPORTANT: This repository will need to be empty. Add <code>testing</code> to the <code>New Forked Name</code> field:</li>
<li>Select images for logo and cover image. It is recommended they are the same dimension's as the original images. Any other will look odd in the orginial template.</li>
<li>Select the Font face you want for the heading and paragraph text within the site.</li>
<li>Add new color swatch values.</li>
<li>Press Submit!</li>
</ol>


<p>You will be redirected to your a new page where you can see the new generated template. It will also appear in your TDK as a new template.</p>

<p>To manually edit the template, go to the templates/ directory in your TDK and edit the files there.</p>

<p>We add various variables to your new template i.e @featureHeightDesktop. Use the ITC previewer to update these to change the value of these variables to make your theme look different.</p>

<p>When you are happy, press <code>Push to Github</code> to send the files up to your Github account.</p>

<h2>Installation</h2>

<ol>
<li>Download the zip from here: https://github.com/richardhealy/instant (master branch)</li>
<li>Extract the contents of the zip into the  TDK public/plugins/ directory</li>
<li>If you have a different TDK url.... i.e. http://localhost:8888 or tdk.local, paste that into line localTDKUrl variable on line 4 in config.php</li>
<li>Generate a github token. Look in the settings panel in Github for this</li>
<li>Paste the token into config.php (line 6)</li>
<li>Add the username where the forked template will be pushed too. You will need to own this account to able to use this feature.</li>
<li>make sure you update the <code>tmp/themes</code> directory in the instant folder and the <code>templates</code> folder in the TDK to be writable via PHP i.e. cd into the TDK dir and run: <code>chmod +w public/plugins/instant/tmp/themes</code> and <code>chmod +w templates/</code></li>
<li>Go to  http://YourTdkUrl/plugins/instant/ in your browser</li>
<li>Build custom themes!</li>
</ol>

<h3>The BaseKit Team</h3>
        </div>

    </body>
</html>