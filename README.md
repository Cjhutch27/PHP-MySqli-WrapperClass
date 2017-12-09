# PHP-MySqli-WrapperClass  
## Table Of Contents  

[Installation](#installation)    
[Initialization](#initialization)  
[Connecting](#connecting)  
[Multiple-Connections](#multiple-connections)   
[Select](#select-query)    
[Insert](#insert-query)  
[Update](#update-query)   
[Where-Conditions](#where-conditions) 
[Having](#having)   
[Order-Conditions](#order-conditions)
[Group-Conditions](#group-conditions)    
[Join-Conditions](#join-conditions)   
[Count,Avg,Sum](#count-avg-sum)   
[Min,Max](#min-max)   
[Table-Copying](#table-copying)    
[Table-Altering](#table-altering)   
[Custom-Queries](#custom-queries)  
[Escape-Strings](#escape-strings)   
[Logging](#logging)   
[Table-Locking](#table-locking)  
[Error-Checking](#error-checking)  

# Installation  
To use this class, add simpleSQL.php and simpleSQLManager.php to your project and require it
```
require_once ('simpleSQLManager.php');
```
The manager class includes the SimpleSQL file as well. The manager class is not required as SimpleSQL does not depend on it. If you do not want to include the manager class as well only include simpleSQL   
```
require_once ('simpleSQL.php');
```   






# Initialization 
To initialize a simpleSQL object
``` 
$db = new simpleSQL("host","username","password","database_name");
```
Previously created mysqli connections can also be used to initialize this object
```
$sqli = new mysqli("host","username","password","database_name");  
$db = new simpleSQL($sqli);
```
To automatically connect your simpleSQL object upon initialization an extra parameter is required
```
$db = new simpleSQL("host","username","password","database_name",true);

```
Port and charset settings can be set by adding 2 parameters to the end of your object initialization
```
$db = new simpleSQL("host","username","password","database_name",true,3306,'utf8');
```




# Connecting  
To connect to your database use the connect() method
```  
$db->connect();
```   
# Multiple Connections  
# Select Query   
# Insert Query  
# Update Query 
# Where Conditions  
# Having  
# Order Conditions  
# Group Conditions  
# Join Conditions  
# Count Avg Sum  
# Min Max
# Table Copying
# Table Altering
# Custom Queries
To query a custom SQL statement use the queryStatement() Method  
```
$db->queryStatement("SELECT * FROM table1");
```
# Escape Strings  
# Logging  
# Table Locking  
# Error Checking  
