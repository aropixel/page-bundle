# UPGRADE FROM `v0.X.X` TO `v1.0.0`

## Migrations

**Becarful : table names have been changed.** Every table is now prefixed by default with "aropixel_". You can override this settings by copying mapping files in your project. 

Automatic generation migrations will generate **DROP** and **CREATE TABLE** lines. 

In order to avoid data loss, you should modify your generated migration files whith following lines:
    * `RENAME TABLE page TO aropixel_page`
    * `RENAME TABLE page_image TO aropixel_page_image`
    * `RENAME TABLE page_image_crop TO aropixel_page_image_crop`

## Entities

Page galleries have been removed, as a gallery is simply a collection of PageImage entities.

With new externalized mapping, you can now extend PageImage and PageImageCrop to build very quickly your own page galleries.
