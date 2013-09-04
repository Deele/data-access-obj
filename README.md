data-access-obj
===============

This is MySQL data access PHP object, that allows extensions. It is inspired by Yii CActiveRecord and built for systems without access to data models.

data.access.obj.class.php
-------------------------

Data Access Object

Uses PDO connection to allow create data models based on database tables with simple syntax.

data.access.pdocon.class.php
----------------------------

PDO connection class

Retreives configuration for connection and uses PDO to create persistent connections to database.

data.access.exception.class.php
-------------------------------

Data access object error class

Contains error codes and messages that can accour during data access object class execution.

data.access.logger.php
----------------------

Logger

Contains static methods for message logging to system and output