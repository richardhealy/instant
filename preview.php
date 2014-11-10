<?php

include('config.php');
include('helpers.php');
require_once('Git.php');

$message = '';
$error = '';

if(
  isset($_GET['template']) && 
  isset($_POST['headerHeight']) &&
  isset($_POST['footerHeight']) &&
  isset($_POST['mainContainerWidth']) &&
  isset($_POST['featureHeightDesktop']) &&
  isset($_POST['featureHeightTablet']) &&
  isset($_POST['featureHeightPhone'])
) {

  $less  = "@headerHeight: ".$_POST['headerHeight'].";".PHP_EOL;
  $less .= "@footerHeight: ".$_POST['footerHeight'].";".PHP_EOL;
  $less .= "@mainContainerWidth: ".$_POST['mainContainerWidth'].";".PHP_EOL;
  $less .= "@featureHeightDesktop: ".$_POST['featureHeightDesktop'].";".PHP_EOL;
  $less .= "@featureHeightTablet: ".$_POST['featureHeightTablet'].";".PHP_EOL;
  $less .= "@featureHeightPhone: ".$_POST['featureHeightPhone'].";".PHP_EOL;

  file_put_contents(getcwd().'/../../../templates/'.$_GET['template'].'/forked-vars.less', $less);
} else if(isset($_GET['template']) && isset($_GET['push'])) {
  // RH: If template is set and read to push to Github

  // RH: generate a clean directory name
  $directoryName = makeCleanDirectoryName($_GET['template']);

  $forkedGitRepoURI = 'http://github.com/'.$forkedGithubUser.'/'.$_GET['template'].'.git';
  
  try {
    // RH: check if the original repo exists
    if (!urlExists($forkedGitRepoURI)) {
       throw new Exception("The url $forkedGitRepoURI can't be reached. Does the repo exist?");  
    }

    // RH: opens repo, sets forked origin url, creates develop branch, ready for changes!
    $repo = Git::open(getcwd().'/../../../templates/'.$directoryName);
    // RH: commits changes to develop, and pushes it!
    $repo->add('.');
    $repo->commit('Changed the logo, feature image and swatches');
    $repo->push('origin', 'develop');
    $repo->push('origin', 'master');

    header("Location: success.php");
    exit;
    
  } catch (Exception $e) {
    $error = $e->getMessage();
    echo($error);
    exit;
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
        
        <style type="text/css">
        .frame-container {
          position:absolute; 
          top:0; 
          right:0; 
          left:250px; 
          bottom:0; 
          overflow-y:scroll;
        }

        .frame {
          border: 0; 
          position:absolute; 
          top:0; 
          left:0; 
          right:0; 
          bottom:0; 
          width:100%; 
          height:100%;
        }

        .panel {
          border-top:1px solid #dddddd; 
          z-index:1000;
          position:absolute; 
          top:0; 
          right:auto; 
          left:0; 
          bottom:0; 
          height:100%; 
          width:250px; 
          overflow:hidden; 
          background-color:#ececec;
        }

        .panel-content {
          padding: 10px;
          display:flex;
          align-items:center;
          justify-content:center;
          height: 100%;
        }

        .slider-selection {
          background: #BABABA;
        }
        </style>
    </head>
    <body >
        <div class="frame-container">
          <iframe src="<?php echo($localTDKUrl); ?>/index.php?template=<?php echo($_GET['template']); ?>&pageType=home" class="frame"></iframe>
        </div>
        <div class="panel">
          <div class="panel-content">
            <form role="form" action="preview.php?template=<?php echo($_GET['template']); ?>" method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label for="headerHeight">Header Height</label>
                <input class="form-control" name="headerHeight" id="headerHeight" value="40px"/>
              </div>
              <div class="form-group">
                <label for="footerHeight">Footer Height</label>
                <input class="form-control" name="footerHeight" id="footerHeight" value="100px"/>
              </div>
              <div class="form-group">
                <label for="mainContainerWidth">Main Container Width</label>
                <input class="form-control" name="mainContainerWidth" id="mainContainerWidth" value="960px"/>
              </div>
              <div class="form-group">
                <label for="featureHeightDesktop">Feature Height Desktop</label>
                <input class="form-control" name="featureHeightDesktop" id="featureHeightDesktop" value="350px"/>
              </div>
              <div class="form-group">
                <label for="featureHeightTablet">Feature Height Tablet</label>
                <input class="form-control" name="featureHeightTablet" id="featureHeightTablet"value="300px"/>
              </div>
              <div class="form-group">
                <label for="featureHeightPhone">Feature Height Phone</label>
                <input class="form-control" name="featureHeightPhone" id="featureHeightPhone" value="250px"/>
              </div>
              <hr />
              <button type="submit" class="btn btn-primary">Submit</button>
              <div class="form-group">
                <button type="button" class="btn btn-primary btn-lg" onclick="javascript:window.location = 'preview.php?template=<?php echo($_GET['template']); ?>&push=1'">Publish to Github</button>
              </div>
            </form>
          </div>
        </div>
    </body>
</html>