#BLogic Web Framework

#248
- Updates and improvements to CSRF functions.
- Other security updates.

#247
- Removed 3rd party libraries. Use composer instead.
- Minor fixes to framework.

#246
- Updates and changes to protect against CSRF and XSS attacks.
- All output involving strings from formValueForKeyPath(), formValueForKey(), and field() now escape HTML by default but can be turned off if required.

#244 - #245
- Refer to GIT Repo for information on fixes.

#Version 243
- Minor bug fixes to JSON datasource deleting and admin feature.

#Version 242
- Various minor improvements and tweaks.
- New JSON data source for fast prototyping and small production data sets.
- Adjustments to installation subdirectory for many of the third-party features.


#Version 241
- Nearly 30 new feature modules added.
- Various bug fixes and tweaks.
- New List template component.
- $form->select() now accecepts a custom default value via the 'value' key as per other inputs.
- BLArrayUtils::first() method for quickly access first item in array.
- PLController template binding now accessed via bindTemplate() method.
- BLGenericRecord::newRecordOfType() now has a third parameter to optionally take an array of field names and values.
- Custom server communication code removed from PLEmail. PEAR is now always used for authenticated sending.
- Import of all files within the framework 'Utils' subfolder is now handled automatically via a glob loop.
- Auto-ban functions moved into their own utils file.
- Removal of old curlPostAsync() method.
- Ability to generate a new session ID for an existing session. Useful security measure to help prevent session fixation.
- Feature modules now support a description file. <blfeature -info feature_name>
- Bootstrap feature updated and including the right files. 
- Updates to standard error and maintenence pages. 
- robots.txt file added to basic template.
- Scriptaculous and other javascripts that used to be part of the standard app template have now been moved into their own features.


#Version 240
- form-validation feature bug fix.

###Version 239
- Doctype tag in error.html switched to standard html5 version.
- Update to itemsSplitIntoHeirarchyWithKeysAndStartPos() to handle keypaths. Direct access to the $vars structure switched to field() accessor.
- Bug fix to banning options.
- feature listing placed into bash script.


###Version 238
- New feature: form_validation, provides basic javascript form validation adhering to the bootstrap convention.
- New feature: bootstrap, installs bootstrap 3 files in the project.
- Various adjustments and fixes to FormBuilder.

###Version 237
- FormUtils in standard template moved to cart and admin feature as they are the last two modules that utilise the methods within. The rest has been reproduced in a new format within the framework.
- Updates to blcomp and blentity production.

###Version 236
- New FormBuilder
- Support for PHP's built-in web server (blserve). 
- Major changes to the starter template.
- New features. 
- Updates to various commands to add more automated and more customised output.
- Various bug fixes. 


###Version 235
- New session system to replace PHP's built-in one. Current PHP versions are experiencing unexplained data loss on valid sessions after approximately 2 hours of inactivity. This system is designed to replace the same functionality and guarantee controlled deletion of said data.
- FontAwesome feature.
- Bug fix to serialisation when saving a new record.
- Products feature.
- Default product fetch in the grid component.
- Basic gallery wrapper component to illustrate a good starting point for using the grid.
- Feature for logging user browser data.
- Adjustment to cookie setting so it sends the same one with ajax calls.
- Auto-banning of IPs after login attempts surpasses auto-ban threshold.
- Various bug fixes.

###Version 234
- Bug fix to lastBuildDate tag in feed component.
- Removed debug printout that caused errors.
- Default working change on AdminLogin template.
- Fix to goToLocation() that makes sure the domain name is always prefixed to the request page name.
- Sort order defaults in template list view component.

###Version 233
- logout.php takes a parameter to optionally redirect to admin login page.
- Removal of plaintext password print out in debug logs.
- Generic down arrow head icon to be used in control widgets like drop-down menus.
- Major updates to styling and default coding of Admin feature. 
- tab bar expended to 100% of window.
- logo disabled and app name displayed in header.
- css definitions moved around.
- Dashboard placeholder component added.
- Login page defaults to AdminUser entity.
- Default tabs in admin page wrapper reduced.
- jQuery updated to version 2.1.4.
- goToLocation() changed to take an optional parameters list.
- bladd adjusted to default to EditViewWrapper and ListViewWrapper if no parent class is given.
- blfeature now ignores .DS_Store files.
- _call() method disabled for now.
- select-holder code removed.

###Version 232
- Fix to default page name in login ajax call.
- safeJSON and implode methods for javascript in general.js
- New goToLocation() method in PLController. Handles not only header redirection but also automatic caching and reloading of a page afterward. In this way the benefits of pageWithName can be used with perma-links.
- Enhanced debugging and strict component comparison on handleRequest() to stop recursive nesting.
- Improved $useHTML usage in template.
- Styling and scripting tweaks.


###Version 231
- Refinement to logout.php steps and safety checks.
- High priority functions moved to top of class.
- Bug fix to field() changes in previous commit.
- Update to field() to check and take priority of native class methods over native fields.
- Update to fields() to check and take priority of native class methods over native fields and to work with key paths.
- Correction of template syntax for PLController.
- Updated MySQL connection debug message.
- Method call templateNameBasedOnDevice() removed from standard template call as responsive design is the preferred method of adjusting layouts at present.
- Tabbing adjustment.
- Removal of old images and icons that are rarely, if ever, used. Form buttons also updated to use plain CSS instead of old gradient graphic.
- mysql_date() now takes an optional true/false parameter to return only the date with no time.
- Fixes to tabbing in source.
- Bug fix for debugging out on FM DS validation errors.
- Forgot password link removed as it shouldn't be needed on the Admin login.
- AdminLogin updated to use password hashing.
- Spelling correction in error message.

###Version 230
- Added auto page and action banning.
- Bug fix to error detection on failed queries within mysql data source.
- Slight debugging changes.

###Version 229
... no info past this point, consult repository.