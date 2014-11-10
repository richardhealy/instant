#BaseKit Instant Theme Creator Plugin

Used in conjuction with the Theme Development Kit.

## Usage

The BaseKit Instant Theme Creator TDK Plugin will automatically create BaseKit ready themes for you based off an existing theme.

### Step-by-step Guide

1. Go to http://github.com/basekit-templates and find a theme you link. Copy the github repository name i.e. `specify` and enter it into the `Git Repo` field.
2. Create a repository your github account. For example, we would create a repository at http://github.com/basekit-templates-fork/ called `testing`. (Replace `basekit-templates-fork` with your github username). IMPORTANT: This repository will need to be empty. Add `testing` to the `New Forked Name` field:
3. Select images for logo and cover image. It is recommended they are the same dimension's as the original images. Any other will look odd in the orginial template.
4. Select the Font face you want for the heading and paragraph text within the site.
5. Add new color swatch values.
6. Press Submit!

You will be redirected to your a new page where you can see the new generated template. It will also appear in your TDK as a new template.

To manually edit the template, go to the templates/ directory in your TDK and edit the files there.

We add various variables to your new template i.e @featureHeightDesktop. Use the ITC previewer to update these to change the value of these variables to make your theme look different.

When you are happy, press `Push to Github` to send the files up to your Github account.

## Installation 

1. Download the zip from here: https://github.com/richardhealy/instant (master branch)
2. Extract the contents of the zip into the  TDK public/plugins/ directory
3. If you have a different TDK url.... i.e. http://localhost:8888 or tdk.local, paste that into line localTDKUrl variable on line 4 in config.php
4. Generate a github token. Look in the settings panel in Github for this
5. Paste the token into config.php (line 6)
6. Add the username where the forked template will be pushed too. You will need to own this account to able to use this feature.
6. make sure you update the `tmp/themes` directory in the instant folder and the `templates` folder in the TDK to be writable via PHP i.e. cd into the TDK dir and run: `chmod +w public/plugins/instant/tmp/themes` and `chmod +w templates/`  
7. Go to  http://YourTdkUrl/plugins/instant/ in your browser
8. Build custom themes!
