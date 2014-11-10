<?php

// This is the URL that will be required to preview the template
$localTDKUrl = 'http://localhost/';
// This is a token that will need to be set up in you github account.
$githubToken = 'ENTERTOKENHERE';
// This is the original basekit theme account on github. default: basekit-templates
$originalGithubUser = 'basekit-templates';
// This is the original basekit theme account on github. default: basekit-templates-fork
$forkedGithubUser = 'basekit-templates-fork';

if ($localTDKUrl == '') {
	echo('Please setup config. $localTDKUrl is not set.');
	exit;
}
if ($githubToken == '') {
	echo('Please setup config. $githubToken is not set.');
	exit;
}
if ($originalGithubUser == '') {
	echo('Please setup config. $originalGithubUser is not set. .i.e If the original github account is `http://github.com/basekit-templates` this value will be `basekit-templates`');
	exit;
}
if ($forkedGithubUser == '') {
	echo('Please setup config. $forkedGithubUser is not set. This is the account where the forked template will be pushed up to. .i.e If the forked github account is `http://github.com/basekit-templates-fork` this value will be `basekit-templates-fork`');
	exit;
}