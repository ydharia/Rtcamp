# Rtcamp

In that My website you can Show your Album Image of Your Facebook Profile. First of You can Authenticate Facebook Login and then After You can show Your Facebook Album Image on the Webpage and You can also see the Photos in the Slider.

Part 1:

User Login to the Website using Facebook credential. Asking for permission of name,email,photos. Application retrieve all the Album Image of the Login User.

Part 2:

Album's will be display with a thumbnail and name. whenever user click the thumbnai then Preview that Album Image in a Fullscreen Slideshow.

A 'Download Album Link' is Putting on Each Album thumbnail whenever the User click on that button then in the background process all the Image of that album is Put on folder and create the zip of that Album with the name of Album name.

A checkbox is displayed in each album. when the user click the 'Selected Album Download' then All the selected Album put on the Folder and creating the zip.

'Download All Albums' link also Available to the Top of Page.using that you can dowanload all the Album into zip.

All the Time while Download Process is not Completing the Progressbar show you current downloading photo of your album.

Part 3:

A 'Move' Link is display on Each Album thumbnail and when the User click that link the Album Image is move on the Google Drive. But the user is Sign into Google for the First Time for moving the album into the Google Drive.After that Google can not Take Any Permission for Moving the Album to the google drive.

Using the checkbox user can select multiple Album and click the 'Move to Selected Album' then all the Selected Album move to the Google drive of the User.

'Move All' link is Top of the Page. It can Move all the Album to the Google Drive.

* How Google Drive Moving Work.

When user click on move album(s) then moving work is assigned to Cron job so user can logout or shutdown device without waiting for complete moving process of albums.

Platforms : PHP

Framework : Codeignitor

Scripting Language : JQuery, JQuery AJAX, JavaScript

Library Used :

    Facebook PHP SDK 
    Google Drive API
    Crontab
