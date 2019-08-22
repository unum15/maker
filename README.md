I found when using Lumen there are several files I have to make that have no corresponding artisan command(Even though many of them do for Laravl). So I created a package that creates these common files based off an existing table.  So first you create your migration.  Then you run your migration, then you run artisan make:all [TABLENAME] and it will create your Model, Controller, and Test files.  It will also append your routes and factories to routes/web.php db/factories/ModelFactory.php. Once you have run the command you can run vendor/bin/phpunit to verify the new stuff is working.

Each command can be ran separately:
  * artisan make:model [TABLENAME]
  * artisan make:controller [TABLENAME]
  * artisan make:routes [TABLENAME]
  * artisan make:factory [TABLENAME]
  * artisan make:test [TABLENAME]
  
It is expected that you will need to alter these files after creating them.  This just creates a very basic set of files and options.  They will not work at all in some cases.

I have set this up to use defaults convent for me. If there is interests options can be added to the commands to very how the files are created.  I use auth middleware by default, but disable it in the tests classes.